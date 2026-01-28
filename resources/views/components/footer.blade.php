<footer class="bg-gray-800 text-white py-6">
    <div class="container mx-auto text-center">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Quiz System') }}. All Rights Reserved.</p>
        <div class="m-3 space-x-2">
            <a href="{{ route('dashboard') }}" class="hover:underline hover:text-gray-300">Home</a>
            <a href="{{ route('categories.index') }}" class="hover:underline hover:text-gray-300">Categories</a>
            <a href="{{ route('quizzes.index') }}" class="hover:underline hover:text-gray-300">My Quizzes</a>
        </div>
    </div>

</footer>