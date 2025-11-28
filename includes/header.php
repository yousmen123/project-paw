<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Attendance System'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <h2>Attendance</h2>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            
            <?php if (hasRole('admin') || hasRole('professor')): ?>
            <a href="courses.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="sessions.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'sessions.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar"></i> Sessions
            </a>
            <a href="attendance.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-check"></i> Attendance
            </a>
            <a href="students.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Students
            </a>
            <?php endif; ?>
            
            <a href="reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="justifications.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'justifications.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> Justifications
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <div>
                <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                <small><?php echo ucfirst($_SESSION['role']); ?></small>
            </div>
        </div>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
        </div>
    <?php endif; ?>