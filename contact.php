<?php
/**
 * StudentHub - Contact Us & Server-Side Form Processing
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';

$page_title = "Contact Us - StudentHub";
$active_page = "contact";

$errors = [];
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Sanitize Inputs (Practical 7)
    $name = trim(strip_tags($_POST["name"] ?? ""));
    $email = trim(filter_var($_POST["email"] ?? "", FILTER_SANITIZE_EMAIL));
    $subject = trim(strip_tags($_POST["subject"] ?? ""));
    $message = trim(strip_tags($_POST["message"] ?? ""));

    // 2. Server-Side Validation (Practical 7)
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Please provide a valid full name (minimum 2 characters).";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if (empty($subject) || strlen($subject) < 3) {
        $errors[] = "Subject must be at least 3 characters long.";
    }

    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long.";
    }

    // 3. File & DB Storage (Practical 7)
    if (empty($errors)) {
        // A. CSV File Storage
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        $csvFile = $dataDir . '/contacts.csv';
        $isNewFile = !file_exists($csvFile);

        $fp = fopen($csvFile, 'a');
        if ($fp) {
            if ($isNewFile) {
                fputcsv($fp, ['Timestamp', 'Full Name', 'Email Address', 'Subject', 'Message', 'IP Address']);
            }
            fputcsv($fp, [
                date('Y-m-d H:i:s'),
                $name,
                $email,
                $subject,
                $message,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
            fclose($fp);
        }

        // B. Database Storage
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $name, $email, $subject, $message);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $e) {}

        logAudit($conn, "CONTACT_SUBMIT", "Contact submission received from: $email");

        $success_msg = "Thank you, " . htmlspecialchars($name) . "! Your message has been saved in server storage and submitted to university administration.";
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1>Contact Us</h1>
<p>Have an inquiry regarding admissions, events, or placement? Submit the form below.</p>

<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success">
        <strong>Submission Received:</strong> <?= $success_msg ?>
    </div>
    <p><a href="contact.php">Send another message</a></p>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="contact.php">
        <div class="form-group">
            <label for="contact_name">Full Name: *</label>
            <input type="text" id="contact_name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="contact_email">Email Address: *</label>
            <input type="email" id="contact_email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="contact_subject">Subject / Department: *</label>
            <select id="contact_subject" name="subject" required>
                <option value="">-- Select Subject Area --</option>
                <option value="Placement & Training Query" <?= ($_POST['subject'] ?? '') === 'Placement & Training Query' ? 'selected' : '' ?>>Placement & Training Query</option>
                <option value="Event Registration Assistance" <?= ($_POST['subject'] ?? '') === 'Event Registration Assistance' ? 'selected' : '' ?>>Event Registration Assistance</option>
                <option value="Campus Club Inquiry" <?= ($_POST['subject'] ?? '') === 'Campus Club Inquiry' ? 'selected' : '' ?>>Campus Club Inquiry</option>
                <option value="Academic / Examination Question" <?= ($_POST['subject'] ?? '') === 'Academic / Examination Question' ? 'selected' : '' ?>>Academic / Examination Question</option>
                <option value="General Support" <?= ($_POST['subject'] ?? '') === 'General Support' ? 'selected' : '' ?>>General Support</option>
            </select>
        </div>

        <div class="form-group">
            <label for="contact_message">Message: *</label>
            <textarea id="contact_message" name="message" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <input type="submit" value="Submit Message">
            <input type="reset" value="Clear Form">
        </div>
    </form>
<?php endif; ?>

<h2>University Helpdesk Contact</h2>
<p>
    <strong>Campus Location:</strong><br>
    Faculty of Technology and Engineering (FTE / CSPIT)<br>
    Charotar University of Science and Technology (CHARUSAT)<br>
    Changa - 388421, Dist. Anand, Gujarat, India.
</p>
<p>
    <strong>Office Hours:</strong> Monday - Saturday: 9:00 AM to 5:00 PM<br>
    <strong>Phone:</strong> +91 2697 265011 / 265021<br>
    <strong>Email:</strong> studenthub@charusat.edu.in
</p>

<?php include __DIR__ . '/includes/footer.php'; ?>
