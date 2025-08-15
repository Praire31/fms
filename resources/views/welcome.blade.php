@extends('layouts.app')

@section('content')
<div class="welcome-container">
    <div class="welcome-box">
        <h1>Welcome to Our Attendance System</h1>
        <p>Effortless attendance tracking at your fingertips.</p>
        <a href="{{ route('login') }}" class="btn-login">Login</a>
    </div>
</div>


<style>
/* Full viewport container */
.welcome-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 60vh; /* smaller than full viewport */
    padding: 40px 20px; /* spacing around content */
    background: linear-gradient(135deg, #4a90e2, #50e3c2);
    text-align: center;
    border-radius: 12px;
    margin: 20px auto; /* center in layout without overriding */
}

/* The card */
.welcome-box {
    background-color: rgba(255, 255, 255, 0.95);
    padding: 40px 60px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    max-width: 450px;
}

/* Typography */
.welcome-box h1 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: #333;
}

.welcome-box p {
    font-size: 1.1rem;
    margin-bottom: 25px;
    color: #666;
}

/* Button */
.btn-login {
    display: inline-block;
    background-color: #4a90e2;
    color: #fff;
    text-decoration: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: bold;
    transition: background 0.3s ease, transform 0.2s ease;
}

.btn-login:hover {
    background-color: #357ab7;
    transform: scale(1.05);
}
</style>
@endsection
