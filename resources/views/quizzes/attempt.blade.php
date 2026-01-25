<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">{{ $quiz->title }}</h1>
                <a href="{{ route('quizzes.category', $quiz->category->slug) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors" onclick="return confirm('Are you sure you want to exit? Your progress will be lost.')">
                    Exit Quiz
                </a>
            </div>

            <form action="{{ route('quizzes.submit', $quiz) }}" method="POST">
                @csrf
                @foreach($quiz->questions as $index => $question)
                    <div class="bg-white p-6 rounded-lg shadow mb-6">
                        <p class="font-semibold text-lg mb-4">
                            Q{{ $index + 1 }}: {{ $question->question_text }}
                        </p>

                        <div class="space-y-3">
                        <div class="space-y-3">
                            @if($question->question_type === 'mcq')
                                @foreach(['a', 'b', 'c', 'd'] as $letter)
                                    @if(!empty($question->$letter))
                                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $letter }}" class="mr-3 text-blue-600" required>
                                            <span>{{ $question->$letter }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            @elseif($question->question_type === 'fill_blank')
                                <input type="text" name="answers[{{ $question->id }}]" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Type your answer here..." required>
                            @elseif($question->question_type === 'code')
                                <label class="block text-sm font-medium text-gray-700 mb-1">Write your code solution below:</label>
                                <textarea name="answers[{{ $question->id }}]" class="w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono" rows="6" placeholder="// Write your solution here..."></textarea>
                            @endif
                        </div>
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700">
                    Submit My Answers
                </button>
            </form>
        </div>
    </div>
</x-app-layout>