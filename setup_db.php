<?php
/**
 * StudentHub - One-Click Database Setup & Seeder
 * Runs database.sql automatically and initializes all tables and seed records.
 */

require_once __DIR__ . '/includes/db.php';

$sqlFile = __DIR__ . '/database.sql';

if (!file_exists($sqlFile)) {
    die("Error: database.sql not found at " . htmlspecialchars($sqlFile));
}

$sql = file_get_contents($sqlFile);

// Execute multi-query
$success = true;
$messages = [];

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    if ($conn->errno) {
        $success = false;
        $messages[] = "SQL Error: " . $conn->error;
    } else {
        $messages[] = "Database 'studenthub' created or verified.";
        $messages[] = "Tables created: users, students, events, registrations, audit_logs, contact_messages.";
        $messages[] = "Default admin user: admin / admin123";
        $messages[] = "Default student user: student / student123";
        $messages[] = "Default student profile: rishit (23DCS089) / student123";
        $messages[] = "Seeded 21 student records (fulfills Practical 11 requirement).";
        $messages[] = "Seeded 8 events, 8 registrations, and audit logs.";
    }
} else {
    $success = false;
    $messages[] = "Execution error: " . $conn->error;
}

// Support both CLI and browser rendering
if (php_sapi_name() === 'cli') {
    echo $success ? "=== DATABASE SETUP SUCCESSFUL ===\n" : "=== DATABASE SETUP FAILED ===\n";
    foreach ($messages as $m) echo " - $m\n";
    exit($success ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - StudentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; }
        .setup-card { max-width: 650px; margin: 60px auto; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="setup-card">
    <h3 class="mb-3 text-primary">StudentHub Database Setup</h3>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <h5 class="alert-heading">Database Setup Completed Successfully!</h5>
            <ul class="mb-0 mt-2">
                <?php foreach ($messages as $m): ?>
                    <li><?= htmlspecialchars($m) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card mb-3 bg-light">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Test Credentials</h6>
                <p class="mb-1"><strong>Admin:</strong> <code>admin</code> / <code>admin123</code></p>
                <p class="mb-1"><strong>Student:</strong> <code>student</code> / <code>student123</code></p>
                <p class="mb-0"><strong>Student (Roll 089):</strong> <code>rishit</code> / <code>student123</code></p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-primary">Go to StudentHub Portal</a>
            <a href="practicals.php" class="btn btn-outline-secondary">View Practicals Index (1-15)</a>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h5>Setup Failed</h5>
            <ul>
                <?php foreach ($messages as $m): ?>
                    <li><?= htmlspecialchars($m) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="setup_db.php" class="btn btn-primary">Retry Setup</a>
    <?php endif; ?>
</div>
</body>
</html>
