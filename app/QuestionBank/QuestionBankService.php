<?php

namespace App\QuestionBank;

use App\Models\Category;
use App\Services\GeminiService;
use App\Models\Question;
use Illuminate\Support\Facades\Log;

class QuestionBankService
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function generateAndStoreQuestions($topic, $difficulty, $numQuestions = 10, $questionType = 'mcq', $categoryId = null, $quizId = null)
    {
        $category = $categoryId ? Category::find($categoryId) : null;
        $categoryName = $category ? $category->name : null;
        
        \Log::info("Starting QuestionBank generation for category: " . ($categoryName ?? 'none'));

        $questions = $this->geminiService->generateQuestions($topic, $difficulty, $numQuestions, $questionType, $categoryName);
        
        \Log::info("Received " . count($questions) . " raw questions from Gemini.");
        
        if (empty($questions)) {
            Log::error("No questions generated for topic: $topic");
            return [];
        }
        
        $storedQuestions = [];
        
        foreach ($questions as $questionData) {
            try {
                $questionData = array_merge([
                    'topic' => $topic,
                    'difficulty' => $difficulty,
                    'question_type' => $questionType,
                    'category_id' => $categoryId,
                    'quiz_id' => $quizId, // Associate the question with the quiz
                    'generated_by_ai' => true,
                    'hint' => $questionData['hint'] ?? null,
                ], $questionData);
                
                // Handle different question types
                switch ($questionType) {
                    case 'mcq':
                        // Determine the correct option letter (a, b, c, d)
                        $correctAnswer = $questionData['correct_answer'];
                        $options = $questionData['options'];
                        
                        // Find index of the correct answer in options
                        $index = array_search($correctAnswer, $options);
                        
                        // If found, map to letter; otherwise keep original (fallback)
                        if ($index !== false && isset(['a', 'b', 'c', 'd'][$index])) {
                            $correctAnswer = ['a', 'b', 'c', 'd'][$index];
                        }

                        $question = Question::create([
                            'question_text' => $questionData['question'],
                            'a' => $questionData['options'][0] ?? '',
                            'b' => $questionData['options'][1] ?? '',
                            'c' => $questionData['options'][2] ?? '',
                            'd' => $questionData['options'][3] ?? '',
                            'correct_answer' => $correctAnswer,
                            'topic' => $questionData['topic'],
                            'difficulty' => $questionData['difficulty'],
                            'question_type' => $questionData['question_type'],
                            'category_id' => $questionData['category_id'],
                            'quiz_id' => $questionData['quiz_id'],
                            'generated_by_ai' => $questionData['generated_by_ai'],
                            'hint' => $questionData['hint'] ?? null,
                        ]);
                        break;
                    
                    case 'fill_blank':
                        $question = Question::create([
                            'question_text' => $questionData['question'],
                            'correct_answer' => $questionData['correct_answer'],
                            'topic' => $questionData['topic'],
                            'difficulty' => $questionData['difficulty'],
                            'question_type' => $questionData['question_type'],
                            'category_id' => $questionData['category_id'],
                            'quiz_id' => $questionData['quiz_id'],
                            'generated_by_ai' => $questionData['generated_by_ai'],
                            'hint' => $questionData['hint'] ?? null,
                        ]);
                        break;
                    
                    case 'code':
                        $question = Question::create([
                            'question_text' => $questionData['question'],
                            'correct_answer' => $questionData['solution'],
                            'topic' => $questionData['topic'],
                            'difficulty' => $questionData['difficulty'],
                            'question_type' => $questionData['question_type'],
                            'category_id' => $questionData['category_id'],
                            'quiz_id' => $questionData['quiz_id'],
                            'generated_by_ai' => $questionData['generated_by_ai'],
                            'hint' => $questionData['hint'] ?? null,
                        ]);
                        break;
                }
                
                \Log::info("Successfully stored question: " . substr($question->question_text, 0, 50));
                
                $storedQuestions[] = $question;
            } catch (\Exception $e) {
                \Log::error("DATABASE STORAGE ERROR for question: " . $e->getMessage(), [
                    'question_data' => $questionData
                ]);
                continue;
            }
        }
        
        \Log::info("Total stored questions in this batch: " . count($storedQuestions));
        
        return $storedQuestions;
    }

    public function getQuestionsByTopic($topic, $limit = 10)
    {
        return Question::where('topic', $topic)
            ->where('generated_by_ai', true)
            ->limit($limit)
            ->get();
    }
}