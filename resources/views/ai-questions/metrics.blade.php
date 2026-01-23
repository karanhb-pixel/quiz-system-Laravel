<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AI Generation Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <!-- Navigation -->
                <div class="mb-6">
                    <a href="{{ route('question-banks.index') }}" class="text-blue-600 hover:text-blue-800">
                        ← Back to Question Banks
                    </a>
                </div>

                <!-- Overview Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800">Total AI Questions</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['total_ai_questions'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-800">Generation Success Rate</h3>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['success_rate'] ?? 0 }}%</p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-yellow-800">Questions Needing Review</h3>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['needs_review_count'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-purple-800">Average Quality Score</h3>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['avg_quality_score'] ?? 0 }}%</p>
                    </div>
                </div>

                <!-- Quality Distribution Chart -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4">Quality Score Distribution</h3>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <div class="space-y-4">
                            @php
                                $qualityBands = $stats['quality_distribution'] ?? [
                                    'excellent' => 0,
                                    'good' => 0,
                                    'average' => 0,
                                    'poor' => 0
                                ];
                            @endphp

                            <div class="flex items-center">
                                <span class="w-24 text-sm text-green-700">Excellent (90-100%)</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-4 mx-4">
                                    <div class="bg-green-500 h-4 rounded-full" style="width: {{ ($qualityBands['excellent'] / max($stats['total_ai_questions'], 1)) * 100 }}%"></div>
                                </div>
                                <span class="w-12 text-sm font-bold">{{ $qualityBands['excellent'] }}</span>
                            </div>

                            <div class="flex items-center">
                                <span class="w-24 text-sm text-blue-700">Good (80-89%)</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-4 mx-4">
                                    <div class="bg-blue-500 h-4 rounded-full" style="width: {{ ($qualityBands['good'] / max($stats['total_ai_questions'], 1)) * 100 }}%"></div>
                                </div>
                                <span class="w-12 text-sm font-bold">{{ $qualityBands['good'] }}</span>
                            </div>

                            <div class="flex items-center">
                                <span class="w-24 text-sm text-yellow-700">Average (60-79%)</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-4 mx-4">
                                    <div class="bg-yellow-500 h-4 rounded-full" style="width: {{ ($qualityBands['average'] / max($stats['total_ai_questions'], 1)) * 100 }}%"></div>
                                </div>
                                <span class="w-12 text-sm font-bold">{{ $qualityBands['average'] }}</span>
                            </div>

                            <div class="flex items-center">
                                <span class="w-24 text-sm text-red-700">Poor (<60%)</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-4 mx-4">
                                    <div class="bg-red-500 h-4 rounded-full" style="width: {{ ($qualityBands['poor'] / max($stats['total_ai_questions'], 1)) * 100 }}%"></div>
                                </div>
                                <span class="w-12 text-sm font-bold">{{ $qualityBands['poor'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Generation Trends -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4">Generation Trends (Last 7 Days)</h3>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        @if(isset($stats['generation_trends']) && count($stats['generation_trends']) > 0)
                            <div class="space-y-2">
                                @foreach($stats['generation_trends'] as $trend)
                                <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                                    <span class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($trend['date'])->format('M d, Y') }}</span>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-sm">{{ $trend['count'] }} questions</span>
                                        <span class="text-sm px-2 py-1 rounded {{ $trend['avg_quality'] >= 80 ? 'bg-green-100 text-green-800' : ($trend['avg_quality'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ number_format($trend['avg_quality'], 1) }}% avg quality
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">No generation data available for the last 7 days.</p>
                        @endif
                    </div>
                </div>

                <!-- Question Type Distribution -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4">Question Types Generated</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @php
                            $typeStats = $stats['question_types'] ?? [
                                'mcq' => 0,
                                'fill_blank' => 0,
                                'code' => 0
                            ];
                        @endphp

                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-4">
                                <div class="text-3xl mr-3">📝</div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Multiple Choice</h4>
                                    <p class="text-sm text-gray-600">MCQ Questions</p>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-blue-600">{{ $typeStats['mcq'] }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $stats['total_ai_questions'] > 0 ? round(($typeStats['mcq'] / $stats['total_ai_questions']) * 100, 1) : 0 }}% of total
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-4">
                                <div class="text-3xl mr-3">🔤</div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Fill in the Blank</h4>
                                    <p class="text-sm text-gray-600">Text Completion</p>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-green-600">{{ $typeStats['fill_blank'] }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $stats['total_ai_questions'] > 0 ? round(($typeStats['fill_blank'] / $stats['total_ai_questions']) * 100, 1) : 0 }}% of total
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <div class="flex items-center mb-4">
                                <div class="text-3xl mr-3">💻</div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Coding Questions</h4>
                                    <p class="text-sm text-gray-600">Programming Challenges</p>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-purple-600">{{ $typeStats['code'] }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $stats['total_ai_questions'] > 0 ? round(($typeStats['code'] / $stats['total_ai_questions']) * 100, 1) : 0 }}% of total
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent AI Generations -->
                @if(isset($stats['recent_generations']) && count($stats['recent_generations']) > 0)
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4">Recent AI Generations</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border">Type</th>
                                    <th class="px-4 py-2 border">Topic</th>
                                    <th class="px-4 py-2 border">Quality Score</th>
                                    <th class="px-4 py-2 border">Needs Review</th>
                                    <th class="px-4 py-2 border">Generated At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_generations'] as $generation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border">
                                        <span class="px-2 py-1 rounded text-xs {{ $generation['question_type'] === 'mcq' ? 'bg-blue-100 text-blue-800' : ($generation['question_type'] === 'fill_blank' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                            {{ ucfirst(str_replace('_', ' ', $generation['question_type'])) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 border">{{ $generation['topic'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 border">
                                        <span class="px-2 py-1 rounded text-xs {{ $generation['quality_score'] >= 80 ? 'bg-green-100 text-green-800' : ($generation['quality_score'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $generation['quality_score'] }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 border">
                                        @if($generation['needs_review'])
                                            <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">Needs Review</span>
                                        @else
                                            <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Approved</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border">{{ $generation['created_at']->format('M d, Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- API Usage Stats -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4">API Usage Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-2">Total API Calls</h4>
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['api_calls'] ?? 0 }}</div>
                            <p class="text-sm text-gray-500">This month</p>
                        </div>

                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-2">Success Rate</h4>
                            <div class="text-2xl font-bold text-green-600">{{ $stats['api_success_rate'] ?? 0 }}%</div>
                            <p class="text-sm text-gray-500">API call success</p>
                        </div>

                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-2">Average Response Time</h4>
                            <div class="text-2xl font-bold text-purple-600">{{ $stats['avg_response_time'] ?? 0 }}ms</div>
                            <p class="text-sm text-gray-500">API response time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>