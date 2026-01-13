<x-app-layout>
    <x-slot name="header" >
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Quizzes in {{ $category->name }}
            </h2>
            <div>
            <a href="{{ route('dashboard') }}" class="inline-flex p-2 rounded-lg items-center border border-blue-600  text-blue-600 hover:text-blue-800 transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Categories
            </a>
        </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- loop throuth all quizzes in Category --}}
                @forelse($quizzes as $quiz)
                    <div class="bg-white p-6 rounded-lg shadow border-t-4 border-blue-500">
                        <h3 class="text-lg font-bold">{{ $quiz->title }}</h3>
                        <p class="text-gray-500 text-sm mb-4">Created by: {{ $quiz->user->name }}</p>
                        <p class="text-gray-500 text-sm mb-4">Questions in quiz: 
                            <span class="font-bold">
                                {{ $quiz->questions_count }}
                            </span>
                        </p>
                        @if($quiz->questions_count >= 1)
                            @auth
                                {{-- Show Start Quiz button only for logged-in users --}}
                                <a href="{{ route('quizzes.attempt', $quiz) }}" 
                                class="block text-center bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
                                    Start Quiz
                                </a>
                            @else
                                {{-- Show Login button or a message for guests --}}
                                <a href="{{ route('login') }}" 
                                class="block text-center bg-gray-500 text-white py-2 rounded hover:bg-gray-600 transition">
                                    Login to Attempt
                                </a>
                            @endauth
                        @else
                            {{-- Show nothing or a 'Coming Soon' message if quiz is empty --}}
                            <p class="text-center text-gray-400 italic">Questions coming soon</p>
                        @endif
                    </div>
                @empty
                    <p class="col-span-3 text-center text-gray-500">No quizzes available in this category yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>