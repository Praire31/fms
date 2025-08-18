{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin-dashboard')

@section('title', 'Admin Dashboard')

@section('admin-content')
    <div class="dashboard-container">
        {{-- Sidebar --}}
        <div class="sidebar">
            <h2>Admin Panel</h2>

            <a href="#" class="tab-link active" data-target="profile"><i class="fas fa-user"></i> Profile</a>

            <input type="checkbox" id="manage-toggle" />
            <label for="manage-toggle"><i class="fas fa-cogs"></i> Manage ▼</label>
            <div class="dropdown-content">
                <a href="#" class="tab-link" data-target="users"><i class="fas fa-users"></i> Users</a>
                <a href="#" class="tab-link" data-target="departments"><i class="fas fa-building"></i> Departments</a>
            </div>

            <a href="#" class="tab-link" data-target="reports"><i class="fas fa-calendar-check"></i> Attendance Reports</a>
            <a href="#" class="tab-link" data-target="audits"><i class="fas fa-file-alt"></i> Audits</a>
            <a href="{{ route('logout') }}" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        {{-- Main Content --}}
        <div class="main-content">

            {{-- Profile Section --}}
            <div id="profile" class="content-section active">
                <h2 style="margin-top:20px;color:#333;">Welcome, {{ auth()->user()->name }}!</h2>
                <div style="font-size:40px;color:#555;margin-bottom:20px;"><i class="fas fa-user-circle"></i></div>
                <div class="dashboard-cards">
                    <div class="card">
                        <h3>Total Departments</h3>
                        <div class="number">{{ $totalDepartments }}</div>
                    </div>
                    <div class="card">
                        <h3>Total Users</h3>
                        <div class="number">{{ $totalUsers }}</div>
                    </div>
                </div>
            </div>

            {{-- Users Section --}}
            <div id="users" class="content-section">
                <div class="card">
                    <h2 style="margin-bottom:15px;"><i class="fas fa-users"></i> Manage Users</h2>

                    <div class="user-actions" style="margin-bottom:10px;">
                        <button id="toggleAddUserBtn" class="btn add-btn">+ Add User</button>

                        <form method="GET" action="{{ route('admin.dashboard') }}" class="search-form">
                            <input type="hidden" name="tab" value="users">
                            <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}"
                                class="input-field" />
                            <button type="submit" class="btn search-btn">Search</button>
                            <a href="{{ route('admin.dashboard', ['active_tab' => 'users']) }}"
                                class="btn clear-btn">Clear</a>
                        </form>
                    </div>

                    {{-- Add User Form --}}
                    <form id="addUserForm" method="POST" action="{{ route('admin.users.store') }}"
                        style="display:none;margin-bottom:20px;">
                        @csrf
                        <input type="text" name="name" placeholder="Full Name" required
                            style="padding:8px;margin-right:5px;">
                        <input type="email" name="email" placeholder="Email" required style="padding:8px;margin-right:5px;">
                        <input type="password" name="password" placeholder="Password" required
                            style="padding:8px;margin-right:5px;">
                        <select name="department_id" required style="padding:8px;margin-right:5px;">
                            <option value="" disabled selected>Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Add User</button>
                        <button type="button" class="btn btn-secondary" onclick="hideEditUserForm()">Cancel</button>
                    </form>

                    {{-- Edit User Form --}}
                    <form id="editUserForm" method="POST" style="display:none;margin-bottom:20px;">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="id" id="editUserId">
                        <input type="text" name="name" id="editUsername" placeholder="Full Name" required
                            style="padding:8px;margin-right:5px;">
                        <input type="email" name="email" id="editEmail" placeholder="Email" required
                            style="padding:8px;margin-right:5px;">
                        <select name="department_id" id="editDepartmentId" required style="padding:8px;margin-right:5px;">
                            <option value="" disabled>Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Update User</button>
                        <button type="button" class="btn btn-secondary" onclick="hideEditUserForm()">Cancel</button>
                    </form>

                    {{-- Users Table --}}
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->department->name ?? '-' }}</td>
                                    <td>
                                        <a href="#" class="btn btn-warning btn-sm"
                                            onclick="showEditUserForm({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', {{ $user->department_id ?? 'null' }})">Edit</a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Are you sure?')"
                                                class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Departments Section --}}
            <div id="departments" class="content-section">
                <div class="card">
                    <h2><i class="fas fa-building"></i> Manage Departments</h2>

                    <div class="department-actions" style="margin-bottom:15px;">
                        <button id="toggleAddDeptBtn" class="btn btn-success">+ Add Department</button>
                        <form id="addDeptForm" method="POST" action="{{ route('admin.departments.store') }}"
                            style="display:none;">
                            @csrf
                            <input type="text" name="name" placeholder="Department Name" required
                                style="padding:8px;margin-right:5px;">
                            <button type="submit" class="btn btn-primary">Add Department</button>
                            <button type="button" class="btn btn-secondary" onclick="hideEditDeptForm()">Cancel</button>
                        </form>
                    </div>

                    {{-- Edit Department Form --}}
                    <form id="editDeptForm" method="POST" style="display:none;margin-bottom:20px;">
                        @csrf
                        <input type="hidden" name="id" id="editDeptId">
                        <input type="text" name="name" id="editDeptName" placeholder="Department Name" required
                            style="padding:8px;margin-right:5px;">
                        <button type="submit" class="btn btn-primary">Update Department</button>
                        <button type="button" class="btn btn-secondary" onclick="hideEditDeptForm()">Cancel</button>
                    </form>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Department Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $index => $dept)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $dept->name }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm"
                                            onclick="showEditDeptForm({{ $dept->id }}, '{{ $dept->name }}')">Edit</button>
                                        <form action="{{ route('admin.departments.delete', $dept->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Are you sure?')"
                                                class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Attendance Reports Section --}}
            <div id="reports" class="content-section">
                <div class="card">
                    <h2><i class="fas fa-calendar-check"></i> Attendance Reports</h2>

                    <div class="attendance-filters" style="margin-bottom:15px;">
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex gap-2 flex-wrap">
                            <select name="filter_user" class="input-field">
                                <option value="">All Users</option>
                                @foreach($usersForFilter as $user)
                                    <option value="{{ $user->id }}" {{ request('filter_user') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}</option>
                                @endforeach
                            </select>

                            <select name="filter_department" class="input-field">
                                <option value="">All Departments</option>
                                @foreach($departmentsForFilter as $dept)
                                    <option value="{{ $dept->id }}" {{ request('filter_department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>

                            <input type="date" name="start_date" value="{{ request('start_date') }}" />
                            <input type="date" name="end_date" value="{{ request('end_date') }}" />

                            <select name="filter_status" class="input-field">
                                <option value="all" {{ request('filter_status') == 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="Present" {{ request('filter_status') == 'Present' ? 'selected' : '' }}>Present</option>
                                <option value="Absent" {{ request('filter_status') == 'Absent' ? 'selected' : '' }}>Absent</option>
                                <option value="Late" {{ request('filter_status') == 'Late' ? 'selected' : '' }}>Late</option>
                                <option value="On Leave" {{ request('filter_status') == 'On Leave' ? 'selected' : '' }}>On Leave</option>
                            </select>

                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Clear Filters</a>

                            {{-- Delete Records by Filter --}}
                            <button type="submit" formaction="{{ route('admin.attendance.deleteFiltered') }}"
                                formmethod="POST" class="btn btn-danger"
                                onclick="return confirm('Delete filtered records?')">
                                @csrf
                                Delete Records
                            </button>
                        </form>
                    </div>

                    <table class="table-auto">
                        <thead class="bg-blue-700 text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendanceRecords as $index => $row)
                                <tr class="{{ $index % 2 == 0 ? 'bg-gray-100' : 'bg-white' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->user->name ?? '-' }}</td>
                                    <td>{{ $row->user->department->name ?? '-' }}</td>
                                    <td>{{ $row->date }}</td>
                                    <td>{{ $row->time_in ?? '-' }}</td>
                                    <td>{{ $row->time_out ?? '-' }}</td>
                                    <td>
                                        @if($row->time_in && $row->time_out)
                                            {{ \Carbon\Carbon::parse($row->time_in)->diff(\Carbon\Carbon::parse($row->time_out))->format('%h h %i m') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $row->status }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No attendance records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- AUDIT LOGS SECTION --}}
            <div id="audits" class="content-section">
                <h2>Audit Logs</h2>

                <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
                    <thead style="background: #004466; color: white;">
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Target</th>
                            <th>IP Address</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $audit)
                            <tr>
                                <td>{{ $audit->user->name ?? 'System' }}</td>
                                <td>{{ $audit->role ?? '-' }}</td>
                                <td>{{ $audit->action }}</td>
                                <td>{{ $audit->target ?? '-' }}</td>
                                <td>{{ $audit->ip_address ?? '-' }}</td>
                                <td>{{ $audit->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;">No audit records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>


        </div> {{-- main-content ends --}}
    </div> {{-- dashboard-container ends --}}

    {{-- JS for toggling sections and forms --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const links = document.querySelectorAll(".sidebar a[data-target]");
            const sections = document.querySelectorAll(".content-section");

            // Tab click event
            links.forEach(link => {
                link.addEventListener("click", function (e) {
                    e.preventDefault();
                    links.forEach(l => l.classList.remove("active"));
                    this.classList.add("active");
                    sections.forEach(s => s.classList.remove("active"));
                    const target = this.getAttribute("data-target");
                    document.getElementById(target).classList.add("active");

                    // Store active tab in URL
                    const url = new URL(window.location);
                    url.searchParams.set("tab", target);
                    window.history.pushState({}, "", url);
                });
            });

            // Toggle Add User Form
            const addUserBtn = document.getElementById('toggleAddUserBtn');
            const addUserForm = document.getElementById('addUserForm');
            if (addUserBtn) {
                addUserBtn.addEventListener('click', () => {
                    addUserForm.style.display = addUserForm.style.display === 'none' ? 'block' : 'none';
                });
            }

            // Toggle Add Department Form
            const addDeptBtn = document.getElementById('toggleAddDeptBtn');
            const addDeptForm = document.getElementById('addDeptForm');
            if (addDeptBtn) {
                addDeptBtn.addEventListener('click', () => {
                    addDeptForm.style.display = addDeptForm.style.display === 'none' ? 'block' : 'none';
                });
            }

            // Restore Active Tab from URL or Controller
            const urlParams = new URLSearchParams(window.location.search);
            let activeTab = urlParams.get('tab');
            if (!activeTab) activeTab = "{{ $activeTab ?? 'profile' }}";
            links.forEach(l => l.classList.remove("active"));
            sections.forEach(s => s.classList.remove("active"));
            const link = document.querySelector(`.sidebar a[data-target='${activeTab}']`);
            const section = document.getElementById(activeTab);
            if (link) link.classList.add("active");
            if (section) section.classList.add("active");
        });

        // Show Edit User Form
        function showEditUserForm(id, name, email, departmentId) {
            const form = document.getElementById('editUserForm');
            form.style.display = 'block';
            form.action = '/admin/users/update/' + id;
            document.getElementById('editUserId').value = id;
            document.getElementById('editUsername').value = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editDepartmentId').value = departmentId;
            window.scrollTo({ top: form.offsetTop, behavior: 'smooth' });
        }

        // Show Edit Department Form
        function showEditDeptForm(id, name) {
            const form = document.getElementById('editDeptForm');
            form.style.display = 'block';
            form.action = '/admin/departments/update/' + id; // POST route
            document.getElementById('editDeptId').value = id;
            document.getElementById('editDeptName').value = name;
            window.scrollTo({ top: form.offsetTop, behavior: 'smooth' });
        }

        function hideEditUserForm() { document.getElementById('editUserForm').style.display = 'none'; }
        function hideEditDeptForm() { document.getElementById('editDeptForm').style.display = 'none'; }
    </script>
@endsection