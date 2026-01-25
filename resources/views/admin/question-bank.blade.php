<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gemini Question Generator') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Success/Error Messages -->
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('question-bank.generate') }}">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Category Selection -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('Category') }}
                                </label>
                                <select id="category_id" name="category_id" 
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                    <option value="">{{ __('Select a category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Question Type Selection -->
                            <div>
                                <label for="question_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('Question Type') }}
                                </label>
                                <select id="question_type" name="question_type" 
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                    <option value="">{{ __('Select question type') }}</option>
                                    <option value="mcq">{{ __('Multiple Choice Questions (MCQ)') }}</option>
                                    <option value="fill_blank">{{ __('Fill in the Blank') }}</option>
                                    <option value="code">{{ __('Code Questions') }}</option>
                                </select>
                                @error('question_type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Topic/Subtopic -->
                            <div>
                                <label for="topic" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('Topic/Subtopic') }}
                                </label>
                                <input type="text" name="topic" id="topic" 
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                       placeholder="Enter specific topic or subtopic" required>
                                @error('topic')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Difficulty Level -->
                            <div>
                                <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('Difficulty Level') }}
                                </label>
                                <select id="difficulty" name="difficulty" 
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                    <option value="">{{ __('Select difficulty') }}</option>
                                    <option value="easy">{{ __('Easy') }}</option>
                                    <option value="medium">{{ __('Medium') }}</option>
                                    <option value="hard">{{ __('Hard') }}</option>
                                </select>
                                @error('difficulty')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Number of Questions -->
                            <div>
                                <label for="num_questions" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('Number of Questions') }}
                                </label>
                                <input type="number" name="num_questions" id="num_questions" 
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                       min="1" max="20" value="10" required>
                                @error('num_questions')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Additional Instructions -->
                            <div class="md:col-span-2">
                                <label for="instructions" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('Additional Instructions (Optional)') }}
                                </label>
                                <textarea name="instructions" id="instructions" rows="3"
                                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                          placeholder="Any specific requirements or instructions for question generation"></textarea>
                                @error('instructions')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="mt-6 flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Generate Questions') }}
                            </button>
                        </div>
                    </form>
                    
                    <!-- Generated Questions Preview -->
                    @if(isset($generatedQuestions) && count($generatedQuestions) > 0)
                        <div class="mt-12">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                {{ __('Generated Questions Preview') }}
                            </h3>
                            
                            <div class="bg-gray-50 rounded-lg p-6">
                                @foreach($generatedQuestions as $index => $question)
                                    <div class="mb-6 p-4 bg-white rounded-lg shadow">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="text-md font-medium text-gray-800">
                                                {{ __('Question') }} {{ $index + 1 }}
                                            </h4>
                                            <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">
                                                {{ strtoupper($question['type']) }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-gray-600 mb-3">{{ $question['question'] }}</p>
                                        
                                        @if($question['type'] === 'mcq')
                                            <div class="ml-4 mb-2">
                                                <p class="text-sm text-gray-500 mb-1">{{ __('Options:') }}</p>
                                                <ul class="list-disc list-inside">
                                                    @foreach($question['options'] as $option)
                                                        <li class="text-sm {{ $option === $question['correct_answer'] ? 'text-green-600 font-medium' : 'text-gray-600' }}">
                                                            {{ $option }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @elseif($question['type'] === 'fill_blank')
                                            <div class="ml-4 mb-2">
                                                <p class="text-sm text-gray-500 mb-1">{{ __('Correct Answer:') }}</p>
                                                <p class="text-sm text-green-600 font-medium">{{ $question['correct_answer'] }}</p>
                                            </div>
                                        @elseif($question['type'] === 'code')
                                            <div class="ml-4 mb-2">
                                                <p class="text-sm text-gray-500 mb-1">{{ __('Expected Solution:') }}</p>
                                                <pre class="bg-gray-100 p-2 rounded text-sm overflow-x-auto">{{ $question['solution'] }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>