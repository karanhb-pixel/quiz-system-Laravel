<form action="{{ route('questions.store') }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow" x-data="{ type: 'mcq' }">
    @csrf
    <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

    <div>
        <label class="block font-bold">Question Type</label>
        <select name="question_type" x-model="type" class="w-full border-gray-300 rounded mb-4">
            <option value="mcq">Multiple Choice</option>
            <option value="fill_blank">Fill in the Blank</option>
            <option value="code">Code Based</option>
        </select>
    </div>

    <div>
        <label class="block font-bold">Question Text / Problem Statement</label>
        <textarea name="question_text" class="w-full border-gray-300 rounded" rows="3" required></textarea>
    </div>

    <!-- MCQ Options Section -->
    <fieldset class="grid grid-cols-1 gap-4" x-show="type === 'mcq'" :disabled="type !== 'mcq'">
        @foreach(['a', 'b', 'c', 'd'] as $letter)
            <div class="flex items-center space-x-2 border p-2 rounded">
                <span class="font-bold uppercase">{{ $letter }}:</span>
                <input type="text" name="{{ $letter }}" class="flex-1 border-none focus:ring-0" placeholder="Enter option {{ $letter }}">
                
                <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="radio" name="correct_answer" value="{{ $letter }}" class="text-green-600">
                    <span class="text-xs text-gray-500 uppercase">Correct</span>
                </label>
            </div>
        @endforeach
    </fieldset>

    <!-- Fill in Blank Answer Section -->
    <div x-show="type === 'fill_blank'" style="display: none;">
        <label class="block font-bold">Correct Answer</label>
        <input type="text" name="correct_answer" class="w-full border-gray-300 rounded" placeholder="Type the correct answer" :disabled="type !== 'fill_blank'">
    </div>

    <!-- Code Based Answer Section -->
    <div x-show="type === 'code'" style="display: none;">
        <label class="block font-bold">Expected Solution Code</label>
        <textarea name="correct_answer" class="w-full border-gray-300 rounded font-mono text-sm" rows="5" placeholder="Enter the expected solution code" :disabled="type !== 'code'"></textarea>
    </div>

    <!-- Hint Section (Available for all types) -->
    <div class="mt-4">
        <label class="block font-bold">Hint / Evaluation Tip (Optional)</label>
        <textarea name="hint" class="w-full border-gray-300 rounded" rows="2" placeholder="Enter a hint or evaluation criteria"></textarea>
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