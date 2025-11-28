<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Dashboard';
$conn = getDBConnection();

// Get statistics
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$totalCourses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
$totalSessions = $conn->query("SELECT COUNT(*) as count FROM sessions")->fetch_assoc()['count'];
$totalAttendance = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE presence = 1")->fetch_assoc()['count'];

// Get recent sessions
$recentSessions = $conn->query("
    SELECT s.*, c.name as course_name, c.code as course_code 
    FROM sessions s 
    JOIN courses c ON s.course_id = c.id 
    ORDER BY s.session_date DESC, s.session_time DESC 
    LIMIT 5
");

include 'includes/header.php';
?>

<div class="cards-grid">
    <div class="card">
        <div class="card-icon">
            <i class="fas fa-users"></i>
        </div>
        <h3>Total Students</h3>
        <div class="card-value"><?php echo $totalStudents; ?></div>
    </div>
    
    <div class="card">
        <div class="card-icon">
            <i class="fas fa-book"></i>
        </div>
        <h3>Total Courses</h3>
        <div class="card-value"><?php echo $totalCourses; ?></div>
    </div>
    
    <div class="card">
        <div class="card-icon">
            <i class="fas fa-calendar"></i>
        </div>
        <h3>Total Sessions</h3>
        <div class="card-value"><?php echo $totalSessions; ?></div>
    </div>
    
    <div class="card">
        <div class="card-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <h3>Total Attendance</h3>
        <div class="card-value"><?php echo $totalAttendance; ?></div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-clock"></i> Recent Sessions</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Course</th>
                <th>Date</th>
                <th>Time</th>
                <th>Subject</th>
                <th>Actions</th>
            </tr>