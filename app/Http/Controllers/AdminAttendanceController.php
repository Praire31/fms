<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

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
            'status' => 'required|in:Present,On Leave,Late,Absent'
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

    // Show attendance reports with optional filters
    public function reports(Request $request)
    {
        $query = Attendance::with('user.department');

        // Apply filters if provided in query params
        if ($request->filter_status) {
            $query->where('status', $request->filter_status);
        }

        if ($request->date) {
            $query->where('date', $request->date);
        }

        if ($request->filter_user) {
            $query->where('user_id', $request->filter_user);
        }

        if ($request->filter_department) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department_id', $request->filter_department);
            });
        }

        $attendanceRecords = $query->get();

        // --- Widget numbers ---
        $today = Carbon::today()->toDateString();

        $todayAttendance = Attendance::where('date', $today)->count();
        $lateUsers = Attendance::where('date', $today)->where('status', 'Late')->count();
        $notCheckedIn = User::whereDoesntHave('attendances', function($q) use ($today) {
            $q->where('date', $today);
        })->count();

        return view('admin.reports', compact(
            'attendanceRecords',
            'todayAttendance',
            'lateUsers',
            'notCheckedIn'
        ));
    }
}
