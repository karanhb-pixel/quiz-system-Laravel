<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quiz: :bank', ['bank' => $bank->name]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <div class="flex justify-between items-center">
                            <h4 class="text-md font-medium">Question {{ $attempt->responses->count() + 1 }} of {{ $attempt->total_questions }}</h4>
                            <span class="text-sm text-gray-500">Points: {{ $attempt->total_points }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('bank-quiz.submit', $attempt) }}" id="quiz-form">
                        @csrf

                        @foreach($questions as $index => $questionData)
                        <div class="question-block {{ $index > 0 ? 'hidden' : '' }}" data-question="{{ $index }}">
                            <div class="mb-6">
                                <h4 class="text-md font-medium mb-3">
                                    <span class="inline-block px-2 py-1 text-xs rounded mr-2
                                        @if($questionData['type'] === 'mcq') bg-green-100 text-green-800
                                        @elseif($questionData['type'] === 'fill_blank') bg-blue-100 text-blue-800
                                        @else bg-purple-100 text-purple-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $questionData['type'])) }}
                                    </span>
                                    {{ $questionData['model']->question_text }}
                                </h4>

                                @if($questionData['type'] === 'mcq')
                                    <div class="space-y-2">
                                        @foreach(['a', 'b', 'c', 'd'] as $option)
                                        <label class="flex items-center">
                                            <input type="radio" name="answers[{{ $questionData['id'] }}]" value="{{ $option }}"
                                                   class="mr-2" required>
                                            <span class="font-medium mr-2">{{ strtoupper($option) }}.</span>
                                            {{ $questionData['model']->{"option_{$option}"} }}
                                        </label>
                                        @endforeach
                                    </div>

                                @elseif($questionData['type'] === 'fill_blank')
                                    <input type="text" name="answers[{{ $questionData['id'] }}]"
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Your answer here..." required>

                                @elseif($questionData['type'] === 'code')
                                    <div class="mb-2">
                                        <span class="text-sm text-gray-600">Language: {{ ucfirst($questionData['model']->language) }}</span>
                                    </div>
                                    <textarea name="answers[{{ $questionData['id'] }}]"
                                              class="w-full h-48 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                                              placeholder="Write your code here..." required></textarea>
                                @endif

                                <div class="mt-3 text-sm text-gray-600">
                                    Points: {{ $questionData['points'] }}
                                </div>
                            </div>

                            @if(!$loop->last)
                            <div class="flex justify-between">
                                <button type="button" class="prev-btn bg-gray-500 text-white px-4 py-2 rounded {{ $index === 0 ? 'hidden' : '' }}">
                                    Previous
                                </button>
                                <button type="button" class="next-btn bg-blue-500 text-white px-4 py-2 rounded">
                                    Next
                                </button>
                            </div>
                            @else
                            <div class="text-center">
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg">
                                    Submit Quiz
                                </button>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple quiz navigation
        document.addEventListener('DOMContentLoaded', function() {
            const blocks = document.querySelectorAll('.question-block');
            const nextBtns = document.querySelectorAll('.next-btn');
            const prevBtns = document.querySelectorAll('.prev-btn');

            let currentQuestion = 0;

            nextBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    blocks[currentQuestion].classList.add('hidden');
                    currentQuestion++;
                    blocks[currentQuestion].classList.remove('hidden');
                });
            });

            prevBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    blocks[currentQuestion].classList.add('hidden');
                    currentQuestion--;
                    blocks[currentQuestion].classList.remove('hidden');
                });
            });
        });
    </script>
</x-app-layout>