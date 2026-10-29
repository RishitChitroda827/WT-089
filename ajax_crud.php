<?php
/**
 * StudentHub - Asynchronous AJAX / Fetch API Event CRUD
 * Web Development Frameworks (AIUE203)
 */

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$page_title = "Quick Event Management (AJAX)";
$active_page = "ajax_crud";

include __DIR__ . '/includes/header.php';
?>

<h1>Quick Event Management (AJAX)</h1>
<p>Manage events in real-time with instant updates and live search without page reloads using the Fetch API.</p>

<p>
    <button type="button" onclick="openAddEventModal()">+ Add New Event (AJAX)</button> | 
    <a href="api/events.php" target="_blank">View Raw JSON API</a> | 
    <a href="admin_dashboard.php">&larr; Return to Admin Dashboard</a>
</p>

<!-- Dynamic Alert Message -->
<div id="ajaxAlert" class="alert alert-info" style="display: none;">
    <span id="ajaxAlertMsg"></span>
</div>

<!-- Search Controls -->
<div style="margin: 15px 0; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
    <div>
        <label for="ajaxSearchInput">Live Search:</label>
        <input type="text" id="ajaxSearchInput" placeholder="Type event name or venue...">
    </div>
    <div style="margin-top: 20px;">
        <button type="button" onclick="loadEventsAjax()">Refresh Table</button>
    </div>
</div>

<div id="ajaxLoadingSpinner" style="display: none; padding: 10px 0;">
    Loading events asynchronously via Fetch API...
</div>

<!-- Events Table -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title & Description</th>
            <th>Category</th>
            <th>Event Date</th>
            <th>Venue</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="ajaxEventsTbody">
        <!-- Loaded via Fetch API -->
    </tbody>
</table>

<!-- Simple AJAX Modal for Add / Edit -->
<div class="custom-modal-backdrop" id="eventAjaxModal" role="dialog" aria-modal="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <strong id="ajaxModalTitle">Add New Event</strong>
            <button type="button" onclick="closeCustomModal('eventAjaxModal')">Close</button>
        </div>
        <form id="ajaxEventForm">
            <input type="hidden" id="modal_event_id" value="">

            <div class="form-group">
                <label for="modal_title">Event Title: *</label>
                <input type="text" id="modal_title" required>
            </div>

            <div class="form-group">
                <label for="modal_category">Category: *</label>
                <select id="modal_category" required>
                    <option value="Technical">Technical</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Sports">Sports</option>
                    <option value="Cultural">Cultural</option>
                    <option value="Seminar">Seminar</option>
                </select>
            </div>

            <div class="form-group">
                <label for="modal_date">Event Date: *</label>
                <input type="date" id="modal_date" required>
            </div>

            <div class="form-group">
                <label for="modal_venue">Venue: *</label>
                <input type="text" id="modal_venue" required>
            </div>

            <div class="form-group">
                <label for="modal_status">Status:</label>
                <select id="modal_status">
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>

            <div class="form-group">
                <label for="modal_description">Description: *</label>
                <textarea id="modal_description" rows="3" required></textarea>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCustomModal('eventAjaxModal')">Cancel</button>
                <button type="submit" id="modalSubmitBtn" class="btn">Save Event</button>
            </div>
        </form>
    </div>
</div>

<script>
// Practical 13 Asynchronous Fetch API CRUD Logic
document.addEventListener("DOMContentLoaded", function () {
    loadEventsAjax();

    const searchInput = document.getElementById("ajaxSearchInput");
    let debounceTimer;
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                loadEventsAjax(this.value.trim());
            }, 300);
        });
    }

    const form = document.getElementById("ajaxEventForm");
    if (form) {
        form.addEventListener("submit", handleEventFormSubmit);
    }
});

function showAjaxAlert(msg, type = "info") {
    const alertBox = document.getElementById("ajaxAlert");
    const msgBox = document.getElementById("ajaxAlertMsg");
    alertBox.className = `alert alert-${type}`;
    msgBox.textContent = msg;
    alertBox.style.display = "block";
    setTimeout(() => { alertBox.style.display = "none"; }, 4000);
}

