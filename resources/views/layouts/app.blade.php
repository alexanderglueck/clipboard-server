<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    @vite(['resources/css/app.css'])
</head>
<body>

@yield('content')

<footer>
    <hr>
    <ul>
        <li><a href="{{ route('pages.index') }}">Universal Clipboard App</a></li>
        <li><a href="{{ route('pages.privacy-policy') }}">Privacy Policy</a></li>
        <li><a href="{{ route('pages.site-notice') }}">Site Notice</a></li>
    </ul>
</footer>
</body>
</html>
