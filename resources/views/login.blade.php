@extends('layouts.app')

@section('content')
{{-- Show success message if any --}}
    @if(session('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

     @if(session('error'))
        <div style="background-color: #d4edda; color: #cd4607; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="login-box">
        <h2>User Login</h2>
        <form action="{{ route('login') }}" method="POST">
            @csrf
       <label for="email">Email</label><br><br>
       <input type="text" name="email" placeholder="email"><br><br>

       <label for="password">Password</label><br><br>
       <input type="password" name="password" placeholder="password"><br><br>
      
       <input type="submit" value="Login"><br><br>
        </form>

        <p style="margin-top: 15px;">Don't have an account?</p>
        <a href="{{ route('user.register') }}">
            <button type="button">Register</button>
        </a>

        <p style="margin-top: 10px; font-size: 0.9rem;">
            <a href="{{ route('home') }}" style="color: yellow;">← Back to Home</a>
        </p>
    </div>
@endsection
