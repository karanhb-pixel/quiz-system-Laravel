<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Quiz Template') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Back Button -->
                    <div class="mb-6">
                        <a href="{{ route('quiz-templates.show', $template) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Back to Template') }}
                        </a>
                    </div>

                    <!-- Edit Template Form -->
                    <form method="POST" action="{{ route('quiz-templates.update', $template) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Template Name -->
                        <div>
                            <x-input-label for="name" :value="__('Template Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $template->name) }}" required autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" autocomplete="description">{{ old('description', $template->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <!-- Question Bank -->
                        <div>
                            <x-input-label for="question_bank_id" :value="__('Question Bank')" />
                            <select id="question_bank_id" name="question_bank_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">{{ __('Select a question bank') }}</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}" {{ old('question_bank_id', $template->question_bank_id) == $bank->id ? 'selected' : '' }}>
                                        {{ $bank->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('question_bank_id')" />
                        </div>

                        <!-- Total Questions -->
                        <div>
                            <x-input-label for="total_questions" :value="__('Total Questions')" />
                            <x-text-input id="total_questions" name="total_questions" type="number" class="mt-1 block w-full" min="1" max="50" value="{{ old('total_questions', $template->config['total_questions']) }}" required />
                            <x-input-error class="mt-2" :messages="$errors->get('total_questions')" />
                        </div>

                        <!-- Question Distribution -->
                        <div>
                            <x-input-label :value="__('Question Distribution')" />
                            <div class="grid grid-cols-3 gap-4 mt-2">
                                <div>
                                    <x-input-label for="mcq_count" :value="__('MCQ')" />
                                    <x-text-input id="mcq_count" name="mcq_count" type="number" class="mt-1 block w-full" min="0" value="{{ old('mcq_count', $template->config['question_distribution']['mcq']) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('mcq_count')" />
                                </div>
                                <div>
                                    <x-input-label for="fill_blank_count" :value="__('Fill Blank')" />
                                    <x-text-input id="fill_blank_count" name="fill_blank_count" type="number" class="mt-1 block w-full" min="0" value="{{ old('fill_blank_count', $template->config['question_distribution']['fill_blank']) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('fill_blank_count')" />
                                </div>
                                <div>
                                    <x-input-label for="code_count" :value="__('Code')" />
                                    <x-text-input id="code_count" name="code_count" type="number" class="mt-1 block w-full" min="0" value="{{ old('code_count', $template->config['question_distribution']['code']) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('code_count')" />
                                </div>
                            </div>
                        </div>

                        <!-- Difficulty Distribution -->
                        <div>
                            <x-input-label :value="__('Difficulty Distribution')" />
                            <div class="grid grid-cols-3 gap-4 mt-2">
                                <div>
                                    <x-input-label for="difficulty_distribution_easy" :value="__('Easy')" />
                                    <x-text-input id="difficulty_distribution_easy" name="difficulty_distribution[easy]" type="number" class="mt-1 block w-full" min="0" value="{{ old('difficulty_distribution.easy', $template->config['difficulty_distribution']['easy']) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('difficulty_distribution.easy')" />
                                </div>
                                <div>
                                    <x-input-label for="difficulty_distribution_medium" :value="__('Medium')" />
                                    <x-text-input id="difficulty_distribution_medium" name="difficulty_distribution[medium]" type="number" class="mt-1 block w-full" min="0" value="{{ old('difficulty_distribution.medium', $template->config['difficulty_distribution']['medium']) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('difficulty_distribution.medium')" />
                                </div>
                                <div>
                                    <x-input-label for="difficulty_distribution_hard" :value="__('Hard')" />
                                    <x-text-input id="difficulty_distribution_hard" name="difficulty_distribution[hard]" type="number" class="mt-1 block w-full" min="0" value="{{ old('difficulty_distribution.hard', $template->config['difficulty_distribution']['hard']) }}" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('difficulty_distribution.hard')" />
                                </div>
                            </div>
                        </div>

                        <!-- Time Limit -->
                        <div>
                            <x-input-label for="time_limit" :value="__('Time Limit (minutes)')" />
                            <x-text-input id="time_limit" name="time_limit" type="number" class="mt-1 block w-full" min="1" max="300" value="{{ old('time_limit', $template->config['time_limit']) }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('time_limit')" />
                        </div>

                        <!-- Is Public -->
                        <div>
                            <label for="is_public" class="block text-sm font-medium text-gray-700">
                                {{ __('Is Public') }}
                            </label>
                            <div class="mt-2">
                                <input id="is_public" name="is_public" type="checkbox" value="1" {{ old('is_public', $template->is_public) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">{{ __('Make this template available to all users') }}</span>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('is_public')" />
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end space-x-4">
                            <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" onclick="window.history.back()">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ __('Update Template') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>