<?php
/**
 * StudentHub - Student Registration
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';

$page_title = "Student Registration - StudentHub";
$active_page = "register";

$errors = [];
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Server-Side Input Sanitization
    $name = trim(strip_tags($_POST["name"] ?? ""));
    $enrollment = strtoupper(trim(strip_tags($_POST["enrollment"] ?? "")));
    $email = trim(filter_var($_POST["email"] ?? "", FILTER_SANITIZE_EMAIL));
    $mobile = trim(strip_tags($_POST["mobile"] ?? ""));
    $course = trim(strip_tags($_POST["course"] ?? ""));
    $year = (int)($_POST["year"] ?? 0);
    $gender = trim(strip_tags($_POST["gender"] ?? ""));
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $terms = isset($_POST["terms"]);

    // 2. Server-Side Validation
    if (!preg_match("/^[A-Za-z ]{2,50}$/", $name)) {
        $errors[] = "Full Name must contain only letters and spaces (2 to 50 characters).";
    }

    if (empty($enrollment) || strlen($enrollment) < 4) {
        $errors[] = "Please provide a valid enrollment number (e.g. 23DCS089).";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
        $errors[] = "Mobile number must be a valid 10-digit phone number.";
    }

    if (empty($course)) {
        $errors[] = "Please select your course / branch.";
    }

    if ($year < 1 || $year > 4) {
        $errors[] = "Please select a valid study year (1 to 4).";
    }

    if (!in_array($gender, ['Male', 'Female', 'Other'], true)) {
        $errors[] = "Please select your gender.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Password and confirm password do not match.";
    }

    if (!$terms) {
        $errors[] = "You must accept the terms and conditions.";
    }

    // 3. Duplicate Email / Username Check
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("ss", $email, $enrollment);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $errors[] = "An account with this email address or enrollment number already exists. Please login instead.";
            }
            $stmt->close();
        }
    }

    // 4. File Storage & Database Insert
    if (empty($errors)) {
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

        // CSV File Storage
        $csvFile = $dataDir . '/registrations.csv';
        $isNewCsv = !file_exists($csvFile);
        $fp = fopen($csvFile, 'a');
        if ($fp) {
            if ($isNewCsv) {
                fputcsv($fp, ['Timestamp', 'Full Name', 'Enrollment No', 'Email', 'Mobile', 'Course', 'Year', 'Gender']);
            }
            fputcsv($fp, [date('Y-m-d H:i:s'), $name, $enrollment, $email, $mobile, $course, $year, $gender]);
            fclose($fp);
        }

        // JSON File Storage
        $jsonFile = $dataDir . '/registrations.json';
        $jsonArray = [];
        if (file_exists($jsonFile)) {
            $jsonArray = json_decode(file_get_contents($jsonFile), true) ?: [];
        }
        $jsonArray[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'name' => $name,
            'enrollment' => $enrollment,
            'email' => $email,
            'mobile' => $mobile,
            'course' => $course,
            'year' => $year,
            'gender' => $gender
        ];
        file_put_contents($jsonFile, json_encode($jsonArray, JSON_PRETTY_PRINT));

        // Database Insert with password_hash() and Prepared Statement
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'student';

        $u_stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        if ($u_stmt) {
            $u_stmt->bind_param("ssss", $enrollment, $email, $hashed_password, $role);
            if ($u_stmt->execute()) {
                $user_id = $u_stmt->insert_id;

                $st_stmt = $conn->prepare("INSERT INTO students (user_id, name, enrollment_no, email, mobile, course, year, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
                if ($st_stmt) {
                    $st_stmt->bind_param("isssssis", $user_id, $name, $enrollment, $email, $mobile, $course, $year, $gender);
                    $st_stmt->execute();
                    $st_stmt->close();
                }

                logAudit($conn, "USER_REGISTER", "New student registered: {$enrollment} ({$email})", $user_id);

                $success_msg = "Account created successfully! Your username is " . htmlspecialchars($enrollment) . ". You can now sign in.";
            } else {
                $errors[] = "Database error: " . $u_stmt->error;
            }
            $u_stmt->close();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1>Student Registration</h1>
<p>Create a student account to participate in campus events and workshops.</p>

<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success">
        <strong>Success:</strong> <?= $success_msg ?>
    </div>
    <p><a href="login.php">Proceed to Login &rarr;</a></p>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please resolve the following errors:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="registrationForm" method="POST" action="register.php" novalidate>
        <div class="form-group">
            <label for="reg_name">Full Name: *</label>
            <input type="text" id="reg_name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            <div id="nameError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <label for="reg_enrollment">Enrollment Number: *</label>
            <input type="text" id="reg_enrollment" name="enrollment" placeholder="e.g. 23DCS089" value="<?= htmlspecialchars($_POST['enrollment'] ?? '') ?>" required>
            <div id="enrollError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <label for="reg_email">University Email: *</label>
            <input type="email" id="reg_email" name="email" placeholder="student@charusat.edu.in" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <div id="emailError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <label for="reg_mobile">Mobile Number: *</label>
            <input type="tel" id="reg_mobile" name="mobile" placeholder="10-digit number" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" required>
            <div id="mobileError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <label for="reg_course">Course / Branch: *</label>
            <select id="reg_course" name="course" required>
                <option value="">-- Select Course --</option>
                <option value="B.Tech AI-ML" <?= ($_POST['course'] ?? '') === 'B.Tech AI-ML' ? 'selected' : '' ?>>B.Tech AI-ML (CSPIT)</option>
                <option value="B.Tech CSE" <?= ($_POST['course'] ?? '') === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE (CSPIT)</option>
                <option value="B.Tech IT" <?= ($_POST['course'] ?? '') === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT (CSPIT)</option>
                <option value="BCA" <?= ($_POST['course'] ?? '') === 'BCA' ? 'selected' : '' ?>>BCA (FTE)</option>
                <option value="MCA" <?= ($_POST['course'] ?? '') === 'MCA' ? 'selected' : '' ?>>MCA (FTE)</option>
            </select>
            <div id="courseError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <label for="reg_year">Year of Study: *</label>
            <select id="reg_year" name="year" required>
                <option value="">-- Select Year --</option>
                <option value="1" <?= ($_POST['year'] ?? '') === '1' ? 'selected' : '' ?>>1st Year</option>
                <option value="2" <?= ($_POST['year'] ?? '') === '2' ? 'selected' : '' ?>>2nd Year</option>
                <option value="3" <?= ($_POST['year'] ?? '') === '3' ? 'selected' : '' ?>>3rd Year</option>
                <option value="4" <?= ($_POST['year'] ?? '') === '4' ? 'selected' : '' ?>>4th Year</option>
            </select>
            <div id="yearError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <label>Gender: *</label>
            <div>
                <label style="display: inline-block; font-weight: normal; margin-right: 15px;">
                    <input type="radio" name="gender" value="Male" <?= ($_POST['gender'] ?? '') === 'Male' ? 'checked' : '' ?>> Male
                </label>
                <label style="display: inline-block; font-weight: normal; margin-right: 15px;">
                    <input type="radio" name="gender" value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'checked' : '' ?>> Female
                </label>
                <label style="display: inline-block; font-weight: normal;">
                    <input type="radio" name="gender" value="Other" <?= ($_POST['gender'] ?? '') === 'Other' ? 'checked' : '' ?>> Other
                </label>
            </div>
            <div id="genderError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <label for="reg_password">Password: *</label>
            <input type="password" id="reg_password" name="password" placeholder="At least 6 characters" required>
            <div class="strength-bar-container">
                <div class="strength-bar" id="strengthBar"></div>
            </div>
            <div class="strength-text" id="strengthText"></div>
            <div id="passwordError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <label for="reg_confirm_password">Confirm Password: *</label>
            <input type="password" id="reg_confirm_password" name="confirm_password" placeholder="Re-enter password" required>
            <div id="confirmPasswordError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <label style="font-weight: normal;">
                <input type="checkbox" id="reg_terms" name="terms" <?= isset($_POST['terms']) ? 'checked' : '' ?> required>
                I accept the campus regulations and terms of service.
            </label>
            <div id="termsError" style="color: #cc0000; font-size: 12px;"></div>
        </div>

        <div class="form-group">
            <input type="submit" value="Register Account">
            <input type="reset" value="Reset Form">
        </div>
    </form>
<?php endif; ?>

<p>
    Already have an account? <a href="login.php">Sign in here</a>
</p>

<!-- Form Validation Script -->
<script src="assets/js/validation.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
