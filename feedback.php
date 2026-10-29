<?php
/**
 * StudentHub - Student Feedback Form
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';

$page_title = "Student Feedback - StudentHub";
$active_page = "feedback";

$submitted = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submitted = true;
    logAudit($conn, "FEEDBACK_SUBMIT", "Feedback received from user");
}

include __DIR__ . '/includes/header.php';
?>

<h1>Student Feedback</h1>
<p>Your suggestions help improve the campus portal and student activities.</p>

<?php if ($submitted): ?>
    <div class="alert alert-success">
        <strong>Thank You!</strong> Your feedback has been successfully submitted.
    </div>
    <p><a href="index.php">Return to Homepage</a></p>
<?php else: ?>
    <form method="POST" action="feedback.php">
        <div class="form-group">
            <label for="fb_name">Full Name (Optional):</label>
            <input type="text" id="fb_name" name="name" placeholder="Leave blank for anonymous">
        </div>

        <div class="form-group">
            <label for="fb_email">Email Address:</label>
            <input type="email" id="fb_email" name="email" placeholder="student@charusat.edu.in">
        </div>

        <div class="form-group">
            <label for="fb_dept">Department / Branch: *</label>
            <select id="fb_dept" name="department" required>
                <option value="">-- Select Department --</option>
                <option value="AI & ML">AI & Machine Learning (CSPIT)</option>
                <option value="CSE">Computer Science & Engineering</option>
                <option value="IT">Information Technology</option>
                <option value="BCA">Bachelor of Computer Applications</option>
                <option value="MCA">Master of Computer Applications</option>
            </select>
        </div>

        <div class="form-group">
            <label>Overall Portal Experience Rating: *</label>
            <div>
                <label style="display: inline-block; font-weight: normal; margin-right: 15px;">
                    <input type="radio" name="rating" value="5" checked> 5 - Excellent
                </label>
                <label style="display: inline-block; font-weight: normal; margin-right: 15px;">
                    <input type="radio" name="rating" value="4"> 4 - Good
                </label>
                <label style="display: inline-block; font-weight: normal; margin-right: 15px;">
                    <input type="radio" name="rating" value="3"> 3 - Average
                </label>
                <label style="display: inline-block; font-weight: normal; margin-right: 15px;">
                    <input type="radio" name="rating" value="2"> 2 - Fair
                </label>
                <label style="display: inline-block; font-weight: normal;">
                    <input type="radio" name="rating" value="1"> 1 - Poor
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="fb_comments">Comments and Suggestions: *</label>
            <textarea id="fb_comments" name="comments" rows="5" required placeholder="Write your suggestions here..."></textarea>
        </div>

        <div class="form-group">
            <input type="submit" value="Submit Feedback">
            <input type="reset" value="Clear">
        </div>
    </form>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
