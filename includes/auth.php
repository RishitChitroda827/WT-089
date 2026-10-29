<?php
/**
 * StudentHub - Authentication, Sessions & RBAC Helpers
 * Covers Practicals 10, 14
 */

if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookie settings
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';

// Session Timeout Check (Practical 10: 15 minutes of inactivity)
$SESSION_TIMEOUT_SECONDS = 900; // 15 minutes

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $SESSION_TIMEOUT_SECONDS)) {
        // Expired
        logAudit($conn, 'TIMEOUT', 'Session timed out due to inactivity');
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit;
    }
    $_SESSION['last_activity'] = time();
} elseif (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
    // Remember-Me Auto-Login (Practical 10 Advanced Extension)
    $token = $_COOKIE['remember_token'];
    $token_hash = hash('sha256', $token);
    
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE remember_token = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($u = $res->fetch_assoc()) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            $_SESSION['username'] = $u['username'];
            $_SESSION['email'] = $u['email'];
            $_SESSION['role'] = $u['role'];
            $_SESSION['last_activity'] = time();
            logAudit($conn, 'REMEMBER_LOGIN', 'User auto-logged in via Remember-Me token');
        }
        $stmt->close();
    }
}

/**
 * Check if user is currently logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user array
 */
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? 'student'
    ];
}

/**
 * Check if user is admin
 */
function isAdmin(): bool {
    return isLoggedIn() && (($_SESSION['role'] ?? '') === 'admin');
}

/**
 * Check if user is student
 */
function isStudent(): bool {
    return isLoggedIn() && (($_SESSION['role'] ?? '') === 'student');
}

/**
 * Restrict page access to logged in users only
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header("Location: login.php?msg=" . urlencode("Please log in to access this page."));
        exit;
    }
}

/**
 * Restrict page access to Admin role only
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        die("
        <!DOCTYPE html>
        <html><head><title>403 Forbidden</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head><body class='bg-light d-flex align-items-center justify-content-center' style='min-height:80vh;'>
        <div class='card shadow-sm p-4 text-center' style='max-width:500px;'>
            <h2 class='text-danger'>Access Denied (403)</h2>
            <p class='text-muted'>This page requires Administrator privileges. Your current role is: <strong>" . htmlspecialchars($_SESSION['role'] ?? 'guest') . "</strong>.</p>
            <div class='mt-3'>
                <a href='dashboard.php' class='btn btn-primary btn-sm'>Return to Dashboard</a>
                <a href='logout.php' class='btn btn-outline-secondary btn-sm'>Switch Account</a>
            </div>
        </div></body></html>");
    }
}

/**
 * Record action in audit_logs table (Practical 14)
 */
function logAudit(mysqli $conn, string $action, string $description, ?int $userId = null): void {
    if ($userId === null && isset($_SESSION['user_id'])) {
        $userId = (int)$_SESSION['user_id'];
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isss", $userId, $action, $description, $ip);
        $stmt->execute();
        $stmt->close();
    }
}
