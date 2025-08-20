{{-- Toast Notifications --}}
@if(session('success') || session('error'))
    <div id="toast" 
         style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
                min-width: 280px; max-width: 500px;
                background: {{ session('success') ? '#4CAF50' : '#f44336' }}; 
                color: white; padding: 14px 20px; 
                border-radius: 8px; font-size: 15px; text-align: center;
                z-index: 9999; box-shadow: 0 2px 8px rgba(0,0,0,0.25);
                display: flex; align-items: center; justify-content: center; gap: 10px;">
        
        <span>{{ session('success') ?? session('error') }}</span>
        <button onclick="document.getElementById('toast').remove()" 
                style="background: transparent; border: none; color: white; font-size: 18px; font-weight: bold; cursor: pointer;">
            &times;
        </button>
    </div>

    <script>
        setTimeout(() => {
            let toast = document.getElementById('toast');
            if (toast) {
                toast.style.transition = "opacity 0.5s";
                toast.style.opacity = "0";
                setTimeout(() => toast.remove(), 500);
            }
        }, 3000); // Auto hide after 3 seconds
    </script>
@endif


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title',"Finger Print System")</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
    @yield("admin-content")
</body>
</html>
