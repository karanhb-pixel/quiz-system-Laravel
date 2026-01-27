
<x-app-layout>

     <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ShowQuestions') }}
        </h2>
    </x-slot>

<div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-500 text-white p-4 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h1 class="text-2xl font-bold mb-4">
                    Quiz : {{ $quiz->title }} Questions
                </h1>
                
                {{-- Add Quiz Question component --}}
                <x-add-question :quiz="$quiz"/>

                {{-- AI Generation Status Indicators --}}
                @if(($quiz->generation_status === 'pending' || $quiz->generation_status === 'processing') && $quiz->questions->isEmpty())
                    <div class="m-4 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center shadow-sm animate-pulse">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <p class="text-blue-700 font-medium">AI is generating questions for you...</p>
                            <p class="text-blue-600 text-sm">The page will automatically refresh when ready.</p>
                        </div>
                    </div>
                    @push('scripts')
                    <script>
                        // Fallback manual refresh every 10 seconds
                        setTimeout(function() {
                            window.location.reload();
                        }, 10000); 

                        // Real-time WebSocket refresh
                        document.addEventListener('DOMContentLoaded', () => {
                            if (window.Echo) {
                                window.Echo.channel('quizzes.{{ $quiz->id }}')
                                    .listen('.QuizGenerationCompleted', (e) => {
                                        console.log('AI Generation Complete! Reloading...');
                                        window.location.reload();
                                    });
                            }
                        });
                    </script>
                    @endpush
                @elseif($quiz->generation_status === 'failed')
                    <div class="m-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start shadow-sm">
                        <svg class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-red-700 font-bold">AI Generation Failed</p>
                            <p class="text-red-600 mt-1">{{ $quiz->generation_error ?: 'An unknown error occurred during generation.' }}</p>
                            <div class="mt-3">
                                <button onclick="window.location.reload()" class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded text-sm transition font-medium border border-red-300">
                                    Try Refreshing
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <h2 class="text-2xl font-bold m-4">All Questions in {{ $quiz->title }} </h2>

                    @forelse ($quiz->questions as $index =>$question )
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="text-lg font-semibold">
                                    <span class="text-gray-400">
                                        #{{  $index + 1 }}
                                    </span>
                                    {{ $question->question_text }}
                                </h4>

                                {{-- Optional: Delete Question Button --}}
                                <form action="{{ route('questions.destroy', $question->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @if($question->question_type === 'mcq')
                                    @foreach (['a','b','c','d'] as $letter )
    
                                        @php
                                            // check which letter ans is write 
                                            // Note: for fill_blank, correct_answer is not a letter, so this won't match accidentally
                                            $isCorrect = ($question->question_type === 'mcq' && $question->correct_answer === $letter);
                                        @endphp
    
                                        <div class="p-3 rounded-md border {{ $isCorrect ? 'bg-green-50 border-green-500 ring-1 ring-green-500' : 'bg-gray-50 border-gray-200' }}">
                                            <div class="flex items-center">
                                                {{-- A B C D letters --}}
                                                <span class="font-bold uppercase mr-2 {{ $isCorrect ? 'text-green-700' : 'text-gray-500' }}">
                                                    {{ $letter }}
                                                </span>
                                                {{-- Answers from Question table using letter as key --}}
                                                <span class="{{ $isCorrect ? 'text-green-900 font-medium' : 'text-gray-700' }}">
                                                    {{ $question->$letter }}
                                                </span>
    
                                                {{-- svg icon --}}
                                                @if($isCorrect)
                                                    <svg class="w-4 h-4 ml-auto text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                        
                                    @endforeach
                                @elseif($question->question_type === 'fill_blank')
                                    <div class="col-span-1 md:col-span-2 p-4 bg-green-50 border border-green-500 rounded-lg flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <span class="font-bold text-green-800 uppercase text-sm tracking-wide">Correct Answer:</span>
                                            <span class="text-green-900 font-semibold ml-1 text-lg">{{ $question->correct_answer }}</span>
                                        </div>
                                    </div>
                                @elseif($question->question_type === 'code')
                                    <div class="col-span-1 md:col-span-2 p-4 bg-gray-900 border border-gray-700 rounded-lg">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                            </svg>
                                            <span class="font-bold text-gray-300 uppercase text-sm tracking-wide">Expected Solution:</span>
                                        </div>
                                        <pre class="text-green-400 font-mono text-sm overflow-x-auto p-2"><code>{{ $question->correct_answer }}</code></pre>
                                    </div>
                                @endif
                                
                                @if($question->hint)
                                    <div class="col-span-1 md:col-span-2 mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                                        <span class="font-bold">Evaluation Tip/Hint:</span> {{ $question->hint }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                    @empty
                        <div class="text-center py-10 bg-white rounded-lg border-2 border-dashed">
                            <p class="text-gray-500">No questions added yet. Use the form above to get started!</p>
                        </div>
                    @endforelse

            </div>
        </div>
    </div>
</x-app-layout>
