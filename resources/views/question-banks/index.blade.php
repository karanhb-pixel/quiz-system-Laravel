<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Question Banks') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Manage Question Banks</h3>
                        <a href="{{ route('question-banks.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create New Bank
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left">Name</th>
                                    <th class="px-4 py-2 text-left">Category</th>
                                    <th class="px-4 py-2 text-left">Questions</th>
                                    <th class="px-4 py-2 text-left">Creator</th>
                                    <th class="px-4 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($banks as $bank)
                                <tr class="border-t">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('question-banks.show', $bank) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $bank->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">{{ $bank->category->name }}</td>
                                    <td class="px-4 py-2">{{ $bank->mcq_questions_count }}</td>
                                    <td class="px-4 py-2">{{ $bank->user->name }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('question-banks.show', $bank) }}" class="text-blue-600 hover:text-blue-800 mr-2">View</a>
                                        @if(Auth::user()->can('update', $bank))
                                        <a href="{{ route('question-banks.edit', $bank) }}" class="text-green-600 hover:text-green-800 mr-2">Edit</a>
                                        @endif
                                        @if($bank->total_questions > 0)
                                        <a href="{{ route('bank-quiz.start', $bank) }}" class="bg-purple-500 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm">
                                            Take Quiz
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $banks->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>