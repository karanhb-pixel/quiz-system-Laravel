<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-6">{{ $quiz->title }}</h1>

            <form action="{{ route('quizzes.submit', $quiz) }}" method="POST">
                @csrf
                @foreach($quiz->questions as $index => $question)
                    <div class="bg-white p-6 rounded-lg shadow mb-6">
                        <p class="font-semibold text-lg mb-4">
                            Q{{ $index + 1 }}: {{ $question->question_text }}
                        </p>

                        <div class="space-y-3">
                            @foreach(['a', 'b', 'c', 'd'] as $letter)
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $letter }}" class="mr-3 text-blue-600" required>
                                    <span>{{ $question->$letter }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700">
                    Submit My Answers
                </button>
            </form>
        </div>
    </div>
</x-app-layout>