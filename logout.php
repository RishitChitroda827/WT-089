<?php
/**
 * StudentHub - Secure Logout Handler (Practical 10)
 * Destroys sessions, clears cookies & tokens, logs audit trail
 */

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    $uid = (int)$_SESSION['user_id'];
    logAudit($conn, "LOGOUT", "User logged out: {$_SESSION['username']}", $uid);

    // Clear remember token in database
    $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->close();
    }
}

// Clear remember_token cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: login.php?logout=1");
exit;
