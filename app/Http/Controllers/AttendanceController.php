<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function mark(Request $request)
    {
        $fingerprintId = $request->query('user_id'); // ESP32 sends ?user_id=1

        if (!$fingerprintId) {
            return response()->json(['error' => 'No fingerprint ID provided'], 400);
        }

        // Find user by fingerprint_id
        $user = User::where('fingerprint_id', $fingerprintId)->first();

        if (!$user) {
            return response()->json(['error' => "No user found with fingerprint_id: $fingerprintId"], 404);
        }

        $today = Carbon::today()->toDateString();

        // Get today's attendance (if exists)
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->time_in && $attendance->time_out) {
            // Already signed in and out
            return response()->json([
                'success' => false,
                'message' => "Attendance already recorded for today."
            ]);
        }

        if (!$attendance || !$attendance->time_in) {
            // Sign-in
            $status = 'Present';
            $lateThreshold = Carbon::createFromTime(8, 30, 0); // change as needed
            if (Carbon::now()->greaterThan($lateThreshold)) {
                $status = 'Late';
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date'    => $today,
                'time_in' => Carbon::now()->toTimeString(),
                'status'  => $status,
            ]);

            // Audit
            DB::table('audits')->insert([
                'user_id' => $user->id,
                'action' => 'Sign In',
                'description' => "{$user->name} signed in at " . $attendance->time_in . " ($status)",
                'ip_address' => $request->ip(),
                'target' => 'ESP32 Attendance Scanner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$user->name} signed in at " . $attendance->time_in . " ($status)",
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'date' => $attendance->date,
                    'time_in' => $attendance->time_in,
                    'status' => $attendance->status,
                ]
            ]);
        } else {
            // Sign-out
            $attendance->update([
                'time_out' => Carbon::now()->toTimeString(),
            ]);

            // Audit
            DB::table('audits')->insert([
                'user_id' => $user->id,
                'action' => 'Sign Out',
                'description' => "{$user->name} signed out at " . $attendance->time_out,
                'ip_address' => $request->ip(),
                'target' => 'ESP32 Attendance Scanner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$user->name} signed out at " . $attendance->time_out,
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'date' => $attendance->date,
                    'time_in' => $attendance->time_in,
                    'time_out' => $attendance->time_out,
                    'status' => $attendance->status,
                ]
            ]);
        }
    }
}
