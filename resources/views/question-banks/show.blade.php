<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Question Bank: :name', ['name' => $bank->name]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-2">{{ $bank->name }}</h3>
                        <p class="text-gray-600 mb-2">{{ $bank->description }}</p>
                        <p class="text-sm text-gray-500">
                            Category: {{ $bank->category->name }} |
                            Questions: {{ $bank->total_questions }} |
                            Created by: {{ $bank->user->name }}
                        </p>
                    </div>

                    @if(Auth::user()->can('update', $bank))
                    <div class="mb-6">
                        <div class="flex flex-wrap gap-2 mb-4">
                            <a href="{{ route('bank-questions.create', [$bank, 'mcq']) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Add MCQ Question
                            </a>
                            <a href="{{ route('bank-questions.create', [$bank, 'fill_blank']) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Add Fill-in-Blank Question
                            </a>
                            <a href="{{ route('bank-questions.create', [$bank, 'code']) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                Add Code Question
                            </a>
                            <a href="{{ route('question-banks.edit', $bank) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Edit Bank
                            </a>
                        </div>

                        <!-- AI Generation Section -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-md font-semibold text-gray-800">🤖 AI Question Generation</h4>
                                    <p class="text-sm text-gray-600">Generate high-quality questions automatically</p>
                                </div>
                                <a href="{{ route('ai-questions.create', $bank) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center">
                                    <span class="mr-2">🚀</span>
                                    Generate AI Questions
                                </a>
                            </div>
                        </div>

                        <!-- Analytics Section -->
                        @if($bank->total_questions > 0)
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-md font-semibold text-gray-800">📊 Analytics Dashboard</h4>
                                    <p class="text-sm text-gray-600">View performance metrics and insights</p>
                                </div>
                                <a href="{{ route('bank-analytics.show', $bank) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center">
                                    <span class="mr-2">📈</span>
                                    View Analytics
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($bank->total_questions > 0)
                    <div class="mb-6">
                        <a href="{{ route('bank-quiz.start', $bank) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            Take Quiz ({{ $bank->questions_per_quiz }} questions)
                        </a>
                    </div>
                    @else
                    <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 rounded">
                        <p class="text-yellow-800">Add some questions to this bank before taking a quiz.</p>
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left">Type</th>
                                    <th class="px-4 py-2 text-left">Question</th>
                                    <th class="px-4 py-2 text-left">Details</th>
                                    <th class="px-4 py-2 text-left">Points</th>
                                    @if(Auth::user()->can('update', $bank))
                                    <th class="px-4 py-2 text-left">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bank->mcqQuestions as $question)
                                <tr class="border-t">
                                    <td class="px-4 py-2">
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">MCQ</span>
                                    </td>
                                    <td class="px-4 py-2">{{ Str::limit($question->question_text, 50) }}</td>
                                    <td class="px-4 py-2">
                                        <small>Correct: {{ $question->correct_option_text }}</small>
                                    </td>
                                    <td class="px-4 py-2">{{ $question->points }}</td>
                                    @if(Auth::user()->can('update', $bank))
                                    <td class="px-4 py-2">
                                        <a href="{{ route('bank-questions.edit', [$bank, $question, 'mcq']) }}" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                                        <form method="POST" action="{{ route('bank-questions.destroy', [$bank, $question, 'mcq']) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach

                                @foreach($bank->fillBlankQuestions as $question)
                                <tr class="border-t">
                                    <td class="px-4 py-2">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Fill Blank</span>
                                    </td>
                                    <td class="px-4 py-2">{{ Str::limit($question->question_text, 50) }}</td>
                                    <td class="px-4 py-2">
                                        <small>Expected: {{ Str::limit($question->expected_answer, 30) }}</small>
                                    </td>
                                    <td class="px-4 py-2">{{ $question->points }}</td>
                                    @if(Auth::user()->can('update', $bank))
                                    <td class="px-4 py-2">
                                        <a href="{{ route('bank-questions.edit', [$bank, $question, 'fill_blank']) }}" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                                        <form method="POST" action="{{ route('bank-questions.destroy', [$bank, $question, 'fill_blank']) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach

                                @foreach($bank->codeQuestions as $question)
                                <tr class="border-t">
                                    <td class="px-4 py-2">
                                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">{{ strtoupper($question->language) }}</span>
                                    </td>
                                    <td class="px-4 py-2">{{ Str::limit($question->question_text, 50) }}</td>
                                    <td class="px-4 py-2">
                                        <small>{{ strlen($question->expected_code) }} chars</small>
                                    </td>
                                    <td class="px-4 py-2">{{ $question->points }}</td>
                                    @if(Auth::user()->can('update', $bank))
                                    <td class="px-4 py-2">
                                        <a href="{{ route('bank-questions.edit', [$bank, $question, 'code']) }}" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                                        <form method="POST" action="{{ route('bank-questions.destroy', [$bank, $question, 'code']) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach

                                @if($bank->total_questions == 0)
                                <tr>
                                    <td colspan="5" class="px-4 py-2 text-center text-gray-500">
                                        No questions added yet.
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>