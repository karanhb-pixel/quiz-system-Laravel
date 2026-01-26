<?php

namespace App\Services;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Client;
use Gemini\Enums\ModelType;
use Gemini\Transporters\HttpTransporter;

class GeminiService
{
    protected $client;

    public function __construct()
    {
        $apiKey = env('GEMINI_API_KEY');
       $this->client = Gemini::client($apiKey);
    }

    public function generateQuestions($topic, $difficulty, $numQuestions = 5, $questionType = 'mcq', $category = null)
    {
        $prompt = $this->buildPrompt($topic, $difficulty, $numQuestions, $questionType, $category);

        try {
            // Use the available 2.5 Flash model
            $result = $this->client->generativeModel('gemini-1.5-flash')->generateContent($prompt);
            $response = $result->text();
            
            // Clean up markdown code blocks if present
            if (strpos($response, '```') !== false) {
                $response = preg_replace('/^```json\s*|\s*```$/', '', trim($response));
                // Handle case where it might just be ``` without json or other variants
                $response = str_replace(['```json', '```'], '', $response);
            }

            \Log::info("Gemini Raw Response (Cleaned): " . $response);

            // Parse the response to ensure it's valid JSON
            $questions = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error("Gemini JSON Parse Error: " . json_last_error_msg());
                throw new \Exception("Failed to parse Gemini response: " . json_last_error_msg());
            }

            \Log::info("Gemini Parsed Questions Count: " . count($questions));
            \Log::info("Gemini Parsed Questions: ", $questions);
            
            // Add metadata to each question
            foreach ($questions as &$question) {
                $question['type'] = $questionType;
                $question['category'] = $category;
                $question['topic'] = $topic;
                $question['difficulty'] = $difficulty;
            }
            
            return $questions;
        } catch (\Exception $e) {
            // Log the error and return an empty array
            \Log::error("Gemini question generation failed: " . $e->getMessage());
            return [];
        }
    }

    protected function buildPrompt($topic, $difficulty, $numQuestions, $questionType, $category = null)
    {
        $categoryText = $category ? " in the category of $category" : '';
        
        switch ($questionType) {
            case 'mcq':
                return "Generate $numQuestions multiple-choice questions about $topic$categoryText with $difficulty difficulty. "
                    . "Each question should have 4 options and a correct answer. "
                    . "Format the response as a JSON array of objects with the following structure: "
                    . "[{'question': 'question text', 'options': ['a', 'b', 'c', 'd'], 'correct_answer': 'correct option', 'hint': 'short hint'}]";
                    
            case 'fill_blank':
                return "Generate $numQuestions fill-in-the-blank questions about $topic$categoryText with $difficulty difficulty. "
                    . "Each question should have a clear blank space and a correct answer. "
                    . "Format the response as a JSON array of objects with the following structure: "
                    . "[{'question': 'question text with blank', 'correct_answer': 'answer to fill the blank', 'hint': 'short hint'}]";
                    
            case 'code':
                return "Generate $numQuestions coding questions about $topic$categoryText with $difficulty difficulty. "
                    . "Each question should include a problem statement, expected solution code, and a hint or evaluation tip. "
                    . "Format the response as a JSON array of objects with the following structure: "
                    . "[{'question': 'problem statement', 'solution': 'expected solution code', 'hint': 'evaluation tip or hint'}]";
                    
            default:
                return "Generate $numQuestions questions about $topic$categoryText with $difficulty difficulty. "
                    . "Format the response as a JSON array of objects.";
        }
    }

    public function evaluateCode($questionText, $userAnswer, $correctAnswer, $hint = null)
    {
        // --- Step 1: Hybrid Keyword Check ---
        // Extract potential requirements from the hint (words in backticks or function-like words)
        if ($hint) {
            preg_match_all('/`([^`]+)`/', $hint, $matches);
            $requiredKeywords = $matches[1] ?? [];
            
            foreach ($requiredKeywords as $keyword) {
                if (stripos($userAnswer, $keyword) === false) {
                    return [
                        'is_correct' => false,
                        'explanation' => "Pre-check failed: Your answer is missing the required element: '$keyword'. Please refer to the hint."
                    ];
                }
            }
        }

        // --- Step 2 & 3: AI Call with Flash Model and Retry Logic ---
        $maxRetries = 3;
        $retryDelay = 2; // seconds

        $prompt = "You are a coding instructor. Evaluate the user's answer for this coding question:
        
Question: $questionText
Correct Solution (Reference): $correctAnswer
Evaluation Tip/Hint: $hint
User's Answer: $userAnswer

Determine if the user's answer is logically correct and solves the problem.
Return ONLY a JSON response: { \"is_correct\": true/false, \"explanation\": \"feedback\" }";

        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                // Using 1.5-flash as 2.5 does not exist
                $result = $this->client->generativeModel('gemini-1.5-flash')->generateContent($prompt);
                $response = $result->text();
                
                if (strpos($response, '```') !== false) {
                    $response = preg_replace('/^```json\s*|\s*```$/', '', trim($response));
                    $response = str_replace(['```json', '```'], '', $response);
                }
                
                return json_decode($response, true);
            } catch (\Exception $e) {
                // Handle 429 Too Many Requests
                if (strpos($e->getMessage(), '429') !== false || strpos($e->getMessage(), 'Too Many Requests') !== false) {
                    if ($i < $maxRetries - 1) {
                        sleep($retryDelay);
                        $retryDelay *= 2; // Exponential backoff
                        continue;
                    }
                    return [
                        'is_correct' => false, 
                        'explanation' => 'Evaluation server is busy. Please try again in 1 minute.',
                        'error_type' => 'rate_limit'
                    ];
                }

                \Log::error("Gemini evaluation failed: " . $e->getMessage());
                break; 
            }
        }

        return ['is_correct' => false, 'explanation' => 'AI Evaluation service unavailable.'];
    }

    public function generateQuestionBank($topic, $difficulty, $numQuestions = 10)
    {
        $questions = $this->generateQuestions($topic, $difficulty, $numQuestions);
        
        // Store questions in the database or return them for further processing
        return $questions;
    }
}