{{-- resources/views/user/dashboard.blade.php --}}
@extends('layouts.user-dashboard') 

@section('title', 'User Dashboard')

@section('user-content')
<div class="dashboard-container" style="display: flex; height: 100vh;">

    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="sidebar-top">
            <h2>User Panel</h2>
            {{-- Dashboard always visible --}}
            <a href="#" class="tab-link active" data-target="welcome"><i class="fas fa-home"></i> Dashboard</a>

            {{-- Profile always visible --}}
            <a href="#" class="tab-link" data-target="profile"><i class="fas fa-user"></i> Profile</a>

            {{-- Attendance only for users with permission --}}
            @can('attendance.view')
                <a href="#" class="tab-link" data-target="attendance"><i class="fas fa-calendar-check"></i> Attendance</a>
            @endcan

            {{-- Fingerprint Scan only for users with permission --}}
            @can('attendance.create')
                <a href="#" class="tab-link" data-target="scan"><i class="fas fa-fingerprint"></i> Fingerprint Scan</a>
            @endcan
        </div>
        <a href="{{ route('logout') }}" class="logout-btn"> <i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    {{-- Main content --}}
    <div class="main-content">

        {{-- Welcome --}}
        <div id="welcome" class="section active">
            <div class="welcome-header-box">
                <i class="fas fa-user-circle fa-2x"></i>
               <span>Welcome --- {{ auth()->user()->username ?? auth()->user()->name }}</span>
            </div>
            <div class="welcome-details-box">
                <p style="text-align: center;">We manage your attendance!</p>
            </div>
        </div>

        {{-- Profile --}}
        <div id="profile" class="section">
            <div class="profile-header-box">
                <h3>Profile Information</h3>
            </div>
            <div class="profile-details-box">
                <p><strong>User ID:</strong> {{ auth()->user()->id }}</p>
                <p><strong>Username:</strong> {{ auth()->user()->username ?? auth()->user()->name }}</p>
                <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
               
            </div>
        </div>

        {{-- Attendance --}}
        @can('attendance.view')
        <div id="attendance" class="section">
            <h3>Attendance History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="attendance-body">
                    @forelse(auth()->user()->attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->date }}</td>
                            <td>{{ $attendance->time_in ?? '-' }}</td>
                            <td>{{ $attendance->time_out ?? '-' }}</td>
                            <td>{{ $attendance->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;">No attendance records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endcan

        {{-- Fingerprint Scan --}}
        @can('attendance.create')
        <div id="scan" class="section">
            <div class="scan-container">
                <h3><i class="fas fa-fingerprint"></i> Fingerprint Scan</h3>
                <div class="scan-wrapper">
                    <p>Please place your finger on the scanner to mark your attendance.</p>
                    <button class="scan-btn" id="simulate-scan-btn">Simulate Scan</button>
                    <p id="scan-feedback" style="margin-top: 15px; color: green;"></p>
                </div>
            </div>
        </div>
        @endcan

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // ==========================
    // Sidebar tab switching
    // ==========================
    const links = document.querySelectorAll(".tab-link");
    const sections = document.querySelectorAll(".section");

    links.forEach(link => {
        link.addEventListener("click", function(e) {
            e.preventDefault();
            links.forEach(l => l.classList.remove("active"));
            sections.forEach(s => s.classList.remove("active"));
            link.classList.add("active");
            const target = document.getElementById(link.dataset.target);
            if(target) target.classList.add("active");
        });
    });

    // ==========================
    // Load attendance on page load
    // ==========================
    function loadAttendance() {
        fetch('{{ route("user.attendance") }}')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('attendance-body');
                tbody.innerHTML = '';

                if(data.length === 0){
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="4" style="text-align:center;">No attendance records yet.</td>';
                    tbody.appendChild(tr);
                    return;
                }

                data.forEach(record => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${record.date}</td>
                        <td>${record.time_in ?? '-'}</td>
                        <td>${record.time_out ?? '-'}</td>
                        <td>${record.status}</td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(err => console.error('Error fetching attendance:', err));
    }

    // Load attendance initially
    loadAttendance();

    // ==========================
    // Simulate Scan Button
    // ==========================
    const simulateBtn = document.getElementById('simulate-scan-btn');
    const feedback = document.getElementById('scan-feedback');

    if(simulateBtn){
        simulateBtn.addEventListener('click', function() {
            fetch('{{ route("user.simulateAttendance") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    feedback.style.color = 'green';
                    feedback.textContent = data.message + 
                        ` (Time In: ${data.attendance.time_in ?? '-'}, Time Out: ${data.attendance.time_out ?? '-'}, Status: ${data.attendance.status})`;

                    // Refresh table
                    loadAttendance();
                } else {
                    feedback.style.color = 'red';
                    feedback.textContent = data.message;
                }
            })
            .catch(err => {
                feedback.style.color = 'red';
                feedback.textContent = 'Error marking attendance.';
                console.error(err);
            });
        });
    }
});
</script>
@endsection


