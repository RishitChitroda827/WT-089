<?php
/**
 * StudentHub - Admin Dashboard
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$page_title = "Admin Dashboard - StudentHub";
$active_page = "admin";

logAudit($conn, "VIEW_ADMIN_DASHBOARD", "Admin accessed central administration dashboard");

// Dynamic Counts
function getTableCount(mysqli $conn, string $table): int {
    $allowed = ['users', 'students', 'events', 'registrations', 'audit_logs'];
    if (!in_array($table, $allowed, true)) return 0;
    $res = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    return $res ? (int)$res->fetch_assoc()['total'] : 0;
}

$countUsers = getTableCount($conn, 'users');
$countStudents = getTableCount($conn, 'students');
$countEvents = getTableCount($conn, 'events');
$countRegistrations = getTableCount($conn, 'registrations');

// Latest Event Registrations
$latest_regs = [];
$r_res = $conn->query("
    SELECT r.id, r.created_at, r.status,
           s.name AS student_name, s.enrollment_no, s.course,
           e.title AS event_title, e.category
    FROM registrations r
    JOIN students s ON r.student_id = s.id
    JOIN events e ON r.event_id = e.id
    ORDER BY r.created_at DESC, r.id DESC
    LIMIT 6
");
if ($r_res) {
    while ($row = $r_res->fetch_assoc()) $latest_regs[] = $row;
}

// Recent Audit Logs
$recent_logs = [];
$l_res = $conn->query("
    SELECT a.*, u.username
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC, a.id DESC
    LIMIT 6
");
if ($l_res) {
    while ($row = $l_res->fetch_assoc()) $recent_logs[] = $row;
}

include __DIR__ . '/includes/header.php';
?>

<h1>Admin Dashboard</h1>
<p>System metrics, student management, event registrations, and security audit logs.</p>

<h2>Administrative Operations</h2>
<ul>
    <li><a href="students_manage.php">Student Management: Add, view, edit, search, filter, and export students to CSV</a></li>
    <li><a href="events_manage.php">Event Management: Create events, upload event posters, and update status</a></li>
    <li><a href="ajax_crud.php">Quick Event Editor: Asynchronous Fetch API CRUD</a></li>
    <li><a href="audit_logs.php">Security Audit Logs: System action history and traceability trail</a></li>
    <li><a href="view_csv.php">Submissions & File Records: View contacts and registrations stored in CSV/JSON</a></li>
</ul>

<h2>System Overview</h2>
<table>
    <thead>
        <tr>
            <th>Metric</th>
            <th>Count</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Total Users</strong></td>
            <td><?= $countUsers ?></td>
            <td>Registered system accounts</td>
        </tr>
        <tr>
            <td><strong>Enrolled Students</strong></td>
            <td><?= $countStudents ?></td>
            <td>Enrolled student directory records</td>
        </tr>
        <tr>
            <td><strong>Campus Events</strong></td>
            <td><?= $countEvents ?></td>
            <td>Scheduled campus activities</td>
        </tr>
        <tr>
            <td><strong>Event Registrations</strong></td>
            <td><?= $countRegistrations ?></td>
            <td>Total student event registrations</td>
        </tr>
    </tbody>
</table>

<h2>Recent Event Registrations</h2>
<?php if (!empty($latest_regs)): ?>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Enrollment No</th>
                <th>Event Title</th>
                <th>Category</th>
                <th>Course</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($latest_regs as $r): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['student_name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($r['enrollment_no']) ?></code></td>
                    <td><?= htmlspecialchars($r['event_title']) ?></td>
                    <td><?= htmlspecialchars($r['category']) ?></td>
                    <td><?= htmlspecialchars($r['course']) ?></td>
                    <td><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No event registrations recorded yet.</p>
<?php endif; ?>

<h2>Recent Security Audit Trail</h2>
<?php if (!empty($recent_logs)): ?>
    <table>
        <thead>
            <tr>
                <th>Action</th>
                <th>User</th>
                <th>Description</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_logs as $log): ?>
                <tr>
                    <td><code><?= htmlspecialchars($log['action']) ?></code></td>
                    <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                    <td><?= htmlspecialchars($log['description']) ?></td>
                    <td><?= date('d M Y, H:i:s', strtotime($log['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="audit_logs.php">View all audit logs &rarr;</a></p>
<?php else: ?>
    <p>No audit logs recorded yet.</p>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
