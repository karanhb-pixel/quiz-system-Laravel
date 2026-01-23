<div class="mb-4">
    <x-input-label for="question_text" :value="__('Question')" />
    <textarea id="question_text" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="question_text" rows="3" required>{{ old('question_text') }}</textarea>
    <x-input-error :messages="$errors->get('question_text')" class="mt-2" />
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <x-input-label for="option_a" :value="__('Option A')" />
        <x-text-input id="option_a" class="block mt-1 w-full" type="text" name="option_a" :value="old('option_a')" required />
        <x-input-error :messages="$errors->get('option_a')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="option_b" :value="__('Option B')" />
        <x-text-input id="option_b" class="block mt-1 w-full" type="text" name="option_b" :value="old('option_b')" required />
        <x-input-error :messages="$errors->get('option_b')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="option_c" :value="__('Option C')" />
        <x-text-input id="option_c" class="block mt-1 w-full" type="text" name="option_c" :value="old('option_c')" required />
        <x-input-error :messages="$errors->get('option_c')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="option_d" :value="__('Option D')" />
        <x-text-input id="option_d" class="block mt-1 w-full" type="text" name="option_d" :value="old('option_d')" required />
        <x-input-error :messages="$errors->get('option_d')" class="mt-2" />
    </div>
</div>

<div class="mb-4">
    <x-input-label for="correct_answer" :value="__('Correct Answer')" />
    <select id="correct_answer" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="correct_answer" required>
        <option value="">Select Correct Answer</option>
        <option value="a" {{ old('correct_answer') == 'a' ? 'selected' : '' }}>A</option>
        <option value="b" {{ old('correct_answer') == 'b' ? 'selected' : '' }}>B</option>
        <option value="c" {{ old('correct_answer') == 'c' ? 'selected' : '' }}>C</option>
        <option value="d" {{ old('correct_answer') == 'd' ? 'selected' : '' }}>D</option>
    </select>
    <x-input-error :messages="$errors->get('correct_answer')" class="mt-2" />
</div>

<div class="mb-4">
    <x-input-label for="points" :value="__('Points')" />
    <x-text-input id="points" class="block mt-1 w-full" type="number" name="points" :value="old('points', 1)" min="1" max="10" required />
    <x-input-error :messages="$errors->get('points')" class="mt-2" />
</div>