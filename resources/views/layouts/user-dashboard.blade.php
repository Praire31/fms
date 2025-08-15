<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Fingerprint System')</title>
    <link rel="stylesheet" href="{{ asset('css/userdashboard.css') }}">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    @yield('user-content')

    {{-- Include child scripts at bottom --}}
    @yield('scripts')
</body>
</html>
