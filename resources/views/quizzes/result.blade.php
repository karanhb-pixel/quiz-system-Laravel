<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center">
                
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Quiz Results</h2>
                <p class="text-gray-500 mb-8">{{ $quiz->title }}</p>

                <div class="inline-flex items-center justify-center w-40 h-40 rounded-full border-8 {{ $percentage >= 50 ? 'border-green-500' : 'border-red-500' }} mb-6">
                    <div>
                        <span class="block text-4xl font-black text-gray-800">{{ $score }}/{{ $total }}</span>
                        <span class="text-sm text-gray-500 uppercase">Correct</span>
                    </div>
                </div>

                <div class="mb-10">
                    <h3 class="text-xl font-semibold">
                        @if($percentage >= 80)
                            Excellent Job! 🏆
                        @elseif($percentage >= 50)
                            Good Effort! 👍
                        @else
                            Keep Practicing! 📚
                        @endif
                    </h3>
                    <p class="text-gray-600 mt-2">You scored {{ $percentage }}%</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('quizzes.attempt', $quiz) }}" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                        Try Again
                    </a>
                    <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
                        Back to Home
                    </a>
                </div>

                {{-- review Section --}}
                <div class="mt-12 text-left">
                    <h3 class="text-xl font-bold mb-6">Review Your Answers</h3>
            
                    @foreach($quiz->questions as $index => $question)
                        @php 
                            $userPicked = $userAnswers[$question->id] ?? 'None';
                            $isCorrect = $userPicked === $question->correct_answer;
                        @endphp

                        <div class="mb-6 p-4 rounded-lg border {{ $isCorrect ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                            <p class="font-semibold mb-3">
                                {{ $index + 1 }}. {{ $question->question_text }}
                                @if($isCorrect)
                                    <span class="text-green-600 ml-2">✓ Correct</span>
                                @else
                                    <span class="text-red-600 ml-2">✗ Incorrect</span>
                                @endif
                            </p>

                            
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>