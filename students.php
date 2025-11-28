<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Students Management';
$conn = getDBConnection();
$message = '';

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $group_id = $_POST['group_id'];
    
    $stmt = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, email, group_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $student_id, $first_name, $last_name, $email, $group_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Student added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding student!</div>';
    }
    $stmt->close();
}

// Handle Delete Student
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Student deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting student!</div>';
    }
    $stmt->close();
}

// Get all students
$students = $conn->query("
    SELECT s.*, g.group_name, c.name as course_name 
    FROM students s 
    LEFT JOIN groups g ON s.group_id = g.id 
    LEFT JOIN courses c ON g.course_id = c.id 
    ORDER BY s.last_name, s.first_name
");

// Get all groups for dropdown
$groups = $conn->query("
    SELECT g.*, c.name as course_name 
    FROM groups g 
    JOIN courses c ON g.course_id = c.id 
    ORDER BY c.name, g.group_name
");

include 'includes/header.php';
?>

<?php echo $message; ?>

<div class="form-container">
    <h2><i class="fas fa-plus-circle"></i> Add New Student</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <div class="form-row">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" placeholder="e.g., STU001" required>
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" placeholder="First Name" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" placeholder="Last Name" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="student@example.com" required>
            </div>
            <div class="form-group">
                <label>Group</label>
                <select name="group_id" required>
                    <option value="">Select Group</option>
                    <?php while ($group = $groups->fetch_assoc()): ?>
                        <option value="<?php echo $group['id']; ?>">
                            <?php echo htmlspecialchars($group['course_name'] . ' - ' . $group['group_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn">
            <i class="fas fa-save"></i> Add Student
        </button>
    </form>
</div>

<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-users"></i> All Students</h2>
        <input type="text" id="tableSearch" placeholder="Search students..." style="padding: 10px; border: 2px solid #ffc0cb; border-radius: 8px;">
    </div>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Group</th>
                <th>Course</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($student = $students->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
                <td><?php echo htmlspecialchars($student['group_name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($student['course_name'] ?? 'N/A'); ?></td>
                <td>
                    <a href="reports.php?student_id=<?php echo $student['id']; ?>" class="btn-sm btn-primary">
                        <i class="fas fa-chart-line"></i> Report
                    </a>
                    <a href="?delete=<?php echo $student['id']; ?>" class="btn-sm btn-danger btn-delete">
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