{{-- resources/views/admin_dashboard.blade.php --}}
@extends('layouts.admin-dashboard')

@section('title', 'Admin Dashboard')

@section('admin-content')
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>

            <a href="#" class="tab-link active" data-target="profile">
                <i class="fas fa-user"></i> Profile
            </a>

            <!-- Manage Dropdown Start -->
            <input type="checkbox" id="manage-toggle" />
            <label for="manage-toggle"><i class="fas fa-cogs"></i> Manage ▼</label>
            <div class="dropdown-content">
                <a href="#" class="tab-link" data-target="users"><i class="fas fa-users"></i> Users</a>
                <a href="#" class="tab-link" data-target="departments"><i class="fas fa-building"></i> Departments</a>
            </div>
            <!-- Manage Dropdown End -->

            <a href="#" class="tab-link" data-target="reports"><i class="fas fa-calendar-check"></i> Attendance Reports</a>
            <a href="{{ route('logout') }}" class="logout"> <i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="main-content">
            {{-- Profile Section --}}
            <div id="profile" class="content-section active">
                <h2 style="margin-top: 20px; color: #333;">Welcome, {{ auth()->user()->name }}!</h2>
                <div style="font-size: 40px; color: #555; margin-bottom: 20px;">
                    <i class="fas fa-user-circle"></i>
                </div>
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
                    <h2><i class="fas fa-users"></i> Manage Users</h2>

                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex gap-2 mb-4">
                        <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}"
                            class="input-field" />
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Clear</a>
                    </form>

                    <!-- Add User Button -->
                    <button id="toggleAddUserBtn" class="btn btn-success">+ Add User</button>

                    <!-- Add User Form (hidden initially) -->
                    <form id="addUserForm" method="POST" action="/admin/users" style="display: none; margin-bottom: 20px;">
                        @csrf
                        <input type="text" name="username" placeholder="Username" required
                            style="padding:8px; margin-right:5px;">
                        <input type="email" name="email" placeholder="Email" required
                            style="padding:8px; margin-right:5px;">
                        <input type="password" name="password" placeholder="Password" required
                            style="padding:8px; margin-right:5px;">
                        <select name="department_id" required style="padding:8px; margin-right:5px;">
                            <option value="" disabled selected>Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </form>


                    {{-- Users Table --}}
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->department->name ?? '-' }}</td>
                                    <td>
                                        <!-- Edit/Delete buttons -->
                                        <a href="/admin/users/{{ $user->id }}/edit" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="/admin/users/{{ $user->id }}" method="POST" style="display:inline;">
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


                    {{-- Departments Section --}}
                    <div id="departments" class="content-section">
                        <div class="card">
                            <h2><i class="fas fa-building"></i> Manage Departments</h2>

                            <!-- Add Department Button -->
                            <button id="toggleAddDeptBtn" class="btn btn-success">+ Add Department</button>

                            <!-- Add Department Form (hidden initially) -->
                            <form id="addDeptForm" method="POST" action="/admin/departments"
                                style="display: none; margin-bottom: 20px;">
                                @csrf
                                <input type="text" name="name" placeholder="Department Name" required
                                    style="padding:8px; margin-right:5px;">
                                <button type="submit" class="btn btn-primary">Add Department</button>
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
                                                <!-- Edit Button -->
                                                <button class="btn btn-warning btn-sm"
                                                    onclick="showEditDeptForm({{ $dept->id }}, '{{ $dept->name }}')">
                                                    Edit
                                                </button>

                                                <!-- Delete Form -->
                                                <form action="/admin/departments/{{ $dept->id }}" method="POST"
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

                    {{-- Reports Section --}}
                    <div id="reports" class="content-section">
                        <div class="card">
                            <h2><i class="fas fa-calendar-check"></i> Attendance Reports</h2>

                            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex gap-2 flex-wrap mb-4">
                                <select name="filter_user" class="input-field">
                                    <option value="">All Users</option>
                                    @foreach($usersForFilter as $user)
                                        <option value="{{ $user->username }}" {{ request('filter_user') == $user->username ? 'selected' : '' }}>{{ $user->username }}</option>
                                    @endforeach
                                </select>

                                <select name="filter_department" class="input-field">
                                    <option value="">All Departments</option>
                                    @foreach($departmentsForFilter as $dept)
                                        <option value="{{ $dept->name }}" {{ request('filter_department') == $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>

                                <input type="date" name="start_date" value="{{ request('start_date') }}" />
                                <input type="date" name="end_date" value="{{ request('end_date') }}" />

                                <select name="filter_status" class="input-field">
                                    <option value="all" {{ request('filter_status') == 'all' ? 'selected' : '' }}>All Status
                                    </option>
                                    <option value="Present" {{ request('filter_status') == 'Present' ? 'selected' : '' }}>
                                        Present</option>
                                    <option value="Absent" {{ request('filter_status') == 'Absent' ? 'selected' : '' }}>Absent
                                    </option>
                                    <option value="Late" {{ request('filter_status') == 'Late' ? 'selected' : '' }}>Late
                                    </option>
                                    <option value="On Leave" {{ request('filter_status') == 'On Leave' ? 'selected' : '' }}>On
                                        Leave</option>
                                </select>

                                <div class="flex gap-2">
                                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Clear Filters</a>
                                </div>
                            </form>

                            {{-- Attendance Table --}}
                            <table class="table-auto">
                                <thead class="bg-blue-700 text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
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
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->department }}</td>
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
                </div>
            </div>

            {{-- Add any JS for toggling forms --}}
            <script>
            
document.addEventListener("DOMContentLoaded", function () {
    const links = document.querySelectorAll(".sidebar a[data-section]");
    const sections = document.querySelectorAll(".content-section");

    links.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            // Hide all sections
            sections.forEach(section => section.classList.remove("active"));

            // Get the clicked section
            const target = this.getAttribute("data-section");
            document.getElementById(target).classList.add("active");
        });
    });
});
        </script>

@endsection