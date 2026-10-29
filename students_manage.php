<?php
/**
 * StudentHub - Student Management CRUD
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$page_title = "Student Directory Management";
$active_page = "students_manage";

$msg = "";
$msg_type = "info";

// 1. CSV EXPORT
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=charusat_students_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Full Name', 'Enrollment No', 'Email', 'Mobile', 'Course', 'Year', 'Gender', 'Status', 'Created At']);
    
    $res = $conn->query("SELECT id, name, enrollment_no, email, mobile, course, year, gender, status, created_at FROM students ORDER BY id ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit;
}

// 2. DELETE STUDENT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delId = (int)($_POST['student_id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $delId);
        if ($stmt->execute()) {
            $msg = "Student record #{$delId} deleted successfully.";
            $msg_type = "success";
            logAudit($conn, "DELETE_STUDENT", "Admin deleted student ID: {$delId}");
        } else {
            $msg = "Delete failed: " . $stmt->error;
            $msg_type = "danger";
        }
        $stmt->close();
    }
}

// 3. CREATE OR UPDATE STUDENT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['create', 'update'], true)) {
    $isUpdate = ($_POST['action'] === 'update');
    $stId = (int)($_POST['student_id'] ?? 0);

    $name = trim(strip_tags($_POST['name'] ?? ''));
    $enrollment_no = strtoupper(trim(strip_tags($_POST['enrollment_no'] ?? '')));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $mobile = trim(strip_tags($_POST['mobile'] ?? ''));
    $course = trim(strip_tags($_POST['course'] ?? ''));
    $year = (int)($_POST['year'] ?? 1);
    $gender = trim(strip_tags($_POST['gender'] ?? 'Male'));
    $status = trim(strip_tags($_POST['status'] ?? 'Active'));

    if (empty($name) || empty($enrollment_no) || empty($email) || empty($mobile) || empty($course)) {
        $msg = "Please fill in all required fields.";
        $msg_type = "danger";
    } else {
        if ($isUpdate && $stId > 0) {
            $u_stmt = $conn->prepare("
                UPDATE students 
                SET name = ?, enrollment_no = ?, email = ?, mobile = ?, course = ?, year = ?, gender = ?, status = ?
                WHERE id = ?
            ");
            if ($u_stmt) {
                $u_stmt->bind_param("sssssissi", $name, $enrollment_no, $email, $mobile, $course, $year, $gender, $status, $stId);
                if ($u_stmt->execute()) {
                    $msg = "Student record #{$stId} updated successfully.";
                    $msg_type = "success";
                    logAudit($conn, "UPDATE_STUDENT", "Admin updated student ID: {$stId} ({$enrollment_no})");
                } else {
                    $msg = "Update failed: " . $u_stmt->error;
                    $msg_type = "danger";
                }
                $u_stmt->close();
            }
        } else {
            // Create New Student Record
            $ins_user = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')");
            $defaultPass = password_hash('student123', PASSWORD_DEFAULT);
            $newUserId = null;
            if ($ins_user) {
                $ins_user->bind_param("sss", $enrollment_no, $email, $defaultPass);
                if ($ins_user->execute()) {
                    $newUserId = $ins_user->insert_id;
                }
                $ins_user->close();
            }

            $c_stmt = $conn->prepare("
                INSERT INTO students (user_id, name, enrollment_no, email, mobile, course, year, gender, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($c_stmt) {
                $c_stmt->bind_param("isssssiss", $newUserId, $name, $enrollment_no, $email, $mobile, $course, $year, $gender, $status);
                if ($c_stmt->execute()) {
                    $msg = "New student record added successfully.";
                    $msg_type = "success";
                    logAudit($conn, "CREATE_STUDENT", "Admin created new student: {$name} ({$enrollment_no})");
                } else {
                    $msg = "Insert failed: " . $c_stmt->error;
                    $msg_type = "danger";
                }
                $c_stmt->close();
            }
        }
    }
}

// 4. FETCH DATA WITH SEARCH & COURSE FILTER
$search = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course'] ?? 'all');

$sql = "SELECT * FROM students WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR enrollment_no LIKE ? OR email LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $types .= "sss";
}

if ($courseFilter !== 'all' && $courseFilter !== '') {
    $sql .= " AND course = ?";
    $params[] = $courseFilter;
    $types .= "s";
}

$sql .= " ORDER BY id ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check if editing specific student
$editStudent = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $e_stmt = $conn->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
    if ($e_stmt) {
        $e_stmt->bind_param("i", $editId);
        $e_stmt->execute();
        $editStudent = $e_stmt->get_result()->fetch_assoc();
        $e_stmt->close();
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1>Student Directory Management</h1>
<p>Create, view, update, delete, search, filter, and export students database.</p>

<p>
    <a href="students_manage.php?export=csv">Export to CSV</a> | 
    <a href="admin_dashboard.php">&larr; Return to Admin Dashboard</a>
</p>

<?php if (!empty($msg)): ?>
    <div class="alert alert-<?= $msg_type ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Add or Edit Student Form -->
<h2><?= $editStudent ? 'Edit Student Record #' . (int)$editStudent['id'] : 'Add New Student Record' ?></h2>

<form method="POST" action="students_manage.php">
    <input type="hidden" name="action" value="<?= $editStudent ? 'update' : 'create' ?>">
    <input type="hidden" name="student_id" value="<?= $editStudent ? (int)$editStudent['id'] : 0 ?>">

    <div class="form-group">
        <label for="st_name">Full Name: *</label>
        <input type="text" id="st_name" name="name" value="<?= htmlspecialchars($editStudent['name'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="st_enroll">Enrollment Number: *</label>
        <input type="text" id="st_enroll" name="enrollment_no" value="<?= htmlspecialchars($editStudent['enrollment_no'] ?? '') ?>" placeholder="e.g. 23DCS089" required>
    </div>

    <div class="form-group">
        <label for="st_email">Email Address: *</label>
        <input type="email" id="st_email" name="email" value="<?= htmlspecialchars($editStudent['email'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="st_mobile">Mobile Number: *</label>
        <input type="tel" id="st_mobile" name="mobile" value="<?= htmlspecialchars($editStudent['mobile'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="st_course">Course / Branch: *</label>
        <select id="st_course" name="course" required>
            <option value="B.Tech AI-ML" <?= ($editStudent['course'] ?? '') === 'B.Tech AI-ML' ? 'selected' : '' ?>>B.Tech AI-ML</option>
            <option value="B.Tech CSE" <?= ($editStudent['course'] ?? '') === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
            <option value="B.Tech IT" <?= ($editStudent['course'] ?? '') === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
            <option value="BCA" <?= ($editStudent['course'] ?? '') === 'BCA' ? 'selected' : '' ?>>BCA</option>
            <option value="MCA" <?= ($editStudent['course'] ?? '') === 'MCA' ? 'selected' : '' ?>>MCA</option>
        </select>
    </div>

    <div class="form-group">
        <label for="st_year">Year: *</label>
        <select id="st_year" name="year" required>
            <option value="1" <?= ($editStudent['year'] ?? 1) == 1 ? 'selected' : '' ?>>Year 1</option>
            <option value="2" <?= ($editStudent['year'] ?? 1) == 2 ? 'selected' : '' ?>>Year 2</option>
            <option value="3" <?= ($editStudent['year'] ?? 1) == 3 ? 'selected' : '' ?>>Year 3</option>
            <option value="4" <?= ($editStudent['year'] ?? 1) == 4 ? 'selected' : '' ?>>Year 4</option>
        </select>
    </div>

    <div class="form-group">
        <label for="st_gender">Gender:</label>
        <select id="st_gender" name="gender">
            <option value="Male" <?= ($editStudent['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($editStudent['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= ($editStudent['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
    </div>

    <div class="form-group">
        <label for="st_status">Status:</label>
        <select id="st_status" name="status">
            <option value="Active" <?= ($editStudent['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Inactive" <?= ($editStudent['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>

    <div class="form-group">
        <input type="submit" value="<?= $editStudent ? 'Save Changes' : 'Add Student' ?>">
        <?php if ($editStudent): ?>
            <a href="students_manage.php">Cancel Edit</a>
        <?php endif; ?>
    </div>
</form>

<!-- Search and Filter Form -->
<h2>Search Student Directory</h2>
<form method="GET" action="students_manage.php">
    <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
        <div>
            <label for="search">Keyword:</label>
            <input type="text" id="search" name="search" placeholder="Search name, enrollment, email..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div>
            <label for="course">Department:</label>
            <select id="course" name="course">
                <option value="all">All Courses</option>
                <option value="B.Tech AI-ML" <?= $courseFilter === 'B.Tech AI-ML' ? 'selected' : '' ?>>B.Tech AI-ML</option>
                <option value="B.Tech CSE" <?= $courseFilter === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
                <option value="B.Tech IT" <?= $courseFilter === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
                <option value="BCA" <?= $courseFilter === 'BCA' ? 'selected' : '' ?>>BCA</option>
                <option value="MCA" <?= $courseFilter === 'MCA' ? 'selected' : '' ?>>MCA</option>
            </select>
        </div>
        <div>
            <input type="submit" value="Search / Filter">
            <?php if (!empty($search) || $courseFilter !== 'all'): ?>
                <a href="students_manage.php">Reset</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Students Table -->
<h2>Enrolled Students (<?= count($students) ?> Records)</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Enrollment No</th>
            <th>Course</th>
            <th>Year</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($students)): ?>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= (int)$s['id'] ?></td>
                    <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($s['enrollment_no']) ?></code></td>
                    <td><?= htmlspecialchars($s['course']) ?></td>
                    <td>Yr <?= (int)$s['year'] ?></td>
                    <td><a href="mailto:<?= htmlspecialchars($s['email']) ?>"><?= htmlspecialchars($s['email']) ?></a></td>
                    <td><?= htmlspecialchars($s['mobile']) ?></td>
                    <td><?= htmlspecialchars($s['status']) ?></td>
                    <td>
                        <a href="students_manage.php?edit_id=<?= (int)$s['id'] ?>">Edit</a> | 
                        <form method="POST" action="students_manage.php" onsubmit="return confirm('Delete student <?= htmlspecialchars($s['name']) ?>?');" style="display:inline; margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
                            <input type="submit" value="Delete" class="btn btn-danger btn-sm">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No student records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/includes/footer.php'; ?>
