<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Quiz Attempts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Your Quiz History</h3>
                        <p class="text-sm text-gray-600">View all your quiz attempts and results</p>
                    </div>

                    @if($attempts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left">Question Bank</th>
                                    <th class="px-4 py-2 text-left">Score</th>
                                    <th class="px-4 py-2 text-left">Points</th>
                                    <th class="px-4 py-2 text-left">Questions</th>
                                    <th class="px-4 py-2 text-left">Completed</th>
                                    <th class="px-4 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attempts as $attempt)
                                <tr class="border-t">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('question-banks.show', $attempt->questionBank) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $attempt->questionBank->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="font-medium
                                            @if($attempt->score_percentage >= 80) text-green-600
                                            @elseif($attempt->score_percentage >= 60) text-yellow-600
                                            @else text-red-600 @endif">
                                            {{ $attempt->score_percentage }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $attempt->points_earned }} / {{ $attempt->total_points }}
                                    </td>
                                    <td class="px-4 py-2">{{ $attempt->total_questions }}</td>
                                    <td class="px-4 py-2">
                                        {{ $attempt->completed_at ? $attempt->completed_at->format('M j, Y g:i A') : 'In Progress' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($attempt->is_completed)
                                        <a href="{{ route('bank-quiz.result', $attempt) }}" class="text-blue-600 hover:text-blue-800">
                                            View Results
                                        </a>
                                        @else
                                        <span class="text-gray-500">In Progress</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $attempts->links() }}
                    @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">You haven't taken any quizzes yet.</p>
                        <a href="{{ route('question-banks.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Browse Question Banks
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>