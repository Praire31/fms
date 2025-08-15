<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Determine which tab should be active
        $activeTab = $request->input('tab', 'profile'); // default: profile

        $search = $request->search;

        // Fetch users with department relationship
        $users = User::with('department')
            ->where('id', '!=', auth()->id()) // exclude current admin
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            })
            ->get();

        // Fetch departments with user count
        $departments = Department::withCount('users')->get();

        // Dashboard stats
        $totalUsers = User::where('id', '!=', auth()->id())->count();
        $totalDepartments = Department::count();

        // Attendance records with filters
        $attendanceRecords = Attendance::query()
            ->when($request->filter_user, fn($q) => $q->where('name', $request->filter_user))
            ->when($request->filter_department, fn($q) => $q->where('department', $request->filter_department))
            ->when($request->filter_status && $request->filter_status != 'all', fn($q) => $q->where('status', $request->filter_status))
            ->get();

        return view('admin.dashboard', [
            'users' => $users,
            'departments' => $departments,
            'totalUsers' => $totalUsers,
            'totalDepartments' => $totalDepartments,
            'attendanceRecords' => $attendanceRecords,
            'usersForFilter' => User::all(),
            'departmentsForFilter' => Department::all(),
            'activeTab' => $activeTab, // pass active tab to Blade
        ]);
    }

    // Store new user
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'department_id' => 'required|exists:departments,id'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
        ]);

        // Redirect back to Users tab with active tab preserved
        return redirect()->route('admin.dashboard', ['tab' => 'users'])
                         ->with('success', 'User added successfully!');
    }

    // Update existing user
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

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
                         ->with('success', 'User updated successfully!');
    }

    // Delete user
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
                         ->with('success', 'User deleted successfully!');
    }
}
