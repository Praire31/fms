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
document.addEventListener("DOMContentLoaded", function() {
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
});
</script>



@endsection
