<?php
/**
 * StudentHub - Stored CSV & JSON Submissions Inspector
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$page_title = "Form Submissions & File Records - StudentHub";
$active_page = "view_csv";

$csvContactFile = __DIR__ . '/data/contacts.csv';
$csvRegFile = __DIR__ . '/data/registrations.csv';
$jsonRegFile = __DIR__ . '/data/registrations.json';

// Helper to read CSV rows safely
function readCsvSafe(string $filepath): array {
    $rows = [];
    if (file_exists($filepath) && ($handle = fopen($filepath, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $rows[] = $data;
        }
        fclose($handle);
    }
    return $rows;
}

$contactRows = readCsvSafe($csvContactFile);
$regRows = readCsvSafe($csvRegFile);

// Read JSON registrations
$jsonRegs = [];
if (file_exists($jsonRegFile)) {
    $raw = file_get_contents($jsonRegFile);
    $jsonRegs = json_decode($raw, true) ?: [];
}

include __DIR__ . '/includes/header.php';
?>

<h1>Stored Records & Submissions</h1>
<p>Review contact inquiries and student registrations saved directly to CSV and JSON files.</p>

<p>
    <a href="admin_dashboard.php">&larr; Return to Admin Dashboard</a> | 
    <a href="contact.php">Submit Contact Inquiry</a> | 
    <a href="register.php">Submit Student Registration</a>
</p>

<!-- Tab Switcher -->
<div style="margin: 15px 0;">
    <button type="button" class="btn" id="btn-contacts" onclick="switchCsvView('contacts')">Contact Inquiries CSV (<?= count($contactRows) > 1 ? count($contactRows) - 1 : 0 ?>)</button>
    <button type="button" class="btn btn-secondary" id="btn-registrations" onclick="switchCsvView('registrations')">Registrations CSV (<?= count($regRows) > 1 ? count($regRows) - 1 : 0 ?>)</button>
    <button type="button" class="btn btn-secondary" id="btn-json" onclick="switchCsvView('json')">Registrations JSON (<?= count($jsonRegs) ?>)</button>
</div>

<!-- SECTION 1: CONTACTS CSV -->
<div id="view-contacts" style="display: block;">
    <h2>Contact Inquiries (<code>data/contacts.csv</code>)</h2>
    <?php if (!empty($contactRows) && count($contactRows) > 1): ?>
        <table>
            <thead>
                <tr>
                    <?php foreach ($contactRows[0] as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i < count($contactRows); $i++): ?>
                    <tr>
                        <?php foreach ($contactRows[$i] as $val): ?>
                            <td><?= htmlspecialchars($val) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No contact inquiry records found in <code>data/contacts.csv</code>.</p>
    <?php endif; ?>
</div>

<!-- SECTION 2: REGISTRATIONS CSV -->
<div id="view-registrations" style="display: none;">
    <h2>Student Registrations (<code>data/registrations.csv</code>)</h2>
    <?php if (!empty($regRows) && count($regRows) > 1): ?>
        <table>
            <thead>
                <tr>
                    <?php foreach ($regRows[0] as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i < count($regRows); $i++): ?>
                    <tr>
                        <?php foreach ($regRows[$i] as $val): ?>
                            <td><?= htmlspecialchars($val) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No registration records found in <code>data/registrations.csv</code>.</p>
    <?php endif; ?>
</div>

<!-- SECTION 3: REGISTRATIONS JSON -->
<div id="view-json" style="display: none;">
    <h2>Student Registrations (<code>data/registrations.json</code>)</h2>
    <?php if (!empty($jsonRegs)): ?>
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Name</th>
                    <th>Enrollment No</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Gender</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jsonRegs as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['timestamp'] ?? 'N/A') ?></td>
                        <td><strong><?= htmlspecialchars($item['name'] ?? 'N/A') ?></strong></td>
                        <td><code><?= htmlspecialchars($item['enrollment'] ?? 'N/A') ?></code></td>
                        <td><?= htmlspecialchars($item['course'] ?? 'N/A') ?></td>
                        <td>Year <?= (int)($item['year'] ?? 1) ?></td>
                        <td><?= htmlspecialchars($item['email'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['mobile'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['gender'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No registration records found in <code>data/registrations.json</code>.</p>
    <?php endif; ?>
</div>

<script>
function switchCsvView(name) {
    document.getElementById('view-contacts').style.display = name === 'contacts' ? 'block' : 'none';
    document.getElementById('view-registrations').style.display = name === 'registrations' ? 'block' : 'none';
    document.getElementById('view-json').style.display = name === 'json' ? 'block' : 'none';

    document.getElementById('btn-contacts').className = name === 'contacts' ? 'btn' : 'btn btn-secondary';
    document.getElementById('btn-registrations').className = name === 'registrations' ? 'btn' : 'btn btn-secondary';
    document.getElementById('btn-json').className = name === 'json' ? 'btn' : 'btn btn-secondary';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
