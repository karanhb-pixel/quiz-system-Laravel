<div class="mb-4">
    <x-input-label for="question_text" :value="__('Question')" />
    <textarea id="question_text" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="question_text" rows="3" required placeholder="e.g., Write CSS to center a div both horizontally and vertically">{{ old('question_text') }}</textarea>
    <x-input-error :messages="$errors->get('question_text')" class="mt-2" />
</div>

<div class="mb-4">
    <x-input-label for="language" :value="__('Programming Language')" />
    <select id="language" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="language" required>
        <option value="">Select Language</option>
        @foreach(\App\Models\CodeBankQuestion::getAvailableLanguages() as $key => $name)
        <option value="{{ $key }}" {{ old('language') == $key ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('language')" class="mt-2" />
</div>

<div class="mb-4">
    <x-input-label for="expected_code" :value="__('Expected Code Solution')" />
    <textarea id="expected_code" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-sm" name="expected_code" rows="8" required placeholder="Enter the expected code solution">{{ old('expected_code') }}</textarea>
    <x-input-error :messages="$errors->get('expected_code')" class="mt-2" />
</div>

<div class="mb-4">
    <x-input-label for="evaluation_criteria" :value="__('Evaluation Criteria (Optional)')" />
    <textarea id="evaluation_criteria" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="evaluation_criteria" rows="3" placeholder="Specific requirements: syntax correctness, specific properties, structure, etc.">{{ old('evaluation_criteria') }}</textarea>
    <x-input-error :messages="$errors->get('evaluation_criteria')" class="mt-2" />
</div>

<div class="mb-4">
    <x-input-label for="points" :value="__('Points')" />
    <x-text-input id="points" class="block mt-1 w-full" type="number" name="points" :value="old('points', 3)" min="1" max="10" required />
    <x-input-error :messages="$errors->get('points')" class="mt-2" />
</div>