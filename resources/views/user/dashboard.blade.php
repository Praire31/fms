{{-- resources/views/user/dashboard.blade.php --}}
@extends('layouts.user-dashboard') 

@section('title', 'User Dashboard')

@section('user-content')
<div class="dashboard-container" style="display: flex; height: 100vh;">

    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="sidebar-top">
            <h2>User Panel</h2>
            <a href="#" class="tab-link active" data-target="welcome"><i class="fas fa-home"></i> Dashboard</a>
            <a href="#" class="tab-link" data-target="profile"><i class="fas fa-user"></i> Profile</a>
            <a href="#" class="tab-link" data-target="attendance"><i class="fas fa-calendar-check"></i> Attendance</a>
            <a href="#" class="tab-link" data-target="scan"><i class="fas fa-fingerprint"></i> Fingerprint Scan</a>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>

    {{-- Main content --}}
    <div class="main-content">   
        {{-- Welcome --}}
        <div id="welcome" class="section active">
            <div class="welcome-header-box">
                <i class="fas fa-user-circle fa-2x"></i>
                <span>Welcome --- {{ auth()->user()->username }}</span>
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
                <p><strong>Username:</strong> {{ auth()->user()->username }}</p>
                <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            </div>
        </div>

        {{-- Attendance --}}
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
                    <!-- Dynamically loaded -->
                </tbody>
            </table>
            <p id="no-attendance-msg" style="color: #666; margin-top: 10px;">
                No attendance records yet.
            </p>
        </div>

        {{-- Fingerprint Scan --}}
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
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const links = document.querySelectorAll('.tab-link');
    const sections = document.querySelectorAll('.section');

    links.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            links.forEach(l => l.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));

            link.classList.add('active');
            const target = document.getElementById(link.dataset.target);
            if (target) target.classList.add('active');
        });
    });

    // Load attendance records
    const attendanceBody = document.getElementById('attendance-body');
    const noAttendanceMsg = document.getElementById('no-attendance-msg');

    fetch('{{ route("user.attendance") }}')
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                noAttendanceMsg.style.display = 'block';
            } else {
                noAttendanceMsg.style.display = 'none';
                data.forEach(record => {
                    const row = attendanceBody.insertRow();
                    row.innerHTML = `
                        <td>${record.date}</td>
                        <td>${record.time_in}</td>
                        <td>${record.time_out ?? '-'}</td>
                        <td>${record.status}</td>
                    `;
                });
            }
        })
        .catch(console.error);

    // Simulate fingerprint scan
    const scanBtn = document.getElementById('simulate-scan-btn');
    if (scanBtn) {
        scanBtn.addEventListener('click', function() {
            const feedback = document.getElementById('scan-feedback');

            const fingerprintId = "{{ auth()->user()->fingerprint_id ?? 'test_fingerprint' }}";
            const data = new URLSearchParams();
            data.append('finger_id', fingerprintId);

            fetch('{{ route("user.markAttendance") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: data.toString(),
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    feedback.style.color = 'red';
                    feedback.textContent = data.error;
                    return;
                }
                feedback.style.color = 'green';
                feedback.textContent = data.message;

                const today = new Date().toISOString().slice(0, 10);
                let existingRow = null;
                Array.from(attendanceBody.rows).forEach(row => {
                    if (row.cells[0].textContent === today) existingRow = row;
                });

                if (existingRow) {
                    existingRow.cells[1].textContent = data.time_in || '-';
                    existingRow.cells[2].textContent = data.time_out || '-';
                    existingRow.cells[3].textContent = data.status || '-';
                } else {
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td>${today}</td>
                        <td>${data.time_in || '-'}</td>
                        <td>${data.time_out || '-'}</td>
                        <td>${data.status || '-'}</td>
                    `;
                    attendanceBody.appendChild(newRow);
                }
            })
            .catch(err => {
                feedback.style.color = 'red';
                feedback.textContent = '❌ Error communicating with server.';
                console.error(err);
            });

            setTimeout(() => feedback.innerHTML = '', 5000);
        });
    }
});
</script>
@endsection
