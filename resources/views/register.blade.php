@extends('layouts.app')

@section('content')
    <div class="login-box">
        <h2>User Registration</h2>
        <form action="{{ route('user.store') }}" method="POST">
            @csrf
            <label for="name">Username</label><br><br>
            <input type="text" name="name" placeholder="name" value="{{ old("name") }}"><br><br>
            @error("name")
                <p>{{ $message }}</p>
            @enderror

            <label for="email">Email Address</label><br><br>
            <input type="email" id="email" name="email" placeholder="email"><br><br>
            @error("email")
                <p>{{ $message }}</p>
            @enderror

            <label for="password">Password</label><br><br>
            <input type="password" name="password" placeholder="password"><br><br>
            @error("password")
                <p>{{ $message }}</p>
            @enderror

            <label for="cpassword">Confirm Password</label><br><br>
            <input id="cpassword" type="password" name="password_confirmation" placeholder="confirm_password"><br><br>

            <input type="submit" value="Register"><br><br>
        </form>

        <p style="margin-top: 10px; font-size: 0.9rem;">
            <a href="{{ route('user.login') }}" style="color: yellow;">← Back to Login</a>
        </p>
    </div>
@endsection
