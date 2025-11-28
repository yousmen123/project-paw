<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Courses Management';
$conn = getDBConnection();
$message = '';

// Handle Add Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $code = $_POST['code'];
    $name = $_POST['name'];
    $professor_id = $_POST['professor_id'];
    
    $stmt = $conn->prepare("INSERT INTO courses (code, name, professor_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $code, $name, $professor_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Course added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding course!</div>';
    }
    $stmt->close();
}

// Handle Delete Course
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Course deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting course!</div>';
    }
    $stmt->close();
}

// Get all courses
$courses = $conn->query("
    SELECT c.*, u.username as professor_name 
    FROM courses c 
    LEFT JOIN users u ON c.professor_id = u.id 
    ORDER BY c.code
");

// Get all professors for dropdown
$professors = $conn->query("SELECT id, username FROM users WHERE role = 'professor'");

include 'includes/header.php';
?>

<?php echo $message; ?>

<div class="form-container">
    <h2><i class="fas fa-plus-circle"></i> Add New Course</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <div class="form-row">
            <div class="form-group">
                <label>Course Code</label>
                <input type="text" name="code" placeholder="e.g., CS101" required>
            </div>
            <div class="form-group">
                <label>Course Name</label>
                <input type="text" name="name" placeholder="e.g., Introduction to CS" required>
            </div>
            <div class="form-group">
                <label>Professor</label>
                <select name="professor_id" required>
                    <option value="">Select Professor</option>
                    <?php while ($prof = $professors->fetch_assoc()): ?>
                        <option value="<?php echo $prof['id']; ?>"><?php echo htmlspecialchars($prof['username']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn">
            <i class="fas fa-save"></i> Add Course
        </button>
    </form>
</div>

<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-book"></i> All Courses</h2>
        <input type="text" id="tableSearch" placeholder="Search courses..." style="padding: 10px; border: 2px solid #ffc0cb; border-radius: 8px;">
    </div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Professor</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($course = $courses->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($course['code']); ?></strong></td>
                <td><?php echo htmlspecialchars($course['name']); ?></td>
                <td><?php echo htmlspecialchars($course['professor_name'] ?? 'Not assigned'); ?></td>
                <td><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                <td>
                    <a href="sessions.php?course_id=<?php echo $course['id']; ?>" class="btn-sm btn-primary">
                        <i class="fas fa-calendar"></i> Sessions
                    </a>
                    <a href="?delete=<?php echo $course['id']; ?>" class="btn-sm btn-danger btn-delete">
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
