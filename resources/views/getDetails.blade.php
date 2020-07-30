@extends('layouts.app')

@section('title', 'Get Details')

@section('content')
    <div class="flex flex-col items-center justify-center w-full">
        <div class="mb-4">
            <h2 class="block text-gray-700 text-2xl font-bold mb-2">
                Enter your configuration to start an import
            </h2>
        </div>

        <div class="flex flex-col items-center justify-center max-w-xs">
            <form class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" method="post">
                @csrf

                <label class="block text-gray-700 text-sm font-bold mb-1" for="bank_url">
                    FinTs bank url
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4 @error('bank_url') border-red-500 @enderror" name="bank_url" type="text" id="bank_url">

                @error('bank_url')
                <span class="flex items-center text-red-600 text-sm mb-4">
                    {{ $message }}
                </span>
                @enderror

                <label class="block text-gray-700 text-sm font-bold mb-1" for="bank_code">
                    FinTs bank code
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4 @error('bank_code') border-red-500 @enderror" name="bank_code" type="text" id="bank_code">

                @error('bank_code')
                <span class="flex items-center text-red-600 text-sm mb-4">
                    {{ $message }}
                </span>
                @enderror

                <label class="block text-gray-700 text-sm font-bold mb-1" for="bank_iban">
                    IBAN of your account
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4 @error('bank_iban') border-red-500 @enderror" name="bank_iban" type="text" id="bank_iban">

                @error('bank_iban')
                <span class="flex items-center text-red-600 text-sm mb-4">
                    {{ $message }}
                </span>
                @enderror

                <label class="block text-gray-700 text-sm font-bold mb-1" for="username">
                    Username
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4 @error('username') border-red-500 @enderror" name="username" type="text" id="username">

                @error('username')
                <span class="flex items-center text-red-600 text-sm mb-4">
                    {{ $message }}
                </span>
                @enderror

                <label class="block text-gray-700 text-sm font-bold mb-1" for="password">
                    Password
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4 @error('password') border-red-500 @enderror" name="password" type="password" id="password">

                @error('password')
                <span class="flex items-center text-red-600 text-sm mb-4">
                    {{ $message }}
                </span>
                @enderror

                <label class="block text-gray-700 text-sm font-bold mb-1" for="tan_mode">
                    Choose a TAN mode you usually use:
                </label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4 @error('tan_mode') border-red-500 @enderror" name="tan_mode" id="tan_mode">
                    <!-- taken from https://github.com/bnw/firefly-iii-fints-importer - thanks! -->
                    <option value="911">chipTAN optisch</option>
                    <option value="910">chipTAN manuell</option>
                    <option value="912">chipTAN per USB</option>
                    <option value="913">chipTAN per QR</option>
                    <option value="920">smsTAN</option>
                    <option value="921">pushTAN</option>
                    <option value="900">iTAN</option>
                    <option value="902">photoTAN</option>
                    <option value="901">mobileTAN</option>
                    <option value="-1">No tan - use for ING</option>
                </select>

                @error('tan_mode')
                <span class="flex items-center text-red-600 text-sm mb-4">
                    {{ $message }}
                </span>
                @enderror

                <label class="block text-gray-700 text-sm font-bold mb-1" for="budget_id">
                    YNAB budget id
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4 @error('budget_id') border-red-500 @enderror" name="budget_id" type="text" id="budget_id">

                @error('budget_id')
                <span class="flex items-center text-red-600 text-sm mb-4">
                    {{ $message }}
                </span>
                @enderror

                <label class="block text-gray-700 text-sm font-bold mb-1" for="account_id">
                    YNAB account id
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4 @error('account_id') border-red-500 @enderror" name="account_id" type="text" id="account_id">

                @error('account_id')
                <span class="flex items-center text-red-600 text-sm mb-4">
                    {{ $message }}
                </span>
                @enderror

                <div class="flex items-center justify-center">
                    <button class="shadow bg-purple-500 hover:bg-purple-400 focus:shadow-outline focus:outline-none text-white font-bold py-2 px-4 rounded" type="submit" value="Submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection
