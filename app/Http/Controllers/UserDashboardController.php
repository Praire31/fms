<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    // Display the user dashboard
    public function index()
    {
        $user = Auth::user();
        $attendances = Attendance::where('user_id', $user->id)
                        ->orderBy('date', 'desc')
                        ->get();

        return view("user.dashboard", [
            'attendances' => $attendances
        ]);
    }

    // Get attendance records (AJAX)
    public function getAttendance()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
                        ->orderBy('date', 'desc')
                        ->get(['date', 'time_in', 'time_out', 'status']);
        return response()->json($attendance);
    }

    // Mark attendance (simulate scan)
    public function markAttendance(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // Check if attendance already exists for today
        $record = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'time_in' => Carbon::now()->format('H:i:s'),
                'status' => 'Present'
            ]
        );

        // If already exists, mark time_out instead
        if (!$record->wasRecentlyCreated && !$record->time_out) {
            $record->time_out = Carbon::now()->format('H:i:s');
            $record->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully!',
            'attendance' => [
                'date' => $record->date,
                'time_in' => $record->time_in,
                'time_out' => $record->time_out ?? '-',
                'status' => $record->status,
            ]
        ]);
    }
}
