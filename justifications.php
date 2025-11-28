<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Absence Justifications';
$conn = getDBConnection();
$message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $student_id = $_POST['student_id'];
    $session_id = $_POST['session_id'];
    
    if (isset($_FILES['justification_file']) && $_FILES['justification_file']['error'] === 0) {
        $upload_dir = 'uploads/justifications/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['justification_file']['name'], PATHINFO_EXTENSION);
        $file_name = 'justification_' . $student_id . '_' . $session_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['justification_file']['tmp_name'], $file_path)) {
            $stmt = $conn->prepare("INSERT INTO justifications (student_id, session_id, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $student_id, $session_id, $file_path);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Justification uploaded successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error saving justification!</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="alert alert-danger">Error uploading file!</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Please select a file!</div>';
    }
}

// Handle delete justification
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get file path before deleting
    $stmt = $conn->prepare("SELECT file_path FROM justifications WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (file_exists($row['file_path'])) {
            unlink($row['file_path']);
        }
    }
    $stmt->close();
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM justifications WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Justification deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting justification!</div>';
    }
    $stmt->close();
}

// Get all justifications
$justifications = $conn->query("
    SELECT j.*, 
           s.student_id, s.first_name, s.last_name,
           sess.session_date, sess.subject,
           c.code as course_code, c.name as course_name
    FROM justifications j
    JOIN students s ON j.student_id = s.id
    JOIN sessions sess ON j.session_id = sess.id
    JOIN courses c ON sess.course_id = c.id
    ORDER BY j.uploaded_at DESC
");

// Get students for dropdown
$students = $conn->query("SELECT id, student_id, first_name, last_name FROM students ORDER BY last_name, first_name");

// Get sessions for dropdown
$sessions = $conn->query("
    SELECT s.*, c.name as course_name, c.code as course_code 
    FROM sessions s 
    JOIN courses c ON s.course_id = c.id 
    ORDER BY s.session_date DESC
");

include 'includes/header.php';
?>

<?php echo $message; ?>

<?php if (hasRole('student')): ?>
<div class="form-container">
    <h2><i class="fas fa-upload"></i> Upload Absence Justification</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">
        <input type="hidden" name="student_id" value="<?php echo $_SESSION['user_id']; ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label>Session</label>
                <select name="session_id" required>
                    <option value="">Select Session</option>
                    <?php while ($session = $sessions->fetch_assoc()): ?>
                        <option value="<?php echo $session['id']; ?>">
                            <?php echo htmlspecialchars($session['course_code'] . ' - ' . date('M d, Y', strtotime($session['session_date'])) . ' - ' . $session['subject']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Justification File (PDF, Image, DOC)</label>
                <input type="file" name="justification_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
            </div>
        </div>
        
        <button type="submit" class="btn">
            <i class="fas fa-upload"></i> Upload Justification
        </button>
    </form>
</div>
<?php endif; ?>

<?php if (hasRole('admin') || hasRole('professor')): ?>
<div class="form-container">
    <h2><i class="fas fa-upload"></i> Upload Justification for Student</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">
        
        <div class="form-row">
            <div class="form-group">
                <label>Student</label>
                <select name="student_id" required>
                    <option value="">Select Student</option>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <option value="<?php echo $student['id']; ?>">
                            <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Session</label>
                <select name="session_id" required>
                    <option value="">Select Session</option>
                    <?php
                    $sessions2 = $conn->query("
                        SELECT s.*, c.name as course_name, c.code as course_code 
                        FROM sessions s 
                        JOIN courses c ON s.course_id = c.id 
                        ORDER BY s.session_date DESC
                    ");
                    while ($session = $sessions2->fetch_assoc()):
                    ?>
                        <option value="<?php echo $session['id']; ?>">
                            <?php echo htmlspecialchars($session['course_code'] . ' - ' . date('M d, Y', strtotime($session['session_date'])) . ' - ' . $session['subject']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Justification File</label>
                <input type="file" name="justification_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
            </div>
        </div>
        
        <button type="submit" class="btn">
            <i class="fas fa-upload"></i> Upload Justification
        </button>
    </form>
</div>
<?php endif; ?>

<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-file-alt"></i> All Justifications</h2>
        <input type="text" id="tableSearch" placeholder="Search justifications..." style="padding: 10px; border: 2px solid #ffc0cb; border-radius: 8px;">
    </div>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Course</th>
                <th>Session Date</th>
                <th>Subject</th>
                <th>Uploaded At</th>
                <th>File</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($just = $justifications->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($just['student_id']); ?></strong> - <?php echo htmlspecialchars($just['first_name'] . ' ' . $just['last_name']); ?></td>
                <td><?php echo htmlspecialchars($just['course_code']); ?></td>
                <td><?php echo date('M d, Y', strtotime($just['session_date'])); ?></td>
                <td><?php echo htmlspecialchars($just['subject']); ?></td>
                <td><?php echo date('M d, Y H:i', strtotime($just['uploaded_at'])); ?></td>
                <td>
                    <a href="<?php echo htmlspecialchars($just['file_path']); ?>" target="_blank" class="btn-sm btn-primary">
                        <i class="fas fa-download"></i> View
                    </a>
                </td>
                <td>
                    <a href="?delete=<?php echo $just['id']; ?>" class="btn-sm btn-danger btn-delete">
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
