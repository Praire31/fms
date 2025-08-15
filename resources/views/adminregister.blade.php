@extends('layouts.app')

@section('content')
    <div class="login-box">
        <h2>Admin Registration</h2>
        <form action="{{ route('admin.register.submit') }}" method="POST">
            @csrf
      <label for="name">Username</label><br><br>
      <input type="text" name="name" placeholder="name" required><br><br>

      <label for="email">Email Address</label><br><br>
      <input type="email" id="email" name="email" placeholder="email"><br><br>

      <label for="password">Password</label><br><br>
      <input type="password" name="password" placeholder="password"><br><br>

      <label for="cpassword">Confirm Password</label><br><br>
      <input type="password" name="password_confirmation" placeholder="confirm password"><br><br>

      <input type="submit" value="Register"><br><br>

        </form>

        <p style="margin-top: 10px; font-size: 0.9rem;">
            <a href="{{ route('admin.login') }}" style="color: yellow;">← Back to Login</a>
        </p>
    </div>
@endsection
