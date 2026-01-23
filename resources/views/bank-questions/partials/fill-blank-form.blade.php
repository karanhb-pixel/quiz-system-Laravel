<div class="mb-4">
    <x-input-label for="question_text" :value="__('Question')" />
    <textarea id="question_text" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="question_text" rows="3" required placeholder="e.g., Laravel is a ______ framework">{{ old('question_text') }}</textarea>
    <x-input-error :messages="$errors->get('question_text')" class="mt-2" />
    <p class="text-sm text-gray-500 mt-1">Use ______ to indicate blanks in your question</p>
</div>

<div class="mb-4">
    <x-input-label for="expected_answer" :value="__('Expected Answer')" />
    <x-text-input id="expected_answer" class="block mt-1 w-full" type="text" name="expected_answer" :value="old('expected_answer')" required placeholder="e.g., PHP" />
    <x-input-error :messages="$errors->get('expected_answer')" class="mt-2" />
</div>

<div class="mb-4">
    <x-input-label for="evaluation_hints" :value="__('Evaluation Hints (Optional)')" />
    <textarea id="evaluation_hints" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="evaluation_hints" rows="2" placeholder="Additional guidance for AI evaluation">{{ old('evaluation_hints') }}</textarea>
    <x-input-error :messages="$errors->get('evaluation_hints')" class="mt-2" />
</div>

<div class="mb-4">
    <label class="flex items-center">
        <input type="checkbox" name="case_sensitive" value="1" {{ old('case_sensitive') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <span class="ml-2 text-sm text-gray-600">Case sensitive answer</span>
    </label>
</div>

<div class="mb-4">
    <x-input-label for="points" :value="__('Points')" />
    <x-text-input id="points" class="block mt-1 w-full" type="number" name="points" :value="old('points', 2)" min="1" max="10" required />
    <x-input-error :messages="$errors->get('points')" class="mt-2" />
</div>