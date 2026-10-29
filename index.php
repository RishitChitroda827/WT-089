<?php
/**
 * StudentHub - Homepage
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';

$page_title = "StudentHub - Home";
$active_page = "home";

// Fetch up to 5 upcoming events from database
$upcoming_events = [];
try {
    $e_res = $conn->query("SELECT * FROM events WHERE status = 'Open' ORDER BY event_date ASC LIMIT 5");
    if ($e_res) {
        while ($row = $e_res->fetch_assoc()) {
            $upcoming_events[] = $row;
        }
    }
} catch (Throwable $e) {}

include __DIR__ . '/includes/header.php';
?>

<h1>StudentHub</h1>
<p>
    Welcome to StudentHub, the campus web portal for students and faculty. This portal allows students to view upcoming campus events, register for activities, access student services, and submit inquiries.
</p>

<h2>Upcoming Campus Events</h2>
<?php if (!empty($upcoming_events)): ?>
    <table>
        <thead>
            <tr>
                <th>Event Title</th>
                <th>Category</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($upcoming_events as $ev): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($ev['title']) ?></strong></td>
                    <td><?= htmlspecialchars($ev['category']) ?></td>
                    <td><?= htmlspecialchars($ev['event_date']) ?></td>
                    <td><?= htmlspecialchars($ev['venue']) ?></td>
                    <td><a href="events.php">View / Register</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="events.php">View all events &rarr;</a></p>
<?php else: ?>
    <p>No upcoming events scheduled at this moment.</p>
<?php endif; ?>

<h2>Student Services & Support</h2>
<ul>
    <li>
        <strong>Placement Cell:</strong> Guidance for campus recruitment drives, resume reviews, and mock interviews. 
        (<a href="contact.php">Contact Placement Cell</a>)
    </li>
    <li>
        <strong>Clubs & Activities:</strong> Technical coding clubs, IEEE student branches, and cultural activities. 
        (<a href="events.php">Browse Events</a>)
    </li>
    <li>
        <strong>Admissions & Exams:</strong> Syllabus copies, examination timetables, and academic guidelines. 
        (<a href="faq.php">Read FAQs</a>)
    </li>
    <li>
        <strong>Sports & Athletics:</strong> Inter-college tournaments, sports grounds, and annual athletic meets. 
        (<a href="events.php">View Sports Schedule</a>)
    </li>
</ul>

<?php include __DIR__ . '/includes/footer.php'; ?>
