
<x-app-layout>

     <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Category_Quizzes') }}
        </h2>
    </x-slot>

<div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
               
                <div class="flex justify-between mb-4 mt-8">
                    <h3 class="text-lg font-medium">All Quizzes in <span class="font-bold">{{ $category->name }}</span> Category</h3>
                    {{-- Back button --}}
                    <a href="{{ route('categories.index') }}" 
                        class="inline-flex p-2 rounded-lg items-center border border-blue-600  text-blue-600 hover:text-blue-800 transition-colors">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                             Back to Categories
                     </a>
                </div >

                <div class="relative overflow-x-auto">
                    <table class="border border-gray-200 w-full text-left ">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr >
                                <th class="px-6 py-3">Quiz Name</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3">Questions Count</th>
                                @if(auth()->user()->role  === 'admin')
                                    <th class="px-6 py-3">Add Questions</th>
                                    <th class="px-6 py-3">Action</th>
                                @endif
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($quizzes as $quiz)
                                <tr class="border-b bg-white">
                                    <td class="px-6 py-4 font-medium text-gray-900">    
                                        {{ $quiz->title }}   
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $quiz->category->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $quiz->questions_count }}
                                    </td>
                                    
                                    @if(auth()->user()->role  === 'admin')
                                        {{-- takes to clicked quizzes --}}
                                        <td class="px-6 py-4">
                                            <a href="{{ route('quizzes.show', $quiz) }}" 
                                            class="text-gray-500 hover:text-blue-600 transition-colors" 
                                            title="Manage Questions">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-6 h-6" fill="currentColor">
                                                    <path d="M444-288h72v-156h156v-72H516v-156h-72v156H288v72h156v156Zm36.28 192Q401-96 331-126t-122.5-82.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.96-834q69.96-30 149.5-30t149.04 30q69.5 30 122 82.5T834-629.28q30 69.73 30 149Q864-401 834-331t-82.5 122.5Q699-156 629.28-126q-69.73 30-149 30Zm-.28-72q130 0 221-91t91-221q0-130-91-221t-221-91q-130 0-221 91t-91 221q0 130 91 221t221 91Zm0-312Z"/>
                                                </svg>
                                            </a>
                                        </td>
                                        {{-- destroy Quiz --}}
                                        <td class="px-6 py-4">
                                            <form action="
                                            {{ route('quizzes.destroy', $quiz->id) }}" 
                                            method="POST" 
                                            onsubmit="return confirm('Are you sure you want to delete this?')">
                                                @csrf
                                                @method('DELETE')
                                                
                                                <button type="submit" class="text-gray-500 hover:text-red-600 transition-colors">
                                                    {{-- Your SVG Icon Starts Here --}}
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    {{-- Your SVG Icon Ends Here --}}
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center">
                                        No quizzes found. Create your first one!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
