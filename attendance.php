<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Attendance Management';
$conn = getDBConnection();
$message = '';

$session_id = $_GET['session_id'] ?? null;

// Handle Attendance Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_attendance') {
    $session_id = $_POST['session_id'];
    $students = $_POST['students'] ?? [];
    
    foreach ($students as $student_id => $data) {
        $presence = isset($data['presence']) ? 1 : 0;
        $participation = isset($data['participation']) ? 1 : 0;
        
        $stmt = $conn->prepare("
            INSERT INTO attendance (student_id, session_id, presence, participation) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE presence = ?, participation = ?
        ");
        $stmt->bind_param("iiiiii", $student_id, $session_id, $presence, $participation, $presence, $participation);
        $stmt->execute();
        $stmt->close();
    }
    
    $message = '<div class="alert alert-success">Attendance updated successfully!</div>';
}

// Get session details
if ($session_id) {
    $stmt = $conn->prepare("
        SELECT s.*, c.name as course_name, c.code as course_code 
        FROM sessions s 
        JOIN courses c ON s.course_id = c.id 
        WHERE s.id = ?
    ");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Get students and their attendance
    $students_result = $conn->query("
        SELECT s.*, g.group_name, 
               a.presence, a.participation 
        FROM students s 
        LEFT JOIN groups g ON s.group_id = g.id 
        LEFT JOIN attendance a ON s.id = a.student_id AND a.session_id = $session_id 
        ORDER BY s.last_name, s.first_name
    ");
}

// Get all sessions for dropdown
$sessions = $conn->query("
    SELECT s.*, c.name as course_name, c.code as course_code 
    FROM sessions s 
    JOIN courses c ON s.course_id = c.id 
    ORDER BY s.session_date DESC, s.session_time DESC
");

include 'includes/header.php';
?>

<?php echo $message; ?>

<div class="form-container">
    <h2><i class="fas fa-calendar-check"></i> Select Session</h2>
    <form method="GET" action="">
        <div class="form-row">
            <div class="form-group">
                <label>Session</label>
                <select name="session_id" onchange="this.form.submit()" required>
                    <option value="">Select Session</option>
                    <?php while ($sess = $sessions->fetch_assoc()): ?>
                        <option value="<?php echo $sess['id']; ?>" <?php echo ($session_id == $sess['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sess['course_code'] . ' - ' . date('M d, Y', strtotime($sess['session_date'])) . ' - ' . $sess['subject']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </form>
</div>

<?php if ($session_id && isset($session)): ?>
<div class="table-container">
    <div class="table-header">
        <div>
            <h2><i class="fas fa-user-check"></i> Attendance for Session</h2>
            <p style="color: #ff8fab; margin-top: 5px;">
                <strong><?php echo htmlspecialchars($session['course_code']); ?></strong> - 
                <?php echo htmlspecialchars($session['subject']); ?> - 
                <?php echo date('M d, Y', strtotime($session['session_date'])); ?> at 
                <?php echo date('h:i A', strtotime($session['session_time'])); ?>
            </p>
        </div>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="update_attendance">
        <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
        
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Group</th>
                    <th>Email</th>
                    <th style="text-align: center;">Present</th>
                    <th style="text-align: center;">Participated</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['group_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td style="text-align: center;">
                        <input type="checkbox" class="attendance-checkbox" 
                               name="students[<?php echo $student['id']; ?>][presence]" 
                               <?php echo $student['presence'] ? 'checked' : ''; ?>>
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" class="attendance-checkbox" 
                               name="students[<?php echo $student['id']; ?>][participation]" 
                               <?php echo $student['participation'] ? 'checked' : ''; ?>>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Save Attendance
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php
$conn->close();
include 'includes/footer.php';
?>
