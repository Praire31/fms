@extends('layouts.admin-dashboard')

@section('title', 'Manual Attendance Input')

@section('content')
    <div class="card max-w-full p-0 bg-transparent shadow-none">
        <h2>Manual Attendance Input</h2>

        @if(session('success'))
            <div style="color:green; margin-bottom:10px;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Manual Attendance Form -->
        <form action="{{ route('attendance.manual.store') }}" method="POST"
            style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px;">
            @csrf
            <label>User</label>
            <select name="user_id" required>
                <option value="" disabled selected>Select User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->department->name ?? '-' }})
                    </option>
                @endforeach
            </select>

            <label>Date</label>
            <input type="date" name="date" required>

            <label>Time In</label>
            <input type="time" name="time_in">

            <label>Time Out</label>
            <input type="time" name="time_out">

            <label>Status</label>
            <select name="status" required>
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
                <option value="Late">Late</option>
                <option value="On Leave">On Leave</option>
            </select>

            <button type="submit"
                style="background:#0077aa;color:white;padding:8px 15px;border:none;border-radius:4px;">Save</button>
        </form>

    </div>
@endsection