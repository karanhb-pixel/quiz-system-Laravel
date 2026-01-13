<form action="{{ route('questions.store') }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
    @csrf
    <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

    <div>
        <label class="block font-bold">Question Text</label>
        <textarea name="question_text" class="w-full border-gray-300 rounded" required></textarea>
    </div>

    <div class="grid grid-cols-1 gap-4">
        @foreach(['a', 'b', 'c', 'd'] as $letter)
            <div class="flex items-center space-x-2 border p-2 rounded">
                <span class="font-bold uppercase">{{ $letter }}:</span>
                <input type="text" name="{{ $letter }}" class="flex-1 border-none focus:ring-0" placeholder="Enter option {{ $letter }}" required>
                
                <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="radio" name="correct_answer" value="{{ $letter }}" class="text-green-600" required>
                    <span class="text-xs text-gray-500 uppercase">Correct</span>
                </label>
            </div>
        @endforeach
    </div>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Add Question
    </button>
    <div>

        <a href="{{ route('quizzes.index') }}" class="inline-flex p-2 rounded-lg items-center border border-blue-600  text-blue-600 hover:text-blue-800 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Quizzes
        </a>
    </div>
</form>