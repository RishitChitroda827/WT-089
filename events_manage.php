<?php
/**
 * StudentHub - Event Management CRUD with Poster Upload
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$page_title = "Event & Poster Management";
$active_page = "events_manage";

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    file_put_contents($uploadDir . '/index.html', '<!DOCTYPE html><html><body>Access Denied</body></html>');
}

$msg = "";
$msg_type = "info";

// 1. DELETE EVENT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delId = (int)($_POST['event_id'] ?? 0);

    // Fetch poster filename to safely delete from disk
    $f_stmt = $conn->prepare("SELECT poster FROM events WHERE id = ?");
    if ($f_stmt) {
        $f_stmt->bind_param("i", $delId);
        $f_stmt->execute();
        $posterName = $f_stmt->get_result()->fetch_assoc()['poster'] ?? null;
        $f_stmt->close();

        if ($posterName && file_exists($uploadDir . '/' . $posterName)) {
            @unlink($uploadDir . '/' . $posterName);
        }
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $delId);
        if ($stmt->execute()) {
            $msg = "Event #{$delId} and its poster were deleted successfully.";
            $msg_type = "success";
            logAudit($conn, "DELETE_EVENT", "Admin deleted event ID: {$delId}");
        } else {
            $msg = "Delete failed: " . $stmt->error;
            $msg_type = "danger";
        }
        $stmt->close();
    }
}

// 2. CREATE OR UPDATE EVENT (With File Upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['create', 'update'], true)) {
    $isUpdate = ($_POST['action'] === 'update');
    $eventId = (int)($_POST['event_id'] ?? 0);

    $title = trim(strip_tags($_POST['title'] ?? ''));
    $description = trim(strip_tags($_POST['description'] ?? ''));
    $category = trim(strip_tags($_POST['category'] ?? 'Technical'));
    $event_date = trim($_POST['event_date'] ?? date('Y-m-d'));
    $venue = trim(strip_tags($_POST['venue'] ?? ''));
    $capacity = (int)($_POST['capacity'] ?? 100);
    $status = trim(strip_tags($_POST['status'] ?? 'Open'));
    $posterFilename = $_POST['existing_poster'] ?? null;

    if (empty($title) || empty($description) || empty($venue) || empty($event_date)) {
        $msg = "Please fill in all required event details.";
        $msg_type = "danger";
    } else {
        // File Upload Processing & Validation
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['poster'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $msg = "File upload failed with error code: " . $file['error'];
                $msg_type = "danger";
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $msg = "Poster file size exceeds maximum limit of 2MB.";
                $msg_type = "danger";
            } else {
                // Strict MIME and Extension Validation
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);
                $allowedMimes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                if (!array_key_exists($mimeType, $allowedMimes)) {
                    $msg = "Invalid image format ($mimeType). Only JPG, PNG, and WebP are accepted.";
                    $msg_type = "danger";
                } elseif (@getimagesize($file['tmp_name']) === false) {
                    $msg = "Corrupted or non-image file detected.";
                    $msg_type = "danger";
                } else {
                    $ext = $allowedMimes[$mimeType];
                    $newFilename = 'poster_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $destPath = $uploadDir . '/' . $newFilename;

                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        // Delete old poster if updating
                        if ($isUpdate && $posterFilename && file_exists($uploadDir . '/' . $posterFilename)) {
                            @unlink($uploadDir . '/' . $posterFilename);
                        }
                        $posterFilename = $newFilename;
                    } else {
                        $msg = "Failed to move uploaded poster to server directory.";
                        $msg_type = "danger";
                    }
                }
            }
        }

        // Execute DB write if no upload errors
        if ($msg_type !== 'danger') {
            if ($isUpdate && $eventId > 0) {
                $stmt = $conn->prepare("
                    UPDATE events 
                    SET title = ?, description = ?, category = ?, event_date = ?, venue = ?, capacity = ?, status = ?, poster = ?
                    WHERE id = ?
                ");
                if ($stmt) {
                    $stmt->bind_param("sssssissi", $title, $description, $category, $event_date, $venue, $capacity, $status, $posterFilename, $eventId);
                    if ($stmt->execute()) {
                        $msg = "Event #{$eventId} updated successfully.";
                        $msg_type = "success";
                        logAudit($conn, "UPDATE_EVENT", "Admin updated event ID: {$eventId} ({$title})");
                    } else {
                        $msg = "Update failed: " . $stmt->error;
                        $msg_type = "danger";
                    }
                    $stmt->close();
                }
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO events (title, description, category, event_date, venue, capacity, status, poster)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if ($stmt) {
                    $stmt->bind_param("sssssiss", $title, $description, $category, $event_date, $venue, $capacity, $status, $posterFilename);
                    if ($stmt->execute()) {
                        $newId = $stmt->insert_id;
                        $msg = "New event created successfully with ID #{$newId}.";
                        $msg_type = "success";
                        logAudit($conn, "CREATE_EVENT", "Admin created event: {$title} (ID: {$newId})");
                    } else {
                        $msg = "Create failed: " . $stmt->error;
                        $msg_type = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// 3. FETCH EVENTS WITH FILTER
$catFilter = trim($_GET['cat'] ?? 'all');
$statusFilter = trim($_GET['status'] ?? 'all');

$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count
        FROM events e WHERE 1=1";
$params = [];
$types = "";

if ($catFilter !== 'all' && $catFilter !== '') {
    $sql .= " AND e.category = ?";
    $params[] = $catFilter;
    $types .= "s";
}

if ($statusFilter !== 'all' && $statusFilter !== '') {
    $sql .= " AND e.status = ?";
    $params[] = $statusFilter;
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

// Check if editing specific event
$editEvent = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $e_stmt = $conn->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
    if ($e_stmt) {
        $e_stmt->bind_param("i", $editId);
        $e_stmt->execute();
        $editEvent = $e_stmt->get_result()->fetch_assoc();
        $e_stmt->close();
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1>Campus Events & Poster Management</h1>
<p>Create events, validate and upload poster images, manage registrations, and update statuses.</p>

<p>
    <a href="ajax_crud.php">Quick Event Editor (AJAX)</a> | 
    <a href="admin_dashboard.php">&larr; Return to Admin Dashboard</a>
</p>

<?php if (!empty($msg)): ?>
    <div class="alert alert-<?= $msg_type ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Event Add / Edit Form -->
<h2><?= $editEvent ? 'Edit Event Details #' . (int)$editEvent['id'] : 'Create New Campus Event' ?></h2>

<form method="POST" action="events_manage.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?= $editEvent ? 'update' : 'create' ?>">
    <input type="hidden" name="event_id" value="<?= $editEvent ? (int)$editEvent['id'] : 0 ?>">
    <input type="hidden" name="existing_poster" value="<?= htmlspecialchars($editEvent['poster'] ?? '') ?>">

    <div class="form-group">
        <label for="ev_title">Event Title: *</label>
        <input type="text" id="ev_title" name="title" value="<?= htmlspecialchars($editEvent['title'] ?? '') ?>" placeholder="e.g. CHARUSAT Hackathon 2026" required>
    </div>

    <div class="form-group">
        <label for="ev_category">Category: *</label>
        <select id="ev_category" name="category" required>
            <option value="Technical" <?= ($editEvent['category'] ?? '') === 'Technical' ? 'selected' : '' ?>>Technical</option>
            <option value="Workshop" <?= ($editEvent['category'] ?? '') === 'Workshop' ? 'selected' : '' ?>>Workshop</option>
            <option value="Sports" <?= ($editEvent['category'] ?? '') === 'Sports' ? 'selected' : '' ?>>Sports</option>
            <option value="Cultural" <?= ($editEvent['category'] ?? '') === 'Cultural' ? 'selected' : '' ?>>Cultural</option>
            <option value="Seminar" <?= ($editEvent['category'] ?? '') === 'Seminar' ? 'selected' : '' ?>>Seminar</option>
        </select>
    </div>

    <div class="form-group">
        <label for="ev_date">Event Date: *</label>
        <input type="date" id="ev_date" name="event_date" value="<?= htmlspecialchars($editEvent['event_date'] ?? date('Y-m-d', strtotime('+7 days'))) ?>" required>
    </div>

    <div class="form-group">
        <label for="ev_venue">Campus Venue: *</label>
        <input type="text" id="ev_venue" name="venue" value="<?= htmlspecialchars($editEvent['venue'] ?? '') ?>" placeholder="e.g. CSPIT Auditorium" required>
    </div>

    <div class="form-group">
        <label for="ev_capacity">Maximum Capacity (Seats):</label>
        <input type="number" id="ev_capacity" name="capacity" value="<?= (int)($editEvent['capacity'] ?? 100) ?>" min="1" max="1000">
    </div>

    <div class="form-group">
        <label for="ev_status">Registration Status:</label>
        <select id="ev_status" name="status">
            <option value="Open" <?= ($editEvent['status'] ?? '') === 'Open' ? 'selected' : '' ?>>Open</option>
            <option value="Closed" <?= ($editEvent['status'] ?? '') === 'Closed' ? 'selected' : '' ?>>Closed</option>
            <option value="Completed" <?= ($editEvent['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
        </select>
    </div>

    <div class="form-group">
        <label for="ev_desc">Description: *</label>
        <textarea id="ev_desc" name="description" rows="4" required><?= htmlspecialchars($editEvent['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label for="ev_poster">Poster Image (JPG, PNG, WebP - Max 2MB):</label>
        <input type="file" id="ev_poster" name="poster" accept="image/jpeg,image/png,image/webp">
        <?php if (!empty($editEvent['poster']) && file_exists($uploadDir . '/' . $editEvent['poster'])): ?>
            <p>Current poster: <a href="uploads/<?= rawurlencode($editEvent['poster']) ?>" target="_blank"><?= htmlspecialchars($editEvent['poster']) ?></a></p>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <input type="submit" value="<?= $editEvent ? 'Save Modifications' : 'Create Event' ?>">
        <?php if ($editEvent): ?>
            <a href="events_manage.php">Cancel Edit</a>
        <?php endif; ?>
    </div>
</form>

<!-- Filter Form -->
<h2>Filter Events</h2>
<form method="GET" action="events_manage.php">
    <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
        <div>
            <label for="cat">Category:</label>
            <select id="cat" name="cat">
                <option value="all">All Categories</option>
                <option value="Technical" <?= $catFilter === 'Technical' ? 'selected' : '' ?>>Technical</option>
                <option value="Workshop" <?= $catFilter === 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                <option value="Sports" <?= $catFilter === 'Sports' ? 'selected' : '' ?>>Sports</option>
                <option value="Cultural" <?= $catFilter === 'Cultural' ? 'selected' : '' ?>>Cultural</option>
                <option value="Seminar" <?= $catFilter === 'Seminar' ? 'selected' : '' ?>>Seminar</option>
            </select>
        </div>
        <div>
            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="all">All Statuses</option>
                <option value="Open" <?= $statusFilter === 'Open' ? 'selected' : '' ?>>Open</option>
                <option value="Closed" <?= $statusFilter === 'Closed' ? 'selected' : '' ?>>Closed</option>
                <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>
        <div>
            <input type="submit" value="Filter">
            <?php if ($catFilter !== 'all' || $statusFilter !== 'all'): ?>
                <a href="events_manage.php">Reset</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Events Table -->
<h2>Campus Events Directory</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Poster</th>
            <th>Title</th>
            <th>Category</th>
            <th>Date</th>
            <th>Venue</th>
            <th>Capacity</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $ev): ?>
                <tr>
                    <td><?= (int)$ev['id'] ?></td>
                    <td>
                        <?php if (!empty($ev['poster']) && file_exists($uploadDir . '/' . $ev['poster'])): ?>
                            <a href="uploads/<?= rawurlencode($ev['poster']) ?>" target="_blank">View Poster</a>
                        <?php else: ?>
                            None
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($ev['title']) ?></strong>
                        <br>
                        <small><?= htmlspecialchars(substr($ev['description'], 0, 70)) ?>...</small>
                    </td>
                    <td><?= htmlspecialchars($ev['category']) ?></td>
                    <td><?= htmlspecialchars($ev['event_date']) ?></td>
                    <td><?= htmlspecialchars($ev['venue']) ?></td>
                    <td><?= (int)$ev['reg_count'] ?> / <?= (int)$ev['capacity'] ?></td>
                    <td><?= htmlspecialchars($ev['status']) ?></td>
                    <td>
                        <a href="events_manage.php?edit_id=<?= (int)$ev['id'] ?>">Edit</a> | 
                        <form method="POST" action="events_manage.php" onsubmit="return confirm('Delete event <?= htmlspecialchars($ev['title']) ?>?');" style="display:inline; margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                            <input type="submit" value="Delete" class="btn btn-danger btn-sm">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No events match your filter criteria.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/includes/footer.php'; ?>
