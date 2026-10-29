<?php
/**
 * StudentHub - FAQ & Help
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';

$page_title = "Frequently Asked Questions - StudentHub";
$active_page = "faq";

include __DIR__ . '/includes/header.php';
?>

<h1>Frequently Asked Questions</h1>
<p>Click on any question below to expand or collapse the answer.</p>

<details>
    <summary>What student services are accessible through StudentHub?</summary>
    <p>
        StudentHub provides access to campus hackathon and workshop registrations, student profile directories, placement inquiries, and campus event information for students of CHARUSAT.
    </p>
</details>

<details>
    <summary>How do I register for campus hackathons and technical events?</summary>
    <p>
        Go to the <strong>Events</strong> page from the top navigation bar. If the event status is Open, click the "Register" button. You must be logged in with a student account to register.
    </p>
</details>

<details>
    <summary>How does the theme switcher work?</summary>
    <p>
        The portal supports switching between light and dark themes using JavaScript. Your choice is saved in browser localStorage so it persists across visits.
    </p>
    <p>
        <button type="button" class="btn theme-toggle-trigger">Toggle Theme (Light / Dark)</button>
    </p>
</details>

<details>
    <summary>Who do I contact for placement and training cell support?</summary>
    <p>
        You can submit a message on the <strong>Contact</strong> page by selecting "Placement & Training Query", or email the placement cell directly at placement@charusat.edu.in.
    </p>
</details>

<details>
    <summary>Where can I view my registered events?</summary>
    <p>
        Log in to your account and open the <strong>Dashboard</strong> page. All events you have registered for will be displayed in a table.
    </p>
</details>

<h2>Help & Support Information</h2>
<p>Click the button below to view helpline contact details:</p>
<p>
    <button type="button" onclick="openCustomModal('demoModal')">View Helpline Details</button>
</p>

<!-- Simple Modal Dialog (Practical 4) -->
<div class="custom-modal-backdrop" id="demoModal" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <strong>Student Helpline Details</strong>
            <button type="button" onclick="closeCustomModal('demoModal')">Close</button>
        </div>
        <p>Faculty of Technology and Engineering (FTE / CSPIT)</p>
        <p>Charotar University of Science and Technology (CHARUSAT)</p>
        <p>Office Hours: Monday - Saturday: 9:00 AM to 5:00 PM</p>
        <p>Phone: +91 2697 265011 / 265021</p>
        <p>Email: studenthub@charusat.edu.in</p>
        <div class="custom-modal-footer">
            <button type="button" onclick="closeCustomModal('demoModal')">Close Dialog</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
