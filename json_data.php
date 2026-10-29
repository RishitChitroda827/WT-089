<?php
/**
 * StudentHub - Campus Directory (Fetch API Data)
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';

$page_title = "Campus Directory - StudentHub";
$active_page = "json_data";

include __DIR__ . '/includes/header.php';
?>

<h1>Campus Directory</h1>
<p>Search and filter events, enrolled student records, and FAQs using client-side JavaScript Fetch API.</p>

<!-- Loading and Error States -->
<div id="jsonLoading" style="display: none; padding: 10px 0;">
    Loading records from JSON files...
</div>
<div id="jsonError" class="alert alert-danger" style="display: none;"></div>

<!-- Tab Navigation -->
<div style="margin: 15px 0;">
    <button type="button" class="btn" id="tab-events-btn" onclick="switchDirectoryTab('events')">Events (<span id="eventsCountBadge">...</span>)</button>
    <button type="button" class="btn btn-secondary" id="tab-students-btn" onclick="switchDirectoryTab('students')">Students (<span id="studentsCountBadge">...</span>)</button>
    <button type="button" class="btn btn-secondary" id="tab-faqs-btn" onclick="switchDirectoryTab('faqs')">FAQs</button>
</div>

<!-- SECTION 1: EVENTS -->
<div id="section-events" style="display: block;">
    <h2>Events Directory</h2>
    <div style="margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 10px;">
        <div>
            <label for="eventSearch">Search Events:</label>
            <input type="text" id="eventSearch" placeholder="Search by title, venue...">
        </div>
        <div>
            <label for="eventCategoryFilter">Category:</label>
            <select id="eventCategoryFilter">
                <option value="all">All Categories</option>
            </select>
        </div>
        <div>
            <label for="eventSort">Sort:</label>
            <select id="eventSort">
                <option value="date-asc">Date (Earliest First)</option>
                <option value="date-desc">Date (Latest First)</option>
                <option value="title-asc">Title (A - Z)</option>
                <option value="title-desc">Title (Z - A)</option>
            </select>
        </div>
    </div>

    <div id="eventsContainer"></div>
    <div id="eventPagination" style="margin-top: 10px;"></div>
</div>

<!-- SECTION 2: STUDENTS -->
<div id="section-students" style="display: none;">
    <h2>Enrolled Students Directory</h2>
    <div style="margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 10px;">
        <div>
            <label for="studentSearch">Search Students:</label>
            <input type="text" id="studentSearch" placeholder="Search by name, enrollment, email...">
        </div>
        <div>
            <label for="studentCourseFilter">Course:</label>
            <select id="studentCourseFilter">
                <option value="all">All Courses</option>
                <option value="B.Tech AI-ML">B.Tech AI-ML</option>
                <option value="B.Tech CSE">B.Tech CSE</option>
                <option value="B.Tech IT">B.Tech IT</option>
                <option value="BCA">BCA</option>
                <option value="MCA">MCA</option>
            </select>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Enrollment No</th>
                <th>Course</th>
                <th>Year</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody id="studentsTableBody"></tbody>
    </table>
    <div id="studentPagination" style="margin-top: 10px;"></div>
</div>

<!-- SECTION 3: FAQS -->
<div id="section-faqs" style="display: none;">
    <h2>Frequently Asked Questions</h2>
    <div style="margin-bottom: 12px;">
        <label for="faqSearch">Search FAQs:</label>
        <input type="text" id="faqSearch" placeholder="Type keywords...">
    </div>
    <div id="faqsListContainer"></div>
</div>

<script>
function switchDirectoryTab(tabName) {
    document.getElementById('section-events').style.display = tabName === 'events' ? 'block' : 'none';
    document.getElementById('section-students').style.display = tabName === 'students' ? 'block' : 'none';
    document.getElementById('section-faqs').style.display = tabName === 'faqs' ? 'block' : 'none';

    document.getElementById('tab-events-btn').className = tabName === 'events' ? 'btn' : 'btn btn-secondary';
    document.getElementById('tab-students-btn').className = tabName === 'students' ? 'btn' : 'btn btn-secondary';
    document.getElementById('tab-faqs-btn').className = tabName === 'faqs' ? 'btn' : 'btn btn-secondary';
}
</script>

<!-- Practical 6 Fetch Data Script -->
<script src="assets/js/fetch_data.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
