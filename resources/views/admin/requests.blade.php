<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Access Requests
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Requested Date</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingUsers as $user)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-4 px-4">{{ $user->name }}</td>
                                <td class="py-4 px-4">{{ $user->email }}</td>
                                <td class="py-4 px-4 text-sm text-gray-500">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td class="py-4 px-4 text-center">
                                <td class="flex justify-center space-x-2 py-4">
                                    <form action="{{ route('admin.approve', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                                            Approve
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.reject', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-red-100 text-red-600 px-4 py-2 rounded-lg hover:bg-red-200 text-sm">
                                            Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">
                                    No pending admin requests at the moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>