<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Fingerprint Attendance System' }}</title>

    {{-- <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}"> --}}
    
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #4a90e2, #FF7F50);
            color: white;
            text-align: center;
        }
        header {
            padding: 20px;
        }
        h1 {
            font-size: 2.5rem;
        }
        p {
            font-size: 1.2rem;
        }
        .content {
            padding: 20px;
        }
        footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .login-box {
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            width: 300px;
        }
        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
        }
        select {
            background: white;
            color: black;
        }
        button {
            background: #FFD700;
            color: black;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #FFC107;
        }
    </style>
</head>
<body>

    <header>
        <h1>Fingerprint Based Attendance Management  System</h1>
    </header>

    <div class="content">
        @yield('content')
    </div>

    <footer>
        &copy; {{ date('Y') }} Fingerprint Attendance System. All Rights Reserved.
    </footer>

    @stack('scripts')
</body>
</html>
