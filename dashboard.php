<?php
/**
 * StudentHub - Student Dashboard
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

if (isAdmin()) {
    header("Location: admin_dashboard.php");
    exit;
}

$page_title = "Student Dashboard - StudentHub";
$active_page = "dashboard";

$userId = (int)$_SESSION['user_id'];

// Handle cancel registration
$msg = "";
$msg_type = "info";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_registration') {
    $regId = (int)($_POST['reg_id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM registrations WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $regId);
        if ($stmt->execute()) {
            $msg = "Event registration cancelled successfully.";
            $msg_type = "success";
            logAudit($conn, "CANCEL_REGISTRATION", "Student cancelled registration ID: {$regId}");
        }
        $stmt->close();
    }
}

// Fetch student profile details
$st_stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ? LIMIT 1");
$st_stmt->bind_param("i", $userId);
$st_stmt->execute();
$student = $st_stmt->get_result()->fetch_assoc();
$st_stmt->close();

if (!$student) {
    $f_res = $conn->query("SELECT * FROM students WHERE id = 1 LIMIT 1");
    $student = $f_res ? $f_res->fetch_assoc() : [
        'id' => 1,
        'name' => $_SESSION['username'],
        'enrollment_no' => '23DCS089',
        'course' => 'B.Tech AI-ML',
        'year' => 3,
        'email' => $_SESSION['email'] ?? 'student@charusat.edu.in',
        'mobile' => '9876543210',
        'gender' => 'Male'
    ];
}

$studentId = (int)$student['id'];

// Fetch registered events for this student
$r_stmt = $conn->prepare("
    SELECT r.id AS reg_id, r.status AS reg_status, r.created_at AS reg_date,
           e.id AS event_id, e.title, e.category, e.event_date, e.venue, e.description
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.student_id = ?
    ORDER BY e.event_date ASC
");
$r_stmt->bind_param("i", $studentId);
$r_stmt->execute();
$my_events = $r_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$r_stmt->close();

include __DIR__ . '/includes/header.php';
?>

<h1>Student Dashboard</h1>
<p>
    Welcome back, <strong><?= htmlspecialchars($student['name']) ?></strong> 
    (Enrollment Number: <code><?= htmlspecialchars($student['enrollment_no']) ?></code>)
</p>

<?php if (!empty($msg)): ?>
    <div class="alert alert-<?= $msg_type ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<h2>Student Information</h2>
<table>
    <tr>
        <th style="width: 25%;">Student Name</th>
        <td><?= htmlspecialchars($student['name']) ?></td>
    </tr>
    <tr>
        <th>Enrollment No</th>
        <td><?= htmlspecialchars($student['enrollment_no']) ?></td>
    </tr>
    <tr>
        <th>Course & Year</th>
        <td><?= htmlspecialchars($student['course']) ?> - Year <?= (int)$student['year'] ?></td>
    </tr>
    <tr>
        <th>Email</th>
        <td><?= htmlspecialchars($student['email']) ?></td>
    </tr>
    <tr>
        <th>Total Events Registered</th>
        <td><?= count($my_events) ?></td>
    </tr>
</table>

<p>
    <a href="profile.php">View Detailed Profile</a> | 
    <a href="events.php">Browse Campus Events</a>
</p>

<h2>Your Registered Events</h2>
<?php if (!empty($my_events)): ?>
    <table>
        <thead>
            <tr>
                <th>Event Title</th>
                <th>Category</th>
                <th>Event Date</th>
                <th>Venue</th>
                <th>Registration Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($my_events as $ev): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($ev['title']) ?></strong></td>
                    <td><?= htmlspecialchars($ev['category']) ?></td>
                    <td><?= htmlspecialchars($ev['event_date']) ?></td>
                    <td><?= htmlspecialchars($ev['venue']) ?></td>
                    <td><?= htmlspecialchars($ev['reg_status']) ?></td>
                    <td>
                        <form method="POST" action="dashboard.php" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel registration?');">
                            <input type="hidden" name="action" value="cancel_registration">
                            <input type="hidden" name="reg_id" value="<?= (int)$ev['reg_id'] ?>">
                            <input type="submit" value="Cancel" class="btn btn-danger btn-sm">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have not registered for any events yet. <a href="events.php">View upcoming events</a> to register.</p>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
