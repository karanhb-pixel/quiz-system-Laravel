
<x-app-layout>

     <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Categories') }}
        </h2>
    </x-slot>

<div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @auth
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-1">Category Name</label>
                            <input type="text" name="name" class="border-gray-300 rounded shadow-sm w-full ">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>              

                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                            Add Category
                        </button>
                    </form>
                @endauth

                <div class="flex justify-between mb-4 mt-8">
                    <h3 class="text-lg font-medium">All Categories</h3>
                </div >

                <div class="relative overflow-x-auto">
                    <table class="border border-gray-200 w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                                <th class="px-6 py-3">Category Name</th>
                                <th class="px-6 py-3">Creator Name</th>
                                <th class="px-6 py-3">Quiz Count</th>
                                @auth
                                    <th class="px-6 py-3">Action</th>
                                @endauth
                                @guest
                                    <th class="px-6 py-3">Attempt</th>
                                @endguest
                        </tr>
                        </thead>

                        <tbody>
                            @forelse($categories as $category)
                                <tr class="bg-white border-b">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                                        <td class="px-6 py-4">
                                            {{$category->creator}}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{$category->quizzes_count}}
                                        </td>
                                        
                                            <td class="px-6 py-4 pl-5 flex gap-2">
                                                @auth
                                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-500 hover:text-red-600 transition-colors" title="Delete Category">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                    
                                                    {{-- Manage/View My Quizzes --}}
                                                    <a href="{{ route('categories.show', $category->id) }}" class="text-gray-500 hover:text-indigo-600 transition-colors" title="Manage My Quizzes">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="w-6 h-6" fill="currentColor">
                                                            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                        </svg>
                                                    </a>

                                                    {{-- Attempt/View All Quizzes (User View) --}}
                                                    <a href="{{ route('quizzes.category', $category->slug) }}" class="text-gray-500 hover:text-green-600 transition-colors" title="Attempt Quizzes">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </a>
                                                @endauth
                                                
                                                @guest
                                                    <a href="{{ route('quizzes.category', $category->id) }}" class="text-gray-500 hover:text-indigo-600 transition-colors" title="View Quiz">
                                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                                        viewBox="0 -960 960 960" 
                                                        class="w-6 h-6" 
                                                        fill="currentColor">
                                                        <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                    </svg>
                                                </a>
                                                @endguest
                                            </td>
                                            
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center">No Categories found. Create your first one!</td>
                                    </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>