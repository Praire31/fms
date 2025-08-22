<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceController extends Controller
{
    // Show manual attendance form
    public function showForm()
    {
        $users = User::all();
        return view('admin.manual-attendance', compact('users'));
    }

    // Store manual attendance
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|string'
        ]);

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'date' => $request->date
            ],
            [
                'time_in' => $request->time_in,
                'time_out' => $request->time_out,
                'status' => $request->status
            ]
        );

        return redirect()->back()->with('success', 'Attendance recorded successfully!');
    }
}
