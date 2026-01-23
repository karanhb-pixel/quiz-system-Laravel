<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analytics for :bank', ['bank' => $bank->name]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <!-- Overview Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800">Total Attempts</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['total_attempts'] }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-800">Average Score</h3>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['average_score'] }}%</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-purple-800">Highest Score</h3>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['highest_score'] ?? 'N/A' }}%</p>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-orange-800">Completion Rate</h3>
                        <p class="text-2xl font-bold text-orange-600">{{ $stats['completion_rate'] }}%</p>
                    </div>
                </div>

                <!-- Question Performance -->
                @if(!empty($questionPerformance))
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4">Question Performance</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border">Question Type</th>
                                    <th class="px-4 py-2 border">Question ID</th>
                                    <th class="px-4 py-2 border">Total Attempts</th>
                                    <th class="px-4 py-2 border">Correct %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questionPerformance as $performance)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border">{{ ucfirst(str_replace('_', ' ', $performance['question_type'])) }}</td>
                                    <td class="px-4 py-2 border">{{ $performance['question_id'] }}</td>
                                    <td class="px-4 py-2 border">{{ $performance['total_attempts'] }}</td>
                                    <td class="px-4 py-2 border">
                                        <span class="px-2 py-1 rounded {{ $performance['correct_percentage'] >= 70 ? 'bg-green-100 text-green-800' : ($performance['correct_percentage'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $performance['correct_percentage'] }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Recent Attempts -->
                @if($recentAttempts->count() > 0)
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4">Recent Attempts</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border">User</th>
                                    <th class="px-4 py-2 border">Score</th>
                                    <th class="px-4 py-2 border">Completed At</th>
                                    <th class="px-4 py-2 border">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttempts as $attempt)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border">{{ $attempt->user->name }}</td>
                                    <td class="px-4 py-2 border">{{ $attempt->score_percentage }}%</td>
                                    <td class="px-4 py-2 border">{{ $attempt->completed_at?->format('M d, Y H:i') ?? 'Not completed' }}</td>
                                    <td class="px-4 py-2 border">
                                        <span class="px-2 py-1 rounded {{ $attempt->completed_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $attempt->completed_at ? 'Completed' : 'In Progress' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Back Button -->
                <div class="mt-8">
                    <a href="{{ route('question-banks.show', $bank) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Question Bank
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>