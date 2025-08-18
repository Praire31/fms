<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Audit; // Audit model
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    // Display the user dashboard
    public function index()
    {
        $user = Auth::user();

        $attendances = Attendance::with('user.department')
            ->where('user_id', $user->id)
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

        $attendance = Attendance::with('user.department')
            ->where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();

        // Format response with user name and department
        $data = $attendance->map(function($item){
            return [
                'id' => $item->id,
                'date' => $item->date,
                'time_in' => $item->time_in,
                'time_out' => $item->time_out,
                'status' => $item->status,
                'user_name' => $item->user->name ?? '-',
                'department' => $item->user->department->name ?? '-',
            ];
        });

        return response()->json($data);
    }

    // Mark attendance (AJAX)
    public function markAttendance(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $signInDeadline = Carbon::today()->setHour(8)->setMinute(0)->setSecond(0); // 08:00
        $signOutTime   = Carbon::today()->setHour(14)->setMinute(0)->setSecond(0);  // 14:00
        $now = Carbon::now();

        // Get or create today's attendance record
        $record = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['status' => 'Absent']
        );

        $actionPerformed = '';
        $description = '';

        // First scan → Time In
        if (!$record->time_in) {
            $record->time_in = $now->format('H:i:s');
            $record->status = $now->gt($signInDeadline) ? 'Late' : 'Present';
            $record->save();

            $actionPerformed = 'Time In';
            $description = "User marked time in at {$record->time_in}";
        }
        // Second scan → Time Out
        elseif (!$record->time_out) {
            if ($now->lt($signOutTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot sign out before 14:00.'
                ]);
            }

            $record->time_out = $now->format('H:i:s');

            // Correct status if needed
            if (!$record->time_in) {
                $record->status = 'Absent';
            } elseif ($record->status != 'Late') {
                $record->status = 'Present';
            }

            $record->save();

            $actionPerformed = 'Time Out';
            $description = "User marked time out at {$record->time_out}";
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'You have already completed your attendance for today.'
            ]);
        }

        // Log audit record
        if ($actionPerformed) {
            Audit::create([
                'user_id'    => $user->id,
                'role'       => $user->role,
                'action'     => $actionPerformed,
                'target'     => 'Attendance',
                'ip_address' => $request->ip(),
                'description'=> $description,
            ]);
        }

        // Return full info including user and department
        $record->load('user.department');

        return response()->json([
            'success' => true,
            'message' => $record->time_out ? 'Time Out recorded!' : 'Time In recorded!',
            'attendance' => [
                'id' => $record->id,
                'date' => $record->date,
                'time_in' => $record->time_in,
                'time_out' => $record->time_out,
                'status' => $record->status,
                'user_name' => $record->user->name ?? '-',
                'department' => $record->user->department->name ?? '-',
            ]
        ]);
    }
}
