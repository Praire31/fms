@extends('layouts.user-dashboard')

@section('content')
<h2>Change Your Password</h2>

<form action="{{ route('force.change.password.update') }}" method="POST">
    @csrf
    <label>New Password:</label>
    <input type="password" name="password" required>

    <label>Confirm Password:</label>
    <input type="password" name="password_confirmation" required>

    <button type="submit">Update Password</button>
</form>
@endsection
