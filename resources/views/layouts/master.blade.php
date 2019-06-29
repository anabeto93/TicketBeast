<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TicketBeast')</title>

    <link rel="stylesheet" href="{{ asset('a_css/app.css') }}">
    @include('scripts.app')
</head>
<body class="bg-dark">
<div id="app">
    @yield('body')
</div>

<style>
    .small-svg {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>
@stack('beforeScripts')
<script src="{{ asset('js/app.js') }}"></script>
@stack('afterScripts')
{{ svg_spritesheet() }}
</body>
</html>