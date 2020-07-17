@extends('layouts.app')

@section('title', 'Start')

@section('content')
    @if (Session::has('success'))
        <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md" role="alert">
            <div class="flex">
                <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                <div class="flex items-center justify-center">
                    <p class="font-bold">{{ Session::get('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (Session::has('errorMessage'))
        <div class="bg-red-100 border-t-4 border-red-500 rounded-b text-teal-900 px-4 py-3 shadow-md" role="alert">
            <div class="flex">
                <div class="py-1"><svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                <div>
                    <p class="font-bold">An error has occured.</p>
                    <p class="text-sm">{{ Session::get('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex w-full flex-row">
        <div class="flex flex-col justify-center">
            @if ($accounts ?? null && count($accounts) > 0)
                <p class="text-lg m-8">Which account do you want to import?</p>

                @foreach ($accounts as $account)
                    <a class="shadow bg-purple-500 hover:bg-purple-400 focus:shadow-outline focus:outline-none text-white font-bold m-2 py-2 px-4 rounded text-center self-center w-56"
                       href="/import/{{ $account['hash'] }}">
                        {{ $account['name'] }}
                    </a>
                @endforeach
            @endif

            <a class="shadow bg-teal-400 hover:bg-teal-300 focus:shadow-outline focus:outline-none text-white font-bold m-2 py-2 px-4 rounded text-center self-center w-56"
               href="/start/details">
                Start without preset
            </a>
        </div>
    </div>
@endsection
