<?php
/**
 * StudentHub - Campus Events Directory & Registration
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';

$page_title = "Campus Events - StudentHub";
$active_page = "events";

$msg = "";
$msg_type = "info";

// Handle Student Event Registration (if logged in)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_event') {
    if (!isLoggedIn()) {
        header("Location: login.php?msg=" . urlencode("Please log in to register for events."));
        exit;
    }

    $event_id = (int)($_POST['event_id'] ?? 0);
    $user_id = (int)$_SESSION['user_id'];

    // Find student ID corresponding to logged-in user
    $s_stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ? LIMIT 1");
    $s_stmt->bind_param("i", $user_id);
    $s_stmt->execute();
    $s_res = $s_stmt->get_result();
    $student = $s_res->fetch_assoc();
    $s_stmt->close();

    $student_id = $student ? (int)$student['id'] : 1;

    // Insert into registrations table
    $stmt = $conn->prepare("INSERT INTO registrations (student_id, event_id, status) VALUES (?, ?, 'Registered') ON DUPLICATE KEY UPDATE status = 'Registered'");
    if ($stmt) {
        $stmt->bind_param("ii", $student_id, $event_id);
        if ($stmt->execute()) {
            logAudit($conn, "REGISTER_EVENT", "Student ID {$student_id} registered for Event ID {$event_id}");
            $msg = "Successfully registered for this event! You can view your registrations on your Student Dashboard.";
            $msg_type = "success";
        } else {
            $msg = "Registration failed: " . $stmt->error;
            $msg_type = "danger";
        }
        $stmt->close();
    }
}

// Fetch events with category filter
$category = trim($_GET['cat'] ?? 'all');
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS registered_count 
        FROM events e";
$params = [];
$types = "";

if ($category !== 'all' && $category !== '') {
    $sql .= " WHERE e.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY e.event_date ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch unique categories for filter dropdown
$cats_res = $conn->query("SELECT DISTINCT category FROM events ORDER BY category ASC");
$categories = [];
while ($row = $cats_res->fetch_assoc()) {
    $categories[] = $row['category'];
}

include __DIR__ . '/includes/header.php';
?>

<h1>Campus Events & Activities</h1>
<p>Browse upcoming seminars, workshops, hackathons, and sports events.</p>

<?php if (!empty($msg)): ?>
    <div class="alert alert-<?= $msg_type ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Category Filter Form -->
<form method="GET" action="events.php">
    <label for="cat"><strong>Filter by Category:</strong></label>
    <select name="cat" id="cat" onchange="this.form.submit()">
        <option value="all">All Categories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $category === $c ? 'selected' : '' ?>>
                <?= htmlspecialchars($c) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="Filter">
    <?php if ($category !== 'all'): ?>
        <a href="events.php">Reset Filter</a>
    <?php endif; ?>
</form>

<!-- Events Table -->
<?php if (!empty($events)): ?>
    <table>
        <thead>
            <tr>
                <th>Event Title</th>
                <th>Category</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Capacity</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $ev): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($ev['title']) ?></strong>
                        <br>
                        <small><?= htmlspecialchars($ev['description']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($ev['category']) ?></td>
                    <td><?= htmlspecialchars($ev['event_date']) ?></td>
                    <td><?= htmlspecialchars($ev['venue']) ?></td>
                    <td><?= (int)$ev['registered_count'] ?> / <?= (int)$ev['capacity'] ?></td>
                    <td><?= htmlspecialchars($ev['status']) ?></td>
                    <td>
                        <?php if ($ev['status'] === 'Open'): ?>
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" action="events.php" style="margin: 0;">
                                    <input type="hidden" name="action" value="register_event">
                                    <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                                    <input type="submit" value="Register">
                                </form>
                            <?php else: ?>
                                <a href="login.php?msg=<?= urlencode('Please login to register for this event.') ?>">Login to Register</a>
                            <?php endif; ?>
                        <?php else: ?>
                            Closed
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No events found in this category.</p>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