async function loadEventsAjax(search = "") {
    const spinner = document.getElementById("ajaxLoadingSpinner");
    const tbody = document.getElementById("ajaxEventsTbody");
    spinner.style.display = "block";
    tbody.innerHTML = "";

    try {
        const url = search ? `api/events.php?search=${encodeURIComponent(search)}` : "api/events.php";
        const res = await fetch(url);
        const json = await res.json();
        spinner.style.display = "none";

        if (!json.success || !json.data || json.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7">No events found matching "${search}".</td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(ev => `
            <tr>
                <td>#${ev.id}</td>
                <td>
                    <strong>${escapeHtml(ev.title)}</strong><br>
                    <small>${escapeHtml((ev.description || '').substring(0, 70))}...</small>
                </td>
                <td>${escapeHtml(ev.category)}</td>
                <td>${escapeHtml(ev.event_date)}</td>
                <td>${escapeHtml(ev.venue)}</td>
                <td>${escapeHtml(ev.status)}</td>
                <td>
                    <button type="button" class="btn btn-sm" onclick="openEditEventModal(${ev.id})">Edit</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEventAjax(${ev.id}, '${escapeHtml(ev.title)}')">Delete</button>
                </td>
            </tr>
        `).join("");
    } catch (err) {
        spinner.style.display = "none";
        showAjaxAlert("Network error fetching events: " + err.message, "danger");
    }
}

function openAddEventModal() {
    document.getElementById("ajaxModalTitle").textContent = "Add New Event (AJAX)";
    document.getElementById("modal_event_id").value = "";
    document.getElementById("modal_title").value = "";
    document.getElementById("modal_category").value = "Technical";
    document.getElementById("modal_date").value = new Date().toISOString().split("T")[0];
    document.getElementById("modal_venue").value = "CSPIT Lab";
    document.getElementById("modal_status").value = "Open";
    document.getElementById("modal_description").value = "";
    openCustomModal("eventAjaxModal");
}

async function openEditEventModal(id) {
    try {
        const res = await fetch(`api/events.php?id=${id}`);
        const json = await res.json();
        if (!json.success || !json.data) {
            showAjaxAlert("Could not fetch event details.", "danger");
            return;
        }

        const ev = json.data;
        document.getElementById("ajaxModalTitle").textContent = `Edit Event #${ev.id}`;
        document.getElementById("modal_event_id").value = ev.id;
        document.getElementById("modal_title").value = ev.title;
        document.getElementById("modal_category").value = ev.category;
        document.getElementById("modal_date").value = ev.event_date;
        document.getElementById("modal_venue").value = ev.venue;
        document.getElementById("modal_status").value = ev.status;
        document.getElementById("modal_description").value = ev.description;
        openCustomModal("eventAjaxModal");
    } catch (err) {
        showAjaxAlert("Failed to load event: " + err.message, "danger");
    }
}

async function handleEventFormSubmit(e) {
    e.preventDefault();
    const id = document.getElementById("modal_event_id").value;
    const isEdit = Boolean(id);

    const payload = {
        title: document.getElementById("modal_title").value.trim(),
        category: document.getElementById("modal_category").value,
        event_date: document.getElementById("modal_date").value,
        venue: document.getElementById("modal_venue").value.trim(),
        status: document.getElementById("modal_status").value,
        description: document.getElementById("modal_description").value.trim()
    };

    if (isEdit) {
        payload.id = parseInt(id, 10);
    }

    try {
        const res = await fetch("api/events.php", {
            method: isEdit ? "PUT" : "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        const json = await res.json();

        if (json.success) {
            closeCustomModal("eventAjaxModal");
            showAjaxAlert(isEdit ? "Event updated successfully!" : "Event created successfully!", "success");
            loadEventsAjax();
        } else {
            showAjaxAlert("Error: " + (json.error || "Save operation failed"), "danger");
        }
    } catch (err) {
        showAjaxAlert("AJAX submit failed: " + err.message, "danger");
    }
}

async function deleteEventAjax(id, title) {
    if (!confirm(`Are you sure you want to delete event "${title}" (#${id})?`)) {
        return;
    }

    try {
        const res = await fetch(`api/events.php?id=${id}`, {
            method: "DELETE"
        });
        const json = await res.json();

        if (json.success) {
            showAjaxAlert(`Event #${id} deleted successfully.`, "success");
            loadEventsAjax();
        } else {
            showAjaxAlert("Delete failed: " + (json.error || "Unknown error"), "danger");
        }
    } catch (err) {
        showAjaxAlert("Network error during delete: " + err.message, "danger");
    }
}

function escapeHtml(str) {
    if (!str) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
