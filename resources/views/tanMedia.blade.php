@extends('layouts.app')

@section('title', 'Choose TAN device')

@section('content')
    <div class="flex flex-col items-center justify-center w-full">
        <div class="mb-4">
            <h2 class="block text-gray-700 text-2xl font-bold mb-2">
                Importing transactions from {{ $bankName }}
            </h2>
        </div>

        <div class="flex flex-col items-center justify-center max-w-xs">
            <form class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" method="post">
                @csrf

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-3" for="tanMedium">
                        Choose your TAN device:
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="tanMedium" id="tanMedium">
                        @for ($i = 0; $i < count($availableTanMedia); $i++)
                            @if ($availableTanMedia[$i]->getName() != null and $availableTanMedia[$i]->getPhoneNumber() != null)
                                <option value="{{ $i }}">{{ $availableTanMedia[$i]->getName() }} - {{ $availableTanMedia[$i]->getPhoneNumber() }}</option>
                            @elseif ($availableTanMedia[$i]->getName() != null)
                                <option value="{{ $i }}">{{ $availableTanMedia[$i]->getName() }}</option>
                            @elseif ($availableTanMedia[$i]->getPhoneNumber() != null)
                                <option value="{{ $i }}">{{ $availableTanMedia[$i]->getPhoneNumber() }}</option>
                            @endif
                        @endfor
                    </select>
                </div>

                <div class="flex items-center justify-center">
                    <button class="shadow bg-purple-500 hover:bg-purple-400 focus:shadow-outline focus:outline-none text-white font-bold py-2 px-4 rounded" type="submit" value="Next">Next</button>
                </div>
            </form>
        </div>
    </div>
@endsection
