<x-app-layout>
    <div class="py-12" x-data="resultHandler()">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center">
                
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Quiz Results</h2>
                <p class="text-gray-500 mb-8">{{ $quiz->title }}</p>

                <!-- Loading State -->
                <template x-if="status === 'pending'">
                    <div class="py-10">
                        <div class="animate-spin rounded-full h-20 w-20 border-b-2 border-blue-600 mx-auto mb-6"></div>
                        <h3 class="text-xl font-semibold text-gray-700">Evaluating your answers...</h3>
                        <p class="text-gray-500 mt-2">Our AI is reviewing your code. This may take a few seconds.</p>
                    </div>
                </template>

                <!-- Completed State -->
                <template x-if="status === 'completed'">
                    <div>
                        <div class="inline-flex items-center justify-center w-40 h-40 rounded-full border-8 mb-6"
                             :class="percentage >= 50 ? 'border-green-500' : 'border-red-500'">
                            <div>
                                <span class="block text-4xl font-black text-gray-800" x-text="score + '/' + total"></span>
                                <span class="text-sm text-gray-500 uppercase">Correct</span>
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-xl font-semibold">
                                <span x-show="percentage >= 80">Excellent Job! 🏆</span>
                                <span x-show="percentage >= 50 && percentage < 80">Good Effort! 👍</span>
                                <span x-show="percentage < 50">Keep Practicing! 📚</span>
                            </h3>
                            <p class="text-gray-600 mt-2">You scored <span x-text="percentage"></span>%</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('quizzes.attempt', $quiz) }}" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                                Try Again
                            </a>
                            <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
                                Back to Home
                            </a>
                        </div>

                        {{-- Review Section --}}
                        <div class="mt-12 text-left">
                            <h3 class="text-xl font-bold mb-6">Review Your Answers</h3>
                    
                            <template x-for="(question, index) in questions" :key="question.id">
                                <div class="mb-6 p-4 rounded-lg border"
                                     :class="isCorrect(question.id) ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">
                                    
                                    <p class="font-semibold mb-3">
                                        <span x-text="(index + 1) + '. ' + question.question_text"></span>
                                        <span x-show="isCorrect(question.id)" class="text-green-600 ml-2">✓ Correct</span>
                                        <span x-show="!isCorrect(question.id)" class="text-red-600 ml-2">✗ Incorrect</span>
                                    </p>
                                    
                                    <div class="text-sm space-y-2">
                                        <p><span class="font-bold">Your Answer:</span> <br>
                                            <pre class="font-mono bg-white p-2 border rounded mt-1" x-text="userAnswers[question.id] || 'No answer'"></pre>
                                        </p>

                                        <template x-if="getEval(question.id)">
                                            <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded text-blue-800">
                                                <p class="font-bold mb-1">🤖 AI Feedback:</p>
                                                <p x-text="getEval(question.id).explanation"></p>
                                            </div>
                                        </template>

                                        <template x-if="!isCorrect(question.id)">
                                            <div>
                                                <p class="mt-4"><span class="font-bold text-red-700">Expected Answer:</span> <br>
                                                    <pre class="font-mono bg-white p-2 border rounded mt-1" x-text="question.correct_answer"></pre>
                                                </p>
                                                
                                                <template x-if="question.hint">
                                                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
                                                        <p class="font-bold mb-1">💡 Solution Tip:</p>
                                                        <p x-text="question.hint"></p>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function resultHandler() {
            return {
                status: '{{ $result->status }}',
                score: {{ $result->correct_answers }},
                total: {{ $result->total_questions }},
                percentage: {{ $result->score_percentage }},
                userAnswers: @json($userAnswers),
                aiEvaluations: @json($aiEvaluations),
                questions: @json($quiz->questions),
                
                init() {
                    if (this.status === 'pending') {
                        this.startEvaluation();
                    }
                },
                
                async startEvaluation() {
                    try {
                        const response = await fetch('{{ route('quizzes.evaluate', $result->id) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.status === 'completed') {
                            this.score = data.score;
                            this.total = data.total;
                            this.percentage = data.percentage;
                            this.aiEvaluations = data.aiEvaluations;
                            this.status = 'completed';
                        }
                    } catch (error) {
                        console.error('Evaluation failed:', error);
                    }
                },
                
                isCorrect(questionId) {
                    if (this.aiEvaluations[questionId]) {
                        return this.aiEvaluations[questionId].is_correct;
                    }
                    
                    const question = this.questions.find(q => q.id === questionId);
                    if (!question) return false;
                    
                    const userAns = (this.userAnswers[questionId] || '').toString().trim().toLowerCase();
                    const correctAns = (question.correct_answer || '').toString().trim().toLowerCase();
                    
                    return userAns === correctAns;
                },
                
                getEval(questionId) {
                    return this.aiEvaluations[questionId] || null;
                }
            }
        }
    </script>
</x-app-layout>