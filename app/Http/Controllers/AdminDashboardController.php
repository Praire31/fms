<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    // ---------------------- DASHBOARD ----------------------
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'profile');
        $search = $request->search;

        $users = User::with('department', 'roles')
            ->where('id', '!=', auth()->id()) // hide logged-in user
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Admin', 'User']); // only fetch Admins and Users
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->get();

        // Departments
        $departments = Department::withCount('users')->get();

        // Stats
        $totalUsers = User::where('id', '!=', auth()->id()) // exclude logged-in user
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Admin', 'User']); // only Admins + Users
            })
            ->count();

        $totalDepartments = Department::count();

        // ---------------------- DASHBOARD WIDGETS ----------------------
        $today = Carbon::today()->toDateString();

        // 1️⃣ Today’s Attendance
        $todayAttendance = Attendance::whereDate('date', $today)->count();

        // 2️⃣ Late Users (after 9:00 AM)
        $lateUsers = Attendance::whereDate('date', $today)
            ->whereNotNull('time_in')
            ->where('status', 'Late')
            ->count();

        // 3️⃣ Users not yet checked in
        $checkedInUserIds = Attendance::whereDate('date', $today)->pluck('user_id');

        $notCheckedIn = User::where('id', '!=', auth()->id()) // exclude logged-in user
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Admin', 'User']); // only Admins + Users
            })
            ->whereNotIn('id', $checkedInUserIds) // exclude users who have checked in
            ->count();


        // Attendance with filters
        $attendanceRecords = Attendance::with('user.department')
            ->when($request->filter_user, fn($q) => $q->where('user_id', $request->filter_user))
            ->when($request->filter_department, fn($q) => $q->whereHas('user', fn($sub) => $sub->where('department_id', $request->filter_department)))
            ->when($request->filter_status && $request->filter_status != 'all', fn($q) => $q->where('status', $request->filter_status))
            ->get();

        // Audits
        $auditsQuery = Audit::with('user');

        if (auth()->user()->hasRole('Super Admin')) {
            // Super Admin sees Admin + User audits, but not their own
            $auditsQuery
                ->where('user_id', '!=', auth()->id())
                ->whereHas('user.roles', fn($r) => $r->whereIn('name', ['Admin', 'User']));
        } elseif (auth()->user()->hasRole('Admin')) {
            // Admin sees only User audits
            $auditsQuery->whereHas('user.roles', fn($r) => $r->where('name', 'User'));
        } else {
            // Users see nothing
            $auditsQuery->whereRaw('0=1');
        }

        $audits = $auditsQuery->latest()->get();


        // Users/Departments for filters
        $usersForFilter = User::where('id', '!=', auth()->id())->get();
        $departmentsForFilter = Department::all();

        // Only for Super Admin audits section
        $rolesForFilter = [];
        if (auth()->user()->hasRole('Super Admin')) {
            $rolesForFilter = \Spatie\Permission\Models\Role::pluck('name');
        }
        // Only for Super Admin audits section
        $actionsForFilter = [];
        if (auth()->user()->hasRole('Super Admin')) {
            $actionsForFilter = Audit::distinct()->pluck('action');
        }

        return view('admin.dashboard', compact(
            'users',
            'departments',
            'totalUsers',
            'totalDepartments',
            'attendanceRecords',
            'usersForFilter',
            'actionsForFilter',
            'rolesForFilter',
            'departmentsForFilter',
            'activeTab',
            'audits',
            'todayAttendance',
            'lateUsers',
            'notCheckedIn'
        ));
    }

    //---------------------- MANUAL ATTENDANCE ----------------------//
    public function storeManualAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|string'
        ]);

        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'status' => $request->status,
        ]);



        // Log audit
        Audit::create([
            'user_id' => auth()->id(),
            'role' => auth()->user()->getRoleNames()->implode(', '),
            'action' => 'Create',
            'target' => 'Manual Attendance for ' . $attendance->user->name,
            'ip_address' => $request->ip(),
            'description' => 'Added manual attendance'
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'manual_attendance'])
            ->with('success', 'Manual attendance added successfully!');
    }


    // ---------------------- DEPARTMENTS CRUD ----------------------
    public function deleteDepartment($id)
    {
        // Check if user has permission to delete departments
        if (!auth()->user()->can('departments.delete')) {
            return redirect()->route('admin.dashboard', ['tab' => 'departments'])
                ->with('error', '❌ You do not have permission to delete departments.');
        }

        $department = Department::findOrFail($id);
        $departmentName = $department->name;
        $department->delete();

        // Log audit
        Audit::create([
            'user_id' => auth()->id(),
            'role' => auth()->user()->getRoleNames()->implode(', '),
            'action' => 'Delete',
            'target' => 'Department: ' . $departmentName,
            'ip_address' => request()->ip(),
            'description' => 'Deleted a department'
        ]);

        // Redirect back to Departments tab
        return redirect()->route('admin.dashboard', ['tab' => 'departments'])
            ->with('success', 'Department deleted successfully!');
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
        $today = Carbon::today()->toDateString();

        // -------------------- DASHBOARD COUNTS --------------------
        $todayAttendance = Attendance::whereDate('date', $today)->count();
        $totalUsers = User::count();
        $totalDepartments = Department::count();

        // Late users
        $lateUsers = Attendance::whereDate('date', $today)
            ->where('status', 'Late')
            ->count();

        // Users not checked in today (count)
        $checkedInUserIds = Attendance::whereDate('date', $today)
            ->pluck('user_id');

        $notCheckedIn = User::where('id', '!=', auth()->id())
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['Admin', 'User']))
            ->whereNotIn('id', $checkedInUserIds)
            ->count();

        // -------------------- FILTERS --------------------
        $filterType = $request->input('filter_type');
        $filterUser = $request->input('user_id');
        $filterDepartment = $request->input('department_id');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // -------------------- QUERY --------------------
        $attendanceRecords = collect();   // default empty
        $notCheckedInUsers = collect();   // default empty

        if ($filterType === 'not_checked_in') {
            // Get users who haven't checked in today (list)
            $notCheckedInUsers = User::where('id', '!=', auth()->id())
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['Admin', 'User']))
                ->whereNotIn('id', $checkedInUserIds)
                ->with('department')
                ->get();

        } elseif ($filterType === 'late') {
            // Users who are late today
            $attendanceRecords = Attendance::with('user.department')
                ->whereDate('date', $today)
                ->where('status', 'Late')
                ->whereNotNull('time_in')
                ->whereTime('time_in', '>', '08:00:00')
                ->orderBy('date', 'desc')
                ->get();

        } else {
            // All attendance with filters
            $attendanceRecords = Attendance::with('user.department')
                ->when($filterUser, fn($q) => $q->where('user_id', $filterUser))
                ->when($filterDepartment, fn($q) => $q->whereHas('user', fn($sub) =>
                    $sub->where('department_id', $filterDepartment)))
                ->when($status, fn($q) => $q->where('status', $status))
                ->when($startDate && $endDate, fn($q) =>
                    $q->whereBetween('date', [$startDate, $endDate]))
                ->orderBy('date', 'desc')
                ->get();
        }

        $rolesForFilter = [];
        if (auth()->user()->hasRole('Super Admin')) {
            $rolesForFilter = \Spatie\Permission\Models\Role::pluck('name');
        }

        $actionsForFilter = [];
        if (auth()->user()->hasRole('Super Admin')) {
            $actionsForFilter = Audit::distinct()->pluck('action');
        }

        $audits = collect(); // default empty

        if (auth()->user()->hasRole('Super Admin')) {
            $audits = Audit::with('user')
                ->whereHas('user.roles', fn($q) => $q->whereIn('name', ['Admin', 'User']))
                ->where('user_id', '!=', auth()->id()) // exclude self
                ->latest()
                ->get();
        } elseif (auth()->user()->hasRole('Admin')) {
            $audits = Audit::with('user')
                ->whereHas('user.roles', fn($q) => $q->where('name', 'User'))
                ->latest()
                ->get();
        }
        
        $users = User::all();
        $departments = Department::all();
        $usersForFilter = User::where('id', '!=', auth()->id())->get();
        $departmentsForFilter = Department::all();

        return view('admin.dashboard', compact(
            'activeTab',
            'attendanceRecords',
            'notCheckedInUsers',   // ✅ list
            'users',
            'departments',
            'todayAttendance',
            'totalDepartments',
            'totalUsers',
            'lateUsers',
            'notCheckedIn',        // ✅ count
            'filterType',
            'filterUser',
            'filterDepartment',
            'status',
            'startDate',
            'endDate',
            'usersForFilter',
            'departmentsForFilter',
            'rolesForFilter',
            'actionsForFilter',
            'audits'
        ));
    }






    public function clearAttendanceFilters()
    {
        return redirect()->route('admin.attendance.reports')
            ->with('success', '✅ Filters cleared successfully.');
    }

    // Audits with filters
    public function audits(Request $request)
    {
        $activeTab = 'audits';

        $today = Carbon::today()->toDateString();
        $todayAttendance = Attendance::whereDate('date', $today)->count();
        $lateUsers = Attendance::whereDate('date', $today)
            ->whereTime('time_in', '>', '08:00:00')
            ->count();

        $checkedInUserIds = Attendance::whereDate('date', $today)
            ->whereNotNull('time_in')
            ->pluck('user_id');

        $notCheckedIn = User::where('id', '!=', auth()->id()) // exclude logged-in user
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Admin', 'User']); // only Admins + Users
            })
            ->whereNotIn('id', $checkedInUserIds) // exclude users who have checked in
            ->count();


        $usersForFilter = User::where('id', '!=', auth()->id())->get();
        $departmentsForFilter = Department::all();
        $rolesForFilter = Role::all();
        $actionsForFilter = Audit::select('action')->distinct()->pluck('action');

        // Get audits excluding your own actions (current Super Admin)
        $audits = Audit::with('user')
            ->whereHas('user', fn($q) => $q->where('id', '!=', auth()->id()))
            ->when($request->filter_user, fn($q) => $q->where('user_id', $request->filter_user))
            ->when($request->filter_role, fn($q) => $q->where('role', $request->filter_role))
            ->when($request->filter_action, fn($q) => $q->where('action', $request->filter_action))
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->latest()
            ->get();

        $users = User::with('department')->where('id', '!=', auth()->id())->get(); // exclude self
        $departments = Department::all();
        $totalUsers = User::where('id', '!=', auth()->id())
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['Admin', 'User']))
            ->count();

        $totalDepartments = Department::count();
        $attendanceRecords = Attendance::with('user.department')->get();
        $departmentsForFilter = $departments;

        return view('admin.dashboard', compact(
            'activeTab',
            'users',
            'departments',
            'totalUsers',
            'totalDepartments',
            'attendanceRecords',
            'usersForFilter',
            'rolesForFilter',
            'actionsForFilter',
            'departmentsForFilter',
            'audits',
            'todayAttendance',
            'lateUsers',
            'notCheckedIn'
        ));
    }



    // Only Super Admin can delete filtered attendance
    public function deleteFilteredAttendance(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            return redirect()->route('admin.attendance.reports')
                ->with('error', '❌ You do not have permission to delete attendance records.');
        }

        $query = Attendance::query();

        // Apply filters
        if ($request->filter_user) {
            $query->where('user_id', $request->filter_user);
        }
        if ($request->filter_department) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $request->filter_department));
        }
        if ($request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }
        if ($request->filter_status && $request->filter_status !== 'all') {
            $query->where('status', $request->filter_status);
        }

        $deletedCount = $query->delete();

        return redirect()->route('admin.attendance.reports')
            ->with('success', "$deletedCount filtered attendance record(s) deleted successfully.");
    }


    // Delete a single audit (super admin only)
    public function deleteAudit($id)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            return redirect()->route('admin.audits')
                ->with('error', '❌ You do not have permission to delete audits.');
        }

        $audit = Audit::findOrFail($id);
        $audit->delete();

        return redirect()->route('admin.audits')
            ->with('success', '✅ Audit record deleted successfully.');
    }

    // Delete multiple filtered audits (super admin only)
    public function deleteFilteredAudits(Request $request)
    {
        $query = Audit::query();

        // Apply filters
        if ($request->filter_user) {
            $query->where('user_id', $request->filter_user);
        }
        if ($request->filter_role) {
            $query->where('role', $request->filter_role);
        }
        if ($request->filter_action) {
            $query->where('action', $request->filter_action);
        }
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $deletedCount = $query->delete();

        return redirect()->route('admin.audits')
            ->with('success', "$deletedCount filtered audit record(s) deleted successfully.");
    }

}
