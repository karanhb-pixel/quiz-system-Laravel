<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 ">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex flex-col items-center">
                    <h1 class="text-3xl text-gray-900 font-bold p-5">
                        User Attempted Quiz List
                    </h1>
                
                    {{-- Recent Result Section --}}
                    <div class="mt-8 w-full  max-w-md">
                        @auth
                            <h3 class="text-lg font-bold mb-4">My Quizzes</h3>
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