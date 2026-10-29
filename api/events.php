<?php
/**
 * StudentHub - RESTful Events JSON API Endpoint (Practical 13)
 * Supports GET (list/single), POST (create), PUT (update), DELETE (delete).
 * Implements HTTP response status codes and input validation.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Helper to send standardized JSON response
function sendJson(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Helper to read JSON request body
function getRequestBody(): array {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return $decoded;
    }
    return $_POST;
}

// Support method overriding (e.g. via _method field or query parameter)
$body = getRequestBody();
if (isset($body['_method'])) {
    $method = strtoupper($body['_method']);
} elseif (isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
}

try {
    // -------------------------------------------------------------
    // 1. GET: Read all events or single event (with debounced search)
    // -------------------------------------------------------------
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$event) {
                sendJson(['success' => false, 'error' => 'Event not found.'], 404);
            }
            sendJson(['success' => true, 'data' => $event]);
        }

        $search = trim($_GET['search'] ?? '');
        if (!empty($search)) {
            $stmt = $conn->prepare("
                SELECT e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count
                FROM events e
                WHERE e.title LIKE ? OR e.venue LIKE ? OR e.category LIKE ? OR e.description LIKE ?
                ORDER BY e.event_date ASC
            ");
            $term = "%$search%";
            $stmt->bind_param("ssss", $term, $term, $term, $term);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $res = $conn->query("
                SELECT e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count
                FROM events e
                ORDER BY e.event_date ASC
            ");
            $data = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }

        sendJson(['success' => true, 'count' => count($data), 'data' => $data]);
    }

    // -------------------------------------------------------------
    // 2. POST: Create a new event
    // -------------------------------------------------------------
    if ($method === 'POST') {
        $title = trim($body['title'] ?? '');
        $category = trim($body['category'] ?? '');
        $event_date = trim($body['event_date'] ?? '');
        $venue = trim($body['venue'] ?? '');
        $description = trim($body['description'] ?? '');
        $capacity = (int)($body['capacity'] ?? 100);
        $status = trim($body['status'] ?? 'Open');

        if (empty($title) || empty($category) || empty($event_date) || empty($venue)) {
            sendJson(['success' => false, 'error' => 'Please fill in title, category, event_date, and venue.'], 400);
        }

        $stmt = $conn->prepare("INSERT INTO events (title, category, event_date, venue, description, capacity, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssis", $title, $category, $event_date, $venue, $description, $capacity, $status);
            if ($stmt->execute()) {
                $newId = $stmt->insert_id;
                sendJson(['success' => true, 'message' => 'Event added successfully via AJAX.', 'id' => $newId], 201);
            } else {
                sendJson(['success' => false, 'error' => 'Insert error: ' . $stmt->error], 500);
            }
            $stmt->close();
        }
    }

    // -------------------------------------------------------------
    // 3. PUT: Update an existing event
    // -------------------------------------------------------------
    if ($method === 'PUT') {
        $id = (int)($body['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            sendJson(['success' => false, 'error' => 'Missing or invalid event ID for update.'], 400);
        }

        $title = trim($body['title'] ?? '');
        $category = trim($body['category'] ?? '');
        $event_date = trim($body['event_date'] ?? '');
        $venue = trim($body['venue'] ?? '');
        $description = trim($body['description'] ?? '');
        $capacity = (int)($body['capacity'] ?? 100);
        $status = trim($body['status'] ?? 'Open');

        if (empty($title) || empty($category) || empty($event_date) || empty($venue)) {
            sendJson(['success' => false, 'error' => 'Please fill in title, category, event_date, and venue.'], 400);
        }

        $stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, event_date = ?, venue = ?, description = ?, capacity = ?, status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sssssisi", $title, $category, $event_date, $venue, $description, $capacity, $status, $id);
            if ($stmt->execute()) {
                sendJson(['success' => true, 'message' => "Event #{$id} updated successfully via AJAX."]);
            } else {
                sendJson(['success' => false, 'error' => 'Update error: ' . $stmt->error], 500);
            }
            $stmt->close();
        }
    }

    // -------------------------------------------------------------
    // 4. DELETE: Remove an event
    // -------------------------------------------------------------
    if ($method === 'DELETE') {
        $id = (int)($body['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            sendJson(['success' => false, 'error' => 'Missing or invalid event ID for deletion.'], 400);
        }

        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                sendJson(['success' => true, 'message' => "Event #{$id} deleted successfully via AJAX."]);
            } else {
                sendJson(['success' => false, 'error' => 'Delete error: ' . $stmt->error], 500);
            }
            $stmt->close();
        }
    }

    // Fallthrough for unsupported methods
    sendJson(['success' => false, 'error' => "HTTP method {$method} not allowed."], 405);

} catch (Throwable $e) {
    sendJson(['success' => false, 'error' => 'Server exception: ' . $e->getMessage()], 500);
}
