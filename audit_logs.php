<?php
/**
 * StudentHub - Security Audit Logs
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$page_title = "Security Audit Logs - StudentHub";
$active_page = "admin";

// Filters
$actionFilter = trim($_GET['action'] ?? 'all');
$search = trim($_GET['search'] ?? '');

$sql = "SELECT a.*, u.username, u.email 
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE 1=1";
$params = [];
$types = "";

if ($actionFilter !== 'all' && $actionFilter !== '') {
    $sql .= " AND a.action = ?";
    $params[] = $actionFilter;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (a.description LIKE ? OR a.action LIKE ? OR u.username LIKE ?)";
    $s_term = "%$search%";
    $params[] = $s_term;
    $params[] = $s_term;
    $params[] = $s_term;
    $types .= "sss";
}

$sql .= " ORDER BY a.created_at DESC, a.id DESC LIMIT 50";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique actions
$act_res = $conn->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC");
$actions = [];
if ($act_res) {
    while ($r = $act_res->fetch_assoc()) $actions[] = $r['action'];
}

include __DIR__ . '/includes/header.php';
?>

<h1>Security Audit Logs</h1>
<p>Record of authentication events, database modifications, and security actions.</p>

<p><a href="admin_dashboard.php">&larr; Return to Admin Dashboard</a></p>

<!-- Filter Form -->
<form method="GET" action="audit_logs.php">
    <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 15px;">
        <div>
            <label for="search">Search Keywords:</label>
            <input type="text" id="search" name="search" placeholder="Search description, action, username..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div>
            <label for="action">Action Type:</label>
            <select id="action" name="action">
                <option value="all">All Actions</option>
                <?php foreach ($actions as $act): ?>
                    <option value="<?= htmlspecialchars($act) ?>" <?= $actionFilter === $act ? 'selected' : '' ?>>
                        <?= htmlspecialchars($act) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <input type="submit" value="Filter Logs">
            <?php if (!empty($search) || $actionFilter !== 'all'): ?>
                <a href="audit_logs.php">Reset</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Logs Table -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Action</th>
            <th>User</th>
            <th>Description</th>
            <th>IP Address</th>
            <th>Timestamp</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($logs)): ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= (int)$log['id'] ?></td>
                    <td><code><?= htmlspecialchars($log['action']) ?></code></td>
                    <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                    <td><?= htmlspecialchars($log['description']) ?></td>
                    <td><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?></td>
                    <td><?= date('d M Y, H:i:s', strtotime($log['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No audit log records found matching your query.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/includes/footer.php'; ?>
