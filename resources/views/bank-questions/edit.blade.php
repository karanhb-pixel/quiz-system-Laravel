<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit :type Question in :bank', ['type' => ucfirst(str_replace('_', ' ', $type)), 'bank' => $bank->name]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('bank-questions.update', [$bank, $question, $type]) }}">
                        @csrf
                        @method('PUT')

                        @if($type === 'mcq')
                            @include('bank-questions.partials.mcq-form')
                        @elseif($type === 'fill_blank')
                            @include('bank-questions.partials.fill-blank-form')
                        @elseif($type === 'code')
                            @include('bank-questions.partials.code-form')
                        @endif

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('question-banks.show', $bank) }}" class="mr-4 text-gray-600 hover:text-gray-800">Cancel</a>
                            <x-primary-button>
                                {{ __('Update Question') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>