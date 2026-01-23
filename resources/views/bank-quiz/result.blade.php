<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quiz Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold mb-2">{{ $attempt->questionBank->name }}</h3>
                        <div class="text-4xl font-bold mb-2
                            @if($attempt->score_percentage >= 80) text-green-600
                            @elseif($attempt->score_percentage >= 60) text-yellow-600
                            @else text-red-600 @endif">
                            {{ $attempt->score_percentage }}%
                        </div>
                        <p class="text-gray-600">
                            {{ $attempt->points_earned }} / {{ $attempt->total_points }} points
                        </p>
                    </div>

                    <div class="space-y-4">
                        @foreach($attempt->responses as $response)
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium">
                                    <span class="inline-block px-2 py-1 text-xs rounded mr-2
                                        @if($response->question_type === 'mcq') bg-green-100 text-green-800
                                        @elseif($response->question_type === 'fill_blank') bg-blue-100 text-blue-800
                                        @else bg-purple-100 text-purple-800 @endif">
                                        {{ $response->question_type_label }}
                                    </span>
                                    {{ Str::limit($response->question->question_text, 100) }}
                                </h4>
                                <span class="text-sm @if($response->is_correct) text-green-600 @else text-red-600 @endif">
                                    {{ $response->points_earned }} / {{ $response->question->points }} pts
                                </span>
                            </div>

                            @if($response->question_type === 'mcq')
                                <p class="text-sm mb-2">
                                    <strong>Your answer:</strong> {{ strtoupper($response->user_answer) }}
                                    @if(!$response->is_correct)
                                    (Correct: {{ strtoupper($response->question->correct_answer) }})
                                    @endif
                                </p>
                            @elseif($response->question_type === 'fill_blank')
                                <p class="text-sm mb-2">
                                    <strong>Your answer:</strong> {{ $response->user_answer }}
                                    @if(!$response->is_correct)
                                    <br><strong>Expected:</strong> {{ $response->question->expected_answer }}
                                    @endif
                                </p>
                            @else
                                <div class="mb-2">
                                    <strong>Your code:</strong>
                                    <pre class="bg-gray-100 p-2 rounded text-sm mt-1 overflow-x-auto">{{ $response->user_answer }}</pre>
                                </div>
                            @endif

                            @if($response->ai_feedback)
                            <div class="mt-2 p-3 bg-blue-50 rounded">
                                <p class="text-sm">
                                    <strong>AI Feedback:</strong> {{ $response->ai_feedback }}
                                    @if($response->ai_confidence)
                                    <span class="text-xs text-gray-500 ml-2">
                                        (Confidence: {{ round($response->ai_confidence * 100) }}%)
                                    </span>
                                    @endif
                                </p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <div class="text-center mt-8">
                        <a href="{{ route('question-banks.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-4">
                            Back to Question Banks
                        </a>
                        <a href="{{ route('bank-quiz.my-attempts') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            My Quiz Attempts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>