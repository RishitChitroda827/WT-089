<?php
/**
 * StudentHub - Student Profile
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$page_title = "Student Profile - StudentHub";
$active_page = "profile";

$userId = (int)$_SESSION['user_id'];

// Fetch student profile details
$st_stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ? LIMIT 1");
$st_stmt->bind_param("i", $userId);
$st_stmt->execute();
$student = $st_stmt->get_result()->fetch_assoc();
$st_stmt->close();

if (!$student) {
    $f_res = $conn->query("SELECT * FROM students WHERE id = 1 LIMIT 1");
    $student = $f_res ? $f_res->fetch_assoc() : [
        'name' => 'Rishit Patel',
        'enrollment_no' => '23DCS089',
        'course' => 'B.Tech AI-ML',
        'year' => 3,
        'email' => 'student@charusat.edu.in',
        'mobile' => '9876543210',
        'gender' => 'Male',
        'status' => 'Active',
        'created_at' => date('Y-m-d H:i:s')
    ];
}

include __DIR__ . '/includes/header.php';
?>

<h1>Student Academic Profile</h1>
<p>Official student registry record for Faculty of Technology and Engineering (CSPIT / FTE).</p>

<table>
    <tr>
        <th style="width: 30%;">Full Name</th>
        <td><strong><?= htmlspecialchars($student['name']) ?></strong></td>
    </tr>
    <tr>
        <th>Enrollment Number</th>
        <td><code><?= htmlspecialchars($student['enrollment_no']) ?></code></td>
    </tr>
    <tr>
        <th>Enrolled Course</th>
        <td><?= htmlspecialchars($student['course']) ?></td>
    </tr>
    <tr>
        <th>Study Year</th>
        <td>Year <?= (int)$student['year'] ?></td>
    </tr>
    <tr>
        <th>Gender</th>
        <td><?= htmlspecialchars($student['gender'] ?? 'Not specified') ?></td>
    </tr>
    <tr>
        <th>Email Address</th>
        <td><a href="mailto:<?= htmlspecialchars($student['email']) ?>"><?= htmlspecialchars($student['email']) ?></a></td>
    </tr>
    <tr>
        <th>Contact Number</th>
        <td><?= htmlspecialchars($student['mobile']) ?></td>
    </tr>
    <tr>
        <th>Account Status</th>
        <td><?= htmlspecialchars($student['status'] ?? 'Active') ?></td>
    </tr>
    <tr>
        <th>Registration Date</th>
        <td><?= htmlspecialchars($student['created_at'] ?? 'N/A') ?></td>
    </tr>
</table>

<p>
    <a href="dashboard.php">&larr; Return to Dashboard</a> | 
    <a href="events.php">Browse Campus Events</a>
</p>

<?php include __DIR__ . '/includes/footer.php'; ?>
