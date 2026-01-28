<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex flex-col items-center">
                    <h1 class="text-3xl text-gray-900 font-bold p-5">Check Your Skill</h1>
                    <div class="w-full max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <input type="text" name="search" placeholder="Search Quiz..."
                                class="w-full pl-10 pr-4 py-2 rounded-2xl shadow-sm text-gray-700 border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none transition-all">
                        </div>
                    </div>


                    {{-- Categories List --}}
                    <div class="flex justify-between mb-4 mt-8">
                         <h3 class="text-lg font-medium">All Categories</h3>
                    </div >

                    <div class="relative overflow-x-auto w-full max-w-lg">
                        <table class="border border-gray-200 w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">Category Name</th>
                                    <th class="px-6 py-3">Quiz Count</th>
                                    <th class="px-6 py-3">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($categories as $category)
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $category->name }}
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $category->quizzes_count }}
                                        </td>
                                            
                                        <td class="px-6 py-4 pl-5 flex gap-2">
                                                    
                                            <a href={{ route('quizzes.category',$category->slug) }} class="text-gray-500 hover:text-indigo-600 transition-colors" title="View Quiz">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 -960 960 960"
                                                    class="w-6 h-6"
                                                    fill="currentColor">
                                                    <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>
                                                </svg>
                                            </a>
                                                    
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
                

                    {{-- Recent Result Section --}}
                    <div class="mt-8 w-full  max-w-md">
                        @auth
                            <h3 class="text-lg font-bold mb-4">My Recent Scores</h3>
                                <div class="bg-white shadow rounded-lg overflow-hidden">
                                    <table class="w-full text-left">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2">Quiz</th>
                                                <th class="px-4 py-2">Score</th>
                                                <th class="px-4 py-2">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentResults as $result)
                                            <tr class="border-t">
                                                <td class="px-4 py-2">{{ $result->quiz->title }}</td>
                                                <td class="px-4 py-2 font-bold {{ $result->score_percentage >= 50 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $result->score_percentage }}%
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ $result->created_at->diffForHumans() }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>