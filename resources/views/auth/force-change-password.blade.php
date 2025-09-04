{{-- resources/views/auth/force-change-password.blade.php --}}

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
    
    <div class="auth-container">
        <h2>Change Your Password</h2>
        <p>You must change your password before continuing.</p>
        <form action="{{ route('force.change.password.update') }}" method="POST">
            @csrf
       <label for="password">New password</label><br><br>
       <input type="password" name="password" placeholder="password"><br><br>

       <label for="password">Confirm Password</label><br><br>
       <input type="password" name="password_confirmation" placeholder="password"><br><br>
      
       <input type="submit" value="Update Password"><br><br>
        </form>

    
    </div>
@endsection

