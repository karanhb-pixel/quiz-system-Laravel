<?php

namespace App\Services;

use Gemini\Client;
use Gemini\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private Client $client;
    private array $config;

    public function __construct()
    {
        $this->config = config('gemini');

        // Check if AI generation is enabled
        if (!$this->config['generation']['enabled']) {
            throw new \Exception('AI generation is currently disabled');
        }

        // Validate API key
        if (empty($this->config['api_key'])) {
            throw new \Exception('Gemini API key is not configured');
        }

        $factory = new Factory();
        $this->client = $factory->withApiKey($this->config['api_key'])->make();
    }

    /**
     * Check rate limiting for AI requests
     */
    private function checkRateLimit(string $userId): void
    {
        if (!$this->config['rate_limiting']['enabled']) {
            return;
        }

        $cacheKey = "ai_rate_limit:{$userId}";
        $currentRequests = Cache::get($cacheKey, 0);

        // Check per-minute limit
        if ($currentRequests >= $this->config['rate_limiting']['max_attempts_per_minute']) {
            $blockKey = "ai_blocked:{$userId}";
            Cache::put($blockKey, true, now()->addMinutes($this->config['rate_limiting']['block_duration_minutes']));
            throw new \Exception('Rate limit exceeded. Too many requests per minute.');
        }

        // Check per-hour limit
        $hourlyKey = "ai_rate_limit_hourly:{$userId}";
        $hourlyRequests = Cache::get($hourlyKey, 0);

        if ($hourlyRequests >= $this->config['rate_limiting']['max_attempts_per_hour']) {
            $blockKey = "ai_blocked:{$userId}";
            Cache::put($blockKey, true, now()->addMinutes($this->config['rate_limiting']['block_duration_minutes']));
            throw new \Exception('Rate limit exceeded. Too many requests per hour.');
        }

        // Check if user is blocked
        $blockKey = "ai_blocked:{$userId}";
        if (Cache::get($blockKey)) {
            throw new \Exception('Account temporarily blocked due to rate limiting.');
        }

        // Increment counters
        Cache::put($cacheKey, $currentRequests + 1, now()->addMinute());
        Cache::put($hourlyKey, $hourlyRequests + 1, now()->addHour());
    }

    /**
     * Log AI request for monitoring
     */
    private function logAiRequest(string $type, array $params, bool $success = true, ?string $error = null): void
    {
        if (!$this->config['monitoring']['log_requests']) {
            return;
        }

        $logData = [
            'type' => $type,
            'params' => $params,
            'success' => $success,
            'timestamp' => now()->toISOString(),
        ];

        if ($error) {
            $logData['error'] = $error;
        }

        Log::info('AI Request', $logData);
    }

    /**
     * Evaluate fill-in-blank answer
     */
    public function evaluateFillBlank(
        string $questionText,
        string $userAnswer,
        string $expectedAnswer,
        ?string $hints = null,
        bool $caseSensitive = false
    ): array {
        $prompt = $this->buildFillBlankPrompt($questionText, $userAnswer, $expectedAnswer, $hints, $caseSensitive);

        try {
            $response = $this->client->geminiPro()->generateContent($prompt);
            return $this->parseEvaluationResponse($response->text());
        } catch (\Exception $e) {
            return $this->fallbackEvaluation($userAnswer, $expectedAnswer, $caseSensitive);
        }
    }

    /**
     * Evaluate code answer
     */
    public function evaluateCode(
        string $questionText,
        string $userCode,
        string $expectedCode,
        ?string $criteria,
        string $language
    ): array {
        $prompt = $this->buildCodePrompt($questionText, $userCode, $expectedCode, $criteria, $language);

        try {
            $response = $this->client->geminiPro()->generateContent($prompt);
            return $this->parseEvaluationResponse($response->text());
        } catch (\Exception $e) {
            return $this->fallbackCodeEvaluation($userCode, $expectedCode);
        }
    }

    /**
     * Generate MCQ question with rate limiting and caching
     */
    public function generateMcqQuestion(
        string $topic,
        string $difficulty = 'medium',
        ?string $additionalContext = null,
        ?int $userId = null
    ): array {
        // Check rate limiting if user ID provided
        if ($userId) {
            $this->checkRateLimit((string)$userId);
        }

        // Check cache first
        $cacheKey = null;
        if ($this->config['cache']['enabled']) {
            $cacheKey = $this->config['cache']['prefix'] . 'mcq:' . md5($topic . $difficulty . ($additionalContext ?? ''));
            $cached = Cache::get($cacheKey);
            if ($cached) {
                $this->logAiRequest('mcq_generation', ['topic' => $topic, 'cached' => true]);
                return $cached;
            }
        }

        $prompt = $this->buildMcqGenerationPrompt($topic, $difficulty, $additionalContext);

        try {
            $response = $this->client->geminiPro()->generateContent($prompt);
            $result = $this->parseMcqGenerationResponse($response->text());

            // Cache the result
            if ($cacheKey) {
                Cache::put($cacheKey, $result, $this->config['cache']['ttl']);
            }

            $this->logAiRequest('mcq_generation', ['topic' => $topic, 'difficulty' => $difficulty], true);
            return $result;

        } catch (\Exception $e) {
            $this->logAiRequest('mcq_generation', ['topic' => $topic, 'difficulty' => $difficulty], false, $e->getMessage());
            return $this->fallbackMcqGeneration($topic, $difficulty);
        }
    }

    /**
     * Generate fill-in-the-blank question with rate limiting and caching
     */
    public function generateFillBlankQuestion(
        string $topic,
        string $difficulty = 'medium',
        ?string $additionalContext = null,
        ?int $userId = null
    ): array {
        // Check rate limiting if user ID provided
        if ($userId) {
            $this->checkRateLimit((string)$userId);
        }

        // Check cache first
        $cacheKey = null;
        if ($this->config['cache']['enabled']) {
            $cacheKey = $this->config['cache']['prefix'] . 'fill_blank:' . md5($topic . $difficulty . ($additionalContext ?? ''));
            $cached = Cache::get($cacheKey);
            if ($cached) {
                $this->logAiRequest('fill_blank_generation', ['topic' => $topic, 'cached' => true]);
                return $cached;
            }
        }

        $prompt = $this->buildFillBlankGenerationPrompt($topic, $difficulty, $additionalContext);

        try {
            $response = $this->client->geminiPro()->generateContent($prompt);
            $result = $this->parseFillBlankGenerationResponse($response->text());

            // Cache the result
            if ($cacheKey) {
                Cache::put($cacheKey, $result, $this->config['cache']['ttl']);
            }

            $this->logAiRequest('fill_blank_generation', ['topic' => $topic, 'difficulty' => $difficulty], true);
            return $result;

        } catch (\Exception $e) {
            $this->logAiRequest('fill_blank_generation', ['topic' => $topic, 'difficulty' => $difficulty], false, $e->getMessage());
            return $this->fallbackFillBlankGeneration($topic, $difficulty);
        }
    }

    /**
     * Generate code question with rate limiting and caching
     */
    public function generateCodeQuestion(
        string $topic,
        string $difficulty = 'medium',
        string $language = 'php',
        ?string $additionalContext = null,
        ?int $userId = null
    ): array {
        // Check rate limiting if user ID provided
        if ($userId) {
            $this->checkRateLimit((string)$userId);
        }

        // Check cache first
        $cacheKey = null;
        if ($this->config['cache']['enabled']) {
            $cacheKey = $this->config['cache']['prefix'] . 'code:' . md5($topic . $difficulty . $language . ($additionalContext ?? ''));
            $cached = Cache::get($cacheKey);
            if ($cached) {
                $this->logAiRequest('code_generation', ['topic' => $topic, 'language' => $language, 'cached' => true]);
                return $cached;
            }
        }

        $prompt = $this->buildCodeGenerationPrompt($topic, $difficulty, $language, $additionalContext);

        try {
            $response = $this->client->geminiPro()->generateContent($prompt);
            $result = $this->parseCodeGenerationResponse($response->text());

            // Cache the result
            if ($cacheKey) {
                Cache::put($cacheKey, $result, $this->config['cache']['ttl']);
            }

            $this->logAiRequest('code_generation', ['topic' => $topic, 'language' => $language, 'difficulty' => $difficulty], true);
            return $result;

        } catch (\Exception $e) {
            $this->logAiRequest('code_generation', ['topic' => $topic, 'language' => $language, 'difficulty' => $difficulty], false, $e->getMessage());
            return $this->fallbackCodeGeneration($topic, $difficulty, $language);
        }
    }

    /**
     * Build prompt for fill-in-blank evaluation
     */
    private function buildFillBlankPrompt($questionText, $userAnswer, $expectedAnswer, $hints, $caseSensitive): string
    {
        $caseInstruction = $caseSensitive ? 'Case sensitive' : 'Case insensitive';

        return "Evaluate this fill-in-the-blank question answer:

Question: {$questionText}
Expected Answer: {$expectedAnswer}
User Answer: {$userAnswer}
" . ($hints ? "Evaluation Hints: {$hints}" : "") . "
{$caseInstruction}

Please respond with ONLY a JSON object in this exact format:
{
    \"is_correct\": true/false,
    \"confidence\": 0.0-1.0,
    \"feedback\": \"brief explanation\",
    \"score\": 0.0-1.0
}";
    }

    /**
     * Build prompt for code evaluation
     */
    private function buildCodePrompt($questionText, $userCode, $expectedCode, $criteria, $language): string
    {
        return "Evaluate this {$language} code answer:

Question: {$questionText}

Expected Solution:
```{$language}
{$expectedCode}
```

User Solution:
```{$language}
{$userCode}
```
" . ($criteria ? "Evaluation Criteria: {$criteria}" : "") . "

Please respond with ONLY a JSON object in this exact format:
{
    \"is_correct\": true/false,
    \"confidence\": 0.0-1.0,
    \"feedback\": \"brief explanation of correctness\",
    \"score\": 0.0-1.0
}";
    }

    /**
     * Build prompt for MCQ generation
     */
    private function buildMcqGenerationPrompt($topic, $difficulty, $additionalContext): string
    {
        $difficultyGuide = match($difficulty) {
            'easy' => 'basic concepts, straightforward questions',
            'medium' => 'intermediate concepts, some analysis required',
            'hard' => 'advanced concepts, complex problem-solving'
        };

        return "Generate a multiple choice question about {$topic} at {$difficulty} difficulty level ({$difficultyGuide}).

" . ($additionalContext ? "Additional context: {$additionalContext}\n\n" : "") . "Requirements:
- Question should be clear and unambiguous
- Four options (A, B, C, D) with only one correct answer
- Options should be plausible but only one correct
- Include a brief explanation of why the answer is correct

Please respond with ONLY a JSON object in this exact format:
{
    \"question_text\": \"The question text\",
    \"option_a\": \"First option\",
    \"option_b\": \"Second option\",
    \"option_c\": \"Third option\",
    \"option_d\": \"Fourth option\",
    \"correct_answer\": \"a\",
    \"explanation\": \"Brief explanation of the correct answer\",
    \"difficulty\": \"{$difficulty}\",
    \"topic\": \"{$topic}\"
}";
    }

    /**
     * Build prompt for fill-in-the-blank generation
     */
    private function buildFillBlankGenerationPrompt($topic, $difficulty, $additionalContext): string
    {
        $difficultyGuide = match($difficulty) {
            'easy' => 'basic vocabulary/terms, simple recall',
            'medium' => 'intermediate concepts, some understanding required',
            'hard' => 'advanced concepts, complex relationships'
        };

        return "Generate a fill-in-the-blank question about {$topic} at {$difficulty} difficulty level ({$difficultyGuide}).

" . ($additionalContext ? "Additional context: {$additionalContext}\n\n" : "") . "Requirements:
- Create a sentence with one blank space
- The blank should contain a single word or short phrase
- Question should test understanding, not just memorization
- Provide 2-3 evaluation hints for grading
- Specify if answer should be case-sensitive

Please respond with ONLY a JSON object in this exact format:
{
    \"question_text\": \"The sentence with ______ blank\",
    \"expected_answer\": \"the correct answer\",
    \"case_sensitive\": true/false,
    \"evaluation_hints\": \"Hints for evaluating answers\",
    \"explanation\": \"Why this answer is correct\",
    \"difficulty\": \"{$difficulty}\",
    \"topic\": \"{$topic}\"
}";
    }

    /**
     * Build prompt for code question generation
     */
    private function buildCodeGenerationPrompt($topic, $difficulty, $language, $additionalContext): string
    {
        $difficultyGuide = match($difficulty) {
            'easy' => 'basic syntax, simple operations',
            'medium' => 'intermediate concepts, some logic required',
            'hard' => 'advanced patterns, complex problem-solving'
        };

        return "Generate a {$language} coding question about {$topic} at {$difficulty} difficulty level ({$difficultyGuide}).

" . ($additionalContext ? "Additional context: {$additionalContext}\n\n" : "") . "Requirements:
- Clear problem statement
- Expected solution should be complete and correct
- Include evaluation criteria for grading
- Code should follow {$language} best practices
- Question should test programming logic, not just syntax

Please respond with ONLY a JSON object in this exact format:
{
    \"question_text\": \"The coding problem description\",
    \"expected_code\": \"Complete correct solution\",
    \"language\": \"{$language}\",
    \"evaluation_criteria\": \"How to evaluate the solution\",
    \"test_cases\": \"Sample input/output examples\",
    \"explanation\": \"Explanation of the solution approach\",
    \"difficulty\": \"{$difficulty}\",
    \"topic\": \"{$topic}\"
}";
    }

    /**
     * Parse AI response into structured format
     */
    private function parseEvaluationResponse(string $response): array
    {
        // Extract JSON from response
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');

        if ($jsonStart === false || $jsonEnd === false) {
            return $this->fallbackEvaluation('', '', false);
        }

        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);

        try {
            $data = json_decode($jsonString, true);

            return [
                'is_correct' => $data['is_correct'] ?? false,
                'confidence' => max(0.0, min(1.0, $data['confidence'] ?? 0.5)),
                'feedback' => $data['feedback'] ?? 'Evaluation completed',
                'score' => max(0.0, min(1.0, $data['score'] ?? 0.0))
            ];
        } catch (\Exception $e) {
            return $this->fallbackEvaluation('', '', false);
        }
    }

    /**
     * Fallback evaluation when AI fails
     */
    private function fallbackEvaluation($userAnswer, $expectedAnswer, $caseSensitive): array
    {
        $isCorrect = $caseSensitive
            ? $userAnswer === $expectedAnswer
            : strtolower($userAnswer) === strtolower($expectedAnswer);

        return [
            'is_correct' => $isCorrect,
            'confidence' => 0.5,
            'feedback' => 'AI evaluation failed. Manual review recommended.',
            'score' => $isCorrect ? 1.0 : 0.0
        ];
    }

    /**
     * Fallback code evaluation
     */
    private function fallbackCodeEvaluation($userCode, $expectedCode): array
    {
        // Simple string similarity fallback
        $similarity = 0;
        similar_text($userCode, $expectedCode, $similarity);
        $score = $similarity / 100;

        return [
            'is_correct' => $score > 0.8,
            'confidence' => 0.3,
            'feedback' => 'AI evaluation failed. Manual review recommended.',
            'score' => $score
        ];
    }

    /**
     * Parse MCQ generation response
     */
    private function parseMcqGenerationResponse(string $response): array
    {
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');

        if ($jsonStart === false || $jsonEnd === false) {
            return $this->fallbackMcqGeneration('Unknown', 'medium');
        }

        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);

        try {
            $data = json_decode($jsonString, true);
            return [
                'question_text' => $data['question_text'] ?? 'Generated question',
                'option_a' => $data['option_a'] ?? 'Option A',
                'option_b' => $data['option_b'] ?? 'Option B',
                'option_c' => $data['option_c'] ?? 'Option C',
                'option_d' => $data['option_d'] ?? 'Option D',
                'correct_answer' => $data['correct_answer'] ?? 'a',
                'explanation' => $data['explanation'] ?? 'AI generated question',
                'difficulty' => $data['difficulty'] ?? 'medium',
                'topic' => $data['topic'] ?? 'General',
                'generated_by_ai' => true
            ];
        } catch (\Exception $e) {
            return $this->fallbackMcqGeneration('Unknown', 'medium');
        }
    }

    /**
     * Parse fill-in-the-blank generation response
     */
    private function parseFillBlankGenerationResponse(string $response): array
    {
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');

        if ($jsonStart === false || $jsonEnd === false) {
            return $this->fallbackFillBlankGeneration('Unknown', 'medium');
        }

        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);

        try {
            $data = json_decode($jsonString, true);
            return [
                'question_text' => $data['question_text'] ?? 'Generated ______ question',
                'expected_answer' => $data['expected_answer'] ?? 'answer',
                'case_sensitive' => $data['case_sensitive'] ?? false,
                'evaluation_hints' => $data['evaluation_hints'] ?? 'AI generated hints',
                'explanation' => $data['explanation'] ?? 'AI generated question',
                'difficulty' => $data['difficulty'] ?? 'medium',
                'topic' => $data['topic'] ?? 'General',
                'generated_by_ai' => true
            ];
        } catch (\Exception $e) {
            return $this->fallbackFillBlankGeneration('Unknown', 'medium');
        }
    }

    /**
     * Parse code generation response
     */
    private function parseCodeGenerationResponse(string $response): array
    {
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');

        if ($jsonStart === false || $jsonEnd === false) {
            return $this->fallbackCodeGeneration('Unknown', 'medium', 'php');
        }

        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);

        try {
            $data = json_decode($jsonString, true);
            return [
                'question_text' => $data['question_text'] ?? 'Generated coding question',
                'expected_code' => $data['expected_code'] ?? 'echo "Hello World";',
                'language' => $data['language'] ?? 'php',
                'evaluation_criteria' => $data['evaluation_criteria'] ?? 'AI generated criteria',
                'test_cases' => $data['test_cases'] ?? 'Sample test cases',
                'explanation' => $data['explanation'] ?? 'AI generated question',
                'difficulty' => $data['difficulty'] ?? 'medium',
                'topic' => $data['topic'] ?? 'General',
                'generated_by_ai' => true
            ];
        } catch (\Exception $e) {
            return $this->fallbackCodeGeneration('Unknown', 'medium', 'php');
        }
    }

    /**
     * Fallback MCQ generation
     */
    private function fallbackMcqGeneration($topic, $difficulty): array
    {
        return [
            'question_text' => "Sample question about {$topic}",
            'option_a' => 'Option A',
            'option_b' => 'Option B',
            'option_c' => 'Option C',
            'option_d' => 'Option D',
            'correct_answer' => 'a',
            'explanation' => 'AI generation failed. Please review and modify.',
            'difficulty' => $difficulty,
            'topic' => $topic,
            'generated_by_ai' => true,
            'needs_review' => true
        ];
    }

    /**
     * Fallback fill-in-the-blank generation
     */
    private function fallbackFillBlankGeneration($topic, $difficulty): array
    {
        return [
            'question_text' => "Sample ______ question about {$topic}",
            'expected_answer' => 'answer',
            'case_sensitive' => false,
            'evaluation_hints' => 'AI generation failed. Please provide evaluation hints.',
            'explanation' => 'AI generation failed. Please review and modify.',
            'difficulty' => $difficulty,
            'topic' => $topic,
            'generated_by_ai' => true,
            'needs_review' => true
        ];
    }

    /**
     * Fallback code generation
     */
    private function fallbackCodeGeneration($topic, $difficulty, $language): array
    {
        $sampleCode = match($language) {
            'php' => '<?php echo "Hello World"; ?>',
            'javascript' => 'console.log("Hello World");',
            'python' => 'print("Hello World")',
            default => 'echo "Hello World";'
        };

        return [
            'question_text' => "Sample coding question about {$topic}",
            'expected_code' => $sampleCode,
            'language' => $language,
            'evaluation_criteria' => 'AI generation failed. Please provide evaluation criteria.',
            'test_cases' => 'AI generation failed. Please provide test cases.',
            'explanation' => 'AI generation failed. Please review and modify.',
            'difficulty' => $difficulty,
            'topic' => $topic,
            'generated_by_ai' => true,
            'needs_review' => true
        ];
    }
}