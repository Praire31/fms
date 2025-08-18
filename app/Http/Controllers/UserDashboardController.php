<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;


class UserDashboardController extends Controller
{
    public function index()
    {
        return view("user.dashboard");
    }

    public function getAttendance()
    {
        $attendance = Attendance::where('user_id', Auth::id())->get(['date', 'time_in', 'time_out', 'status']);
        return response()->json($attendance);
    }

    public function markAttendance(Request $request)
    {
        $user = Auth::user();

        // Simplified logic
        $today = now()->toDateString();
        $record = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['time_in' => now()->format('H:i:s'), 'status' => 'Present']
        );

        if ($record->wasRecentlyCreated === false) {
            $record->time_out = now()->format('H:i:s');
            $record->save();
        }

        return response()->json([
            'message' => 'Attendance marked successfully!',
            'time_in' => $record->time_in,
            'time_out' => $record->time_out,
            'status' => $record->status,
        ]);
    }
   // Show force change password form
public function showForceChangePassword()
{
    return view('user.force_change_password');
}

// Update password
public function updateForceChangePassword(Request $request)
{
    $request->validate([
        'password' => 'required|min:6|confirmed',
    ]);

    $user = auth()->user();
    $user->password = bcrypt($request->password);
    $user->force_password_change = 0; // Mark as changed
    $user->save();

    return redirect()->route('user.dashboard')->with('success', 'Password updated successfully!');
}


}
