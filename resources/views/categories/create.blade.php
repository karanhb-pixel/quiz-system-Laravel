
<x-app-layout>

     <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Category') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-gray-700">Category Name</label>
                        <input type="text" name="name" class="border-gray-300 rounded shadow-sm w-full">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Hidden or static field for creator if required by your migration -->
                    <input type="hidden" name="creator" value="{{ auth()->user()->name }}">

                    <button type="submit" class="bg-gray-500 text-white px-4 py-2 rounded">
                        Save Category
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>