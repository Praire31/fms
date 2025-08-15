<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        // Get all users with department relationship, with search if provided
        $users = User::with('department')
            ->when($search, function ($q) use ($search) {
                $q->where('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            })
            ->get();

        // Get all departments with user count for display
        $departments = Department::withCount('users')->get();

        // Stats
        $totalUsers = User::count();
        $totalDepartments = Department::count();

        // Attendance report filters
        $attendanceRecords = Attendance::query()
            ->when($request->filter_user, fn($q) => $q->where('username', $request->filter_user))
            ->when($request->filter_department, fn($q) => $q->where('department', $request->filter_department))
            ->when(
                $request->filter_status && $request->filter_status != 'all',
                fn($q) => $q->where('status', $request->filter_status)
            )
            ->get();

        // These two variables are useful for the dropdowns in your Add/Edit forms
        $departmentsForForm = Department::all();
        $usersForForm = User::with('department')->get();

        return view('admin.dashboard', [
            'users' => $users,
            'departments' => $departments,
            'totalUsers' => $totalUsers,
            'totalDepartments' => $totalDepartments,
            'attendanceRecords' => $attendanceRecords,
            'usersForFilter' => User::all(),
            'departmentsForFilter' => Department::all(),
            'departmentsForForm' => $departmentsForForm,
            'usersForForm' => $usersForForm,
        ]);
    }
}
