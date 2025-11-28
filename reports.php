
<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Reports';
$conn = getDBConnection();

$student_id = $_GET['student_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

// Get all students for dropdown
$students = $conn->query("SELECT id, student_id, first_name, last_name FROM students ORDER BY last_name, first_name");

// Get all courses for dropdown
$courses = $conn->query("SELECT id, code, name FROM courses ORDER BY code");

include 'includes/header.php';
?>

<div class="form-container">
    <h2><i class="fas fa-filter"></i> Filter Reports</h2>
    <form method="GET" action="">
        <div class="form-row">
            <div class="form-group">
                <label>Student</label>
                <select name="student_id" onchange="this.form.submit()">
                    <option value="">All Students</option>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <option value="<?php echo $student['id']; ?>" <?php echo ($student_id == $student['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" onchange="this.form.submit()">
                    <option value="">All Courses</option>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo ($course_id == $course['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </form>
</div>

<?php if ($student_id): ?>
    <?php
    // Get student details
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Get attendance records
    $query = "
        SELECT s.*, c.name as course_name, c.code as course_code, 
               a.presence, a.participation 
        FROM sessions s 
        JOIN courses c ON s.course_id = c.id 
        LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
    ";
    
    if ($course_id) {
        $query .= " WHERE c.id = ?";
    }
    
    $query .= " ORDER BY s.session_date DESC, s.session_time DESC";
    
    $stmt = $conn->prepare($query);
    if ($course_id) {
        $stmt->bind_param("ii", $student_id, $course_id);
    } else {
        $stmt->bind_param("i", $student_id);
    }
    $stmt->execute();
    $attendance_records = $stmt->get_result();
    $stmt->close();
    
    // Calculate statistics
    $total_sessions = 0;
    $present_count = 0;
    $participated_count = 0;
    
    $temp_records = [];
    while ($record = $attendance_records->fetch_assoc()) {
        $total_sessions++;
        if ($record['presence']) $present_count++;
        if ($record['participation']) $participated_count++;
        $temp_records[] = $record;
    }
    
    $attendance_rate = $total_sessions > 0 ? round(($present_count / $total_sessions) * 100, 2) : 0;
    $participation_rate = $total_sessions > 0 ? round(($participated_count / $total_sessions) * 100, 2) : 0;
    ?>
    
    <div class="cards-grid">
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-user"></i>
            </div>
            <h3>Student</h3>
            <div class="card-value" style="font-size: 20px;">
                <?php echo htmlspecialchars($student_info['first_name'] . ' ' . $student_info['last_name']); ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-calendar"></i>
            </div>
            <h3>Total Sessions</h3>
            <div class="card-value"><?php echo $total_sessions; ?></div>
        </div>
        
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Attendance Rate</h3>
            <div class="card-value"><?php echo $attendance_rate; ?>%</div>
        </div>
        
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-star"></i>
            </div>
            <h3>Participation Rate</h3>
            <div class="card-value"><?php echo $participation_rate; ?>%</div>
        </div>
    </div>
    
    <div class="table-container">
        <div class="table-header">
            <h2><i class="fas fa-list"></i> Attendance Details</h2>
            <button onclick="window.print()" class="btn-sm btn-primary btn-print">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Course</th>
                    <th>Subject</th>
                    <th>Time</th>
                    <th>Present</th>
                    <th>Participated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($temp_records as $record): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($record['session_date'])); ?></td>
                    <td><strong><?php echo htmlspecialchars($record['course_code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($record['subject']); ?></td>
                    <td><?php echo date('h:i A', strtotime($record['session_time'])); ?></td>
                    <td>
                        <?php if ($record['presence']): ?>
                            <span class="badge badge-success">✓ Present</span>
                        <?php else: ?>
                            <span class="badge badge-danger">✗ Absent</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($record['participation']): ?>
                            <span class="badge badge-success">✓ Yes</span>
                        <?php else: ?>
                            <span class="badge badge-warning">✗ No</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="table-container">
        <div style="text-align: center; padding: 50px; color: #ff8fab;">
            <i class="fas fa-chart-bar" style="font-size: 64px; margin-bottom: 20px;"></i>
            <h2>Select a student to view their report</h2>
            <p>Use the filter above to choose a student and optionally filter by course</p>
        </div>
    </div>
<?php endif; ?>

<?php
$conn->close();
include 'includes/footer.php';
?>