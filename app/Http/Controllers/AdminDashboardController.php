<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminDashboardController extends Controller
{
    // ---------------------- DASHBOARD ----------------------
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'profile');
        $search = $request->search;

        // Users (exclude logged-in admin)
        $users = User::with('department')
            ->where('id', '!=', auth()->id())
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%")
                                        ->orWhere('email', 'like', "%$search%"))
            ->get();

        // Departments
        $departments = Department::withCount('users')->get();

        // Stats
        $totalUsers = User::where('id', '!=', auth()->id())->count();
        $totalDepartments = Department::count();

        // Attendance with filters
        $attendanceRecords = Attendance::with('user.department')
            ->when($request->filter_user, fn($q) => $q->where('user_id', $request->filter_user))
            ->when($request->filter_department, fn($q) => $q->whereHas('user', fn($sub) => $sub->where('department_id', $request->filter_department)))
            ->when($request->filter_status && $request->filter_status != 'all', fn($q) => $q->where('status', $request->filter_status))
            ->get();

        // Audits
        $audits = Audit::with('user')->latest()->get();

        // Users/Departments for filters
        $usersForFilter = User::all();
        $departmentsForFilter = Department::all();

        return view('admin.dashboard', compact(
            'users', 'departments', 'totalUsers', 'totalDepartments',
            'attendanceRecords', 'usersForFilter', 'departmentsForFilter',
            'activeTab', 'audits'
        ));
    }

    // ---------------------- USERS CRUD ----------------------
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'department_id' => 'required|exists:departments,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
            'force_password_change' => true,
        ]);

        // Assign default role
        $user->assignRole('User');

        // Log audit
        Audit::create([
            'user_id' => auth()->id(),
            'role' => auth()->user()->getRoleNames()->implode(', '),
            'action' => 'Create',
            'target' => 'User: ' . $user->name,
            'ip_address' => request()->ip(),
            'description' => 'Created a new user account'
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
                         ->with('success', 'User added successfully!');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'department_id' => 'required|exists:departments,id'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
        ]);

        // Log audit
        Audit::create([
            'user_id' => auth()->id(),
            'role' => auth()->user()->getRoleNames()->implode(', '),
            'action' => 'Update',
            'target' => 'User: ' . $user->name,
            'ip_address' => request()->ip(),
            'description' => 'Updated a user account'
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
                         ->with('success', 'User updated successfully!');
    }

    public function deleteUser($id)
    {
        if (!auth()->user()->can('users.delete')) {
            return redirect()->route('admin.dashboard', ['tab' => 'users'])
                             ->with('error', '❌ You do not have permission to delete users.');
        }

        $user = User::findOrFail($id);
        $userName = $user->name;
        $user->delete();

        // Log audit
        Audit::create([
            'user_id' => auth()->id(),
            'role' => auth()->user()->getRoleNames()->implode(', '),
            'action' => 'Delete',
            'target' => 'User: ' . $userName,
            'ip_address' => request()->ip(),
            'description' => 'Deleted a user account'
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
                         ->with('success', 'User deleted successfully!');
    }

    // ---------------------- ATTENDANCE REPORTS ----------------------
    public function attendanceReports(Request $request)
{
    $activeTab = $request->input('tab', 'reports'); // default to 'reports' tab
    $filterUser = $request->filter_user;
    $filterDepartment = $request->filter_department;
    $startDate = $request->start_date;
    $endDate = $request->end_date;
    $status = $request->filter_status;

    $users = User::with('department')->get();
    $departments = Department::all();
    $totalUsers = User::count();
    $totalDepartments = Department::count();
    $audits = Audit::with('user')->latest()->get();

    $query = Attendance::with(['user', 'user.department']);

    if ($filterUser) {
        $query->where('user_id', $filterUser);
    }

    if ($filterDepartment) {
        $query->whereHas('user.department', function ($q) use ($filterDepartment) {
            $q->where('id', $filterDepartment);
        });
    }

    if ($startDate) {
        $query->whereDate('date', '>=', $startDate);
    }

    if ($endDate) {
        $query->whereDate('date', '<=', $endDate);
    }

    if ($status && $status !== 'all') {
        $query->where('status', $status);
    }

    $attendanceRecords = $query->orderBy('date', 'desc')->get();

    $usersForFilter = User::all();
    $departmentsForFilter = Department::all();

    return view('admin.dashboard', compact(
        'users', 'departments', 'totalUsers', 'totalDepartments',
        'attendanceRecords', 'usersForFilter', 'departmentsForFilter',
        'activeTab', 'audits'
    ));
}


    public function clearAttendanceFilters()
    {
        return redirect()->route('admin.attendance.reports')
                         ->with('success', '✅ Filters cleared successfully.');
    }
}
