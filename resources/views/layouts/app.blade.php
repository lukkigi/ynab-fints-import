<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

        <!-- Styles -->
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    </head>
    <body class="bg-gray-100 antialiased leading-none">
        <div class="container mx-auto flex items-center justify-center flex-col h-full self-center">
            <div class="flex justify-center p-4 w-3/4 m-6">
                <h1 class="text-4xl text-center">YNAB FinTs Importer</h1>
            </div>

            <div class="m-4">
                @yield('content')
            </div>

            <footer class="flex flex-row justify-center bottom-0 p-4">
                <a class="m-4" href="https://github.com/lukkigi" target="_blank">GitHub</a>
            </footer>
        </div>
    </body>
</html>
