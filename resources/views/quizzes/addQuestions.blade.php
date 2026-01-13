
<x-app-layout>

     <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AddQuestions') }}
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
                <h1 class="2xl font-bold "> Add Questions to {{ $quiz->title }}</h1>
                
                    <form action="{{ route('quizzes.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-1">Quiz Name</label>
                            <input type="text" name="title" class="border-gray-300 rounded shadow-sm w-full ">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>              
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-1">Select Category</label>
                            <select name="category_id" class="border-gray-300 rounded shadow-sm w-full ">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach    
                            </select>
                            @error('category_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>              

                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                            Create Quiz
                        </button>
                    </form>
                

                <div class="flex justify-between mb-4 mt-8">
                    <h3 class="text-lg font-medium">All Quizzes</h3>
                    
                </div >
                <div class="relative overflow-x-auto">
                    <table class="border border-gray-200 w-full text-left ">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr >
                                <th class="px-6 py-3">Quiz Name</th>
                                <th class="px-6 py-3">Category</th>
                                @auth
                                    <th class="px-6 py-3">Action</th>
                                @endauth
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($quizzes as $quiz)
                                <tr class="border-b bg-white">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $quiz->title }}</td>
                                        <td class="px-6 py-4">{{ $quiz->category->name }}</td>
                                        
                                        @auth
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
                                        @endauth
                                </tr>
                                @empty
                                <tr>
                                        <td colspan="4" class="px-6 py-4 text-center">No quizzes found. Create your first one!</td>
                                    </tr>
                            @endforelse
                         </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
