<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $template->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Template Header -->
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">{{ $template->questionBank->name }}</p>
                        @if ($template->description)
                            <p class="mt-2 text-sm text-gray-600">{{ $template->description }}</p>
                        @endif
                    </div>
                    <div class="flex space-x-4">
                        @if (auth()->id() === $template->user_id)
                            <a href="{{ route('quiz-templates.edit', $template) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                {{ __('Edit') }}
                            </a>
                        @endif
                        <a href="{{ route('quiz-templates.take', $template) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{ __('Take Quiz') }}
                        </a>
                    </div>
                </div>

                <!-- Template Details -->
                <div class="p-6">
                    <!-- Template Information -->
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Template Information') }}</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Created by') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $template->user->name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Created at') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $template->created_at->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Type') }}</dt>
                                    <dd>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $template->is_public ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $template->is_public ? __('Public') : __('Private') }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Quiz Configuration -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Quiz Configuration') }}</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Total Questions') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $template->config['total_questions'] }}</dd>
                                </div>
                                @if ($template->config['time_limit'])
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Time Limit') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $template->config['time_limit'] }} {{ __('minutes') }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Shuffle Questions') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $template->config['shuffle_questions'] ? __('Yes') : __('No') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Question Distribution -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Question Distribution') }}</h3>
                        
                        <!-- Type Distribution -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('By Type') }}</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-blue-600">{{ $template->config['question_distribution']['mcq'] }}</div>
                                    <div class="text-sm text-gray-500">{{ __('MCQ') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-green-600">{{ $template->config['question_distribution']['fill_blank'] }}</div>
                                    <div class="text-sm text-gray-500">{{ __('Fill Blank') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-purple-600">{{ $template->config['question_distribution']['code'] }}</div>
                                    <div class="text-sm text-gray-500">{{ __('Code') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Difficulty Distribution -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('By Difficulty') }}</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-green-600">{{ $template->config['difficulty_distribution']['easy'] }}</div>
                                    <div class="text-sm text-gray-500">{{ __('Easy') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-yellow-600">{{ $template->config['difficulty_distribution']['medium'] }}</div>
                                    <div class="text-sm text-gray-500">{{ __('Medium') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-red-600">{{ $template->config['difficulty_distribution']['hard'] }}</div>
                                    <div class="text-sm text-gray-500">{{ __('Hard') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Question Bank Statistics -->
                    @if (isset($stats))
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Question Bank Statistics') }}</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-semibold text-blue-600">{{ $stats['total_questions'] }}</div>
                                        <div class="text-sm text-gray-500">{{ __('Total Questions') }}</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-semibold text-green-600">{{ $stats['mcq_count'] }}</div>
                                        <div class="text-sm text-gray-500">{{ __('MCQ Questions') }}</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-semibold text-purple-600">{{ $stats['code_count'] }}</div>
                                        <div class="text-sm text-gray-500">{{ __('Code Questions') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex justify-center">
                        <a href="{{ route('quiz-templates.take', $template) }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-lg font-semibold">
                            {{ __('Start Quiz') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>