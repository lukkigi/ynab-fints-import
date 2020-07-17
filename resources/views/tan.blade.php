@extends('layouts.app')

@section('title', 'TAN')

@section('content')
    <div class="flex flex-col items-center justify-center w-full">
        <div class="mb-4">
            <h2 class="block text-gray-700 text-2xl font-bold mb-2">
                Importing transactions from {{ $bankName }}
            </h2>
        </div>

        <div class="flex flex-col items-center justify-center max-w-xs">
            @isset($tanChallengeImageData)
                <img src="data:{{ htmlspecialchars($tanChallengeImageMimeType) }};base64,{{ $tanChallengeImageData }}" alt="TAN image" />
            @endisset

            <form class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" method="post">
                @csrf

                <p class="block text-gray-700 text-md font-bold mb-6">TAN Device: {{ $tanMedium }}</p>

                <label class="block text-gray-700 text-sm font-bold mb-3" for="tanCode">
                    Enter your TAN code here:
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-6" name="tanCode" type="text" id="tanCode">

                <div class="flex items-center justify-center">
                    <button class="shadow bg-purple-500 hover:bg-purple-400 focus:shadow-outline focus:outline-none text-white font-bold py-2 px-4 rounded" type="submit" value="Submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection
