
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
                                @foreach (['a','b','c','d'] as $letter )

                                    @php
                                        // check which letter ans is write 
                                        $isCorrect = ($question->correct_answer === $letter);
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
