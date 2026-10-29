<?php
/**
 * StudentHub - Simple Navigation Header
 * Web Development Frameworks (AIUE203)
 */

if (!isset($page_title)) {
    $page_title = "StudentHub";
}
if (!isset($active_page)) {
    $active_page = "";
}

$currentUser = function_exists('currentUser') ? currentUser() : null;
$isAdmin = function_exists('isAdmin') ? isAdmin() : false;
$isLoggedIn = function_exists('isLoggedIn') ? isLoggedIn() : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <nav class="site-nav" aria-label="Main Navigation">
            <div class="nav-links">
                <a href="index.php" class="nav-brand">StudentHub</a>
                <a href="index.php" class="<?= $active_page === 'home' ? 'active' : '' ?>">Home</a>
                <a href="about.php" class="<?= $active_page === 'about' ? 'active' : '' ?>">About</a>
                <a href="events.php" class="<?= $active_page === 'events' ? 'active' : '' ?>">Events</a>
                <a href="faq.php" class="<?= $active_page === 'faq' ? 'active' : '' ?>">FAQ</a>
                <a href="json_data.php" class="<?= $active_page === 'json_data' ? 'active' : '' ?>">Directory</a>
                <a href="contact.php" class="<?= $active_page === 'contact' ? 'active' : '' ?>">Contact</a>
                <a href="feedback.php" class="<?= $active_page === 'feedback' ? 'active' : '' ?>">Feedback</a>
                <?php if ($isAdmin): ?>
                    <a href="admin_dashboard.php" class="<?= $active_page === 'admin' ? 'active' : '' ?>">Admin</a>
                    <a href="students_manage.php" class="<?= $active_page === 'students_manage' ? 'active' : '' ?>">Students</a>
                    <a href="events_manage.php" class="<?= $active_page === 'events_manage' ? 'active' : '' ?>">Manage Events</a>
                    <a href="ajax_crud.php" class="<?= $active_page === 'ajax_crud' ? 'active' : '' ?>">Quick Events</a>
                    <a href="audit_logs.php">Audit Logs</a>
                <?php endif; ?>
            </div>
            <div class="nav-auth">
                <?php if ($isLoggedIn): ?>
                    <span class="user-greeting">Welcome, <?= htmlspecialchars($currentUser['username']) ?></span>
                    <a href="dashboard.php" class="<?= $active_page === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                    <a href="profile.php" class="<?= $active_page === 'profile' ? 'active' : '' ?>">Profile</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="<?= $active_page === 'login' ? 'active' : '' ?>">Login</a>
                    <a href="register.php" class="<?= $active_page === 'register' ? 'active' : '' ?>">Register</a>
                <?php endif; ?>
                <button type="button" id="themeToggleBtn" class="theme-toggle-btn theme-toggle-trigger">Dark Mode</button>
            </div>
        </nav>
    </header>

    <main id="main-content" class="page-container">
