<?php
/**
 * StudentHub - About Portal
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';

$page_title = "About StudentHub";
$active_page = "about";

include __DIR__ . '/includes/header.php';
?>

<h1>About StudentHub</h1>

<h2>Mission and Purpose</h2>
<p>
    StudentHub is an integrated campus web portal developed for the Faculty of Technology and Engineering (FTE / CSPIT) at Charotar University of Science and Technology (CHARUSAT). It provides students, faculty coordinators, and campus administrators with a platform for event registrations, student directories, academic information, and administrative services.
</p>
<p>
    The portal is designed using standard semantic HTML5, CSS responsive layout, client-side JavaScript, server-side PHP, and a MySQL relational database.
</p>

<h2>Core Features</h2>
<ul>
    <li>
        <strong>Event Registration:</strong> Students can browse upcoming hackathons, technical workshops, and sports tournaments, and register directly online.
    </li>
    <li>
        <strong>Student Directory:</strong> Campus directory providing search and department filtering across enrolled student records.
    </li>
    <li>
        <strong>Campus Inquiries:</strong> Contact form for submitting queries related to placement, exams, and clubs.
    </li>
    <li>
        <strong>Role-Based Access:</strong> Secure authentication with password hashing, session tracking, and administrative controls.
    </li>
</ul>

<h2>Campus Information</h2>
<table>
    <tr>
        <th style="width: 30%;">University</th>
        <td>Charotar University of Science and Technology (CHARUSAT)</td>
    </tr>
    <tr>
        <th>Faculty / Institute</th>
        <td>Faculty of Technology and Engineering (FTE / CSPIT)</td>
    </tr>
    <tr>
        <th>Campus Location</th>
        <td>Changa - 388421, Dist. Anand, Gujarat, India</td>
    </tr>
    <tr>
        <th>Contact Email</th>
        <td>info@charusat.edu.in</td>
    </tr>
</table>

<?php include __DIR__ . '/includes/footer.php'; ?>
