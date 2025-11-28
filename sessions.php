<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Sessions Management';
$conn = getDBConnection();
$message = '';

// Handle Add Session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $course_id = $_POST['course_id'];
    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];
    $subject = $_POST['subject'];
    
    $stmt = $conn->prepare("INSERT INTO sessions (course_id, session_date, session_time, subject) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $course_id, $session_date, $session_time, $subject);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Session added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding session!</div>';
    }
    $stmt->close();
}

// Handle Delete Session
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM sessions WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Session deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting session!</div>';
    }
    $stmt->close();
}

// Get all sessions
$sessions = $conn->query("
    SELECT s.*, c.name as course_name, c.code as course_code 
    FROM sessions s 
    JOIN courses c ON s.course_id = c.id 
    ORDER BY s.session_date DESC, s.session_time DESC
");

// Get all courses for dropdown
$courses = $conn->query("SELECT id, code, name FROM courses ORDER BY code");

include 'includes/header.php';
?>

<?php echo $message; ?>

<div class="form-container">
    <h2><i class="fas fa-plus-circle"></i> Add New Session</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <div class="form-row">
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" required>
                    <option value="">Select Course</option>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $course['id']; ?>">
                            <?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="session_date" required>
            </div>
            <div class="form-group">
                <label>Time</label>
                <input type="time" name="session_time" required>
            </div>
        </div>
        <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject" placeholder="e.g., Introduction to Programming" required>
        </div>
        <button type="submit" class="btn">
            <i class="fas fa-save"></i> Add Session
        </button>
    </form>
</div>

<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-calendar"></i> All Sessions</h2>
        <input type="text" id="tableSearch" placeholder="Search sessions..." style="padding: 10px; border: 2px solid #ffc0cb; border-radius: 8px;">
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
        </thead>
        <tbody>
            <?php while ($session = $sessions->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($session['course_code']); ?></strong> - <?php echo htmlspecialchars($session['course_name']); ?></td>
                <td><?php echo date('M d, Y', strtotime($session['session_date'])); ?></td>
                <td><?php echo date('h:i A', strtotime($session['session_time'])); ?></td>
                <td><?php echo htmlspecialchars($session['subject']); ?></td>
                <td>
                    <a href="attendance.php?session_id=<?php echo $session['id']; ?>" class="btn-sm btn-primary">
                        <i class="fas fa-user-check"></i> Attendance
                    </a>
                    <a href="?delete=<?php echo $session['id']; ?>" class="btn-sm btn-danger btn-delete">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>