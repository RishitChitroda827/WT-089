/**
 * StudentHub - External JSON Data Rendering via Fetch API (Practical 6)
 * Features: Fetch API, Search, Filter, Sort, Pagination, Error & Loading States, LocalStorage Cache
 */

document.addEventListener("DOMContentLoaded", function () {
    let eventsData = [];
    let studentsData = [];
    let faqsData = [];

    let currentEventPage = 1;
    let currentStudentPage = 1;
    let currentFaqPage = 1;
    const pageSize = 5;

    const loadingSpinner = document.getElementById("jsonLoading");
    const errorAlert = document.getElementById("jsonError");

    async function loadJsonWithCache(url, cacheKey) {
        try {
            const res = await fetch(url);
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            const data = await res.json();
            try {
                localStorage.setItem(cacheKey, JSON.stringify(data));
            } catch (e) {
                // storage quota exceeded or disabled, ignore
            }
            return data;
        } catch (err) {
            // Check cache
            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                console.warn(`Fetch failed for ${url}, using cached localStorage data.`);
                return JSON.parse(cached);
            }
            throw err;
        }
    }

    async function initializeData() {
        if (!document.getElementById("eventsContainer") && !document.getElementById("eventSearch")) {
            return; // Not on Practical 6 page
        }

        if (loadingSpinner) loadingSpinner.style.display = "block";
        if (errorAlert) errorAlert.style.display = "none";

        try {
            // Support both direct path and data/ path
            const [ev, st, fq] = await Promise.all([
                loadJsonWithCache("data/events.json", "cache_events").catch(() => loadJsonWithCache("events.json", "cache_events")),
                loadJsonWithCache("data/students.json", "cache_students").catch(() => loadJsonWithCache("students.json", "cache_students")),
                loadJsonWithCache("data/faqs.json", "cache_faqs").catch(() => loadJsonWithCache("faqs.json", "cache_faqs")).catch(() => loadJsonWithCache("faq.json", "cache_faqs"))
            ]);

            eventsData = ev || [];
            studentsData = st || [];
            faqsData = fq || [];

            if (loadingSpinner) loadingSpinner.style.display = "none";

            renderEvents();
            renderStudents();
            renderFaqs();
            populateCategoryFilter();
        } catch (err) {
            if (loadingSpinner) loadingSpinner.style.display = "none";
            if (errorAlert) {
                errorAlert.style.display = "block";
                errorAlert.textContent = "Error fetching JSON data. Please verify file paths: " + err.message;
            }
            console.error("JSON Fetch error:", err);
        }
    }

    // 1. EVENTS RENDERING, SEARCH, FILTER, SORT & PAGINATION
    function getFilteredEvents() {
        const searchInput = document.getElementById("eventSearch");
        const categoryFilter = document.getElementById("eventCategoryFilter");
        const sortSelect = document.getElementById("eventSort");

        const q = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const cat = categoryFilter ? categoryFilter.value : "all";
        const sort = sortSelect ? sortSelect.value : "date-asc";

        let result = eventsData.filter(item => {
            const matchesSearch = !q || (item.title && item.title.toLowerCase().includes(q)) ||
                                  (item.venue && item.venue.toLowerCase().includes(q)) ||
                                  (item.location && item.location.toLowerCase().includes(q)) ||
                                  (item.description && item.description.toLowerCase().includes(q));
            const matchesCat = (cat === "all") || (item.category === cat);
            return matchesSearch && matchesCat;
        });

        result.sort((a, b) => {
            if (sort === "title-asc") return a.title.localeCompare(b.title);
            if (sort === "title-desc") return b.title.localeCompare(a.title);
            const dateA = new Date(a.date || a.event_date || '2026-01-01');
            const dateB = new Date(b.date || b.event_date || '2026-01-01');
            return sort === "date-desc" ? dateB - dateA : dateA - dateB;
        });

        return result;
    }

    function renderEvents() {
        const container = document.getElementById("eventsContainer");
        if (!container) return;

        const filtered = getFilteredEvents();
        const total = filtered.length;
        const totalPages = Math.ceil(total / pageSize) || 1;
        if (currentEventPage > totalPages) currentEventPage = 1;

        const start = (currentEventPage - 1) * pageSize;
        const pageItems = filtered.slice(start, start + pageSize);

        if (pageItems.length === 0) {
            container.innerHTML = `<p>No events match your criteria.</p>`;
        } else {
            container.innerHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${pageItems.map(ev => `
                            <tr>
                                <td><strong>${escapeHtml(ev.title)}</strong></td>
                                <td>${escapeHtml(ev.category || 'General')}</td>
                                <td>${escapeHtml(ev.date || ev.event_date || 'TBD')}</td>
                                <td>${escapeHtml(ev.venue || ev.location || 'Campus')}</td>
                                <td><small>${escapeHtml(ev.description || '')}</small></td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            `;
        }

        renderPagination("eventPagination", currentEventPage, totalPages, (p) => {
            currentEventPage = p;
            renderEvents();
        });

        const countBadge = document.getElementById("eventsCountBadge");
        if (countBadge) countBadge.textContent = `${total} Events Found`;
    }

    // 2. STUDENTS RENDERING, SEARCH & PAGINATION
    function getFilteredStudents() {
        const searchInput = document.getElementById("studentSearch");
        const courseFilter = document.getElementById("studentCourseFilter");
        const q = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const course = courseFilter ? courseFilter.value : "all";

        return studentsData.filter(s => {
            const matchesQ = !q || (s.name && s.name.toLowerCase().includes(q)) ||
                             (s.enrollment_no && s.enrollment_no.toLowerCase().includes(q)) ||
                             (s.email && s.email.toLowerCase().includes(q));
            const matchesCourse = (course === "all") || (s.course === course);
            return matchesQ && matchesCourse;
        });
    }

    function renderStudents() {
        const tbody = document.getElementById("studentsTableBody");
        if (!tbody) return;

        const filtered = getFilteredStudents();
        const total = filtered.length;
        const totalPages = Math.ceil(total / pageSize) || 1;
        if (currentStudentPage > totalPages) currentStudentPage = 1;

        const start = (currentStudentPage - 1) * pageSize;
        const pageItems = filtered.slice(start, start + pageSize);

        if (pageItems.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">No student records found.</td></tr>`;
        } else {
            tbody.innerHTML = pageItems.map(s => `
                <tr>
                    <td><strong>${escapeHtml(s.name)}</strong></td>
                    <td><code>${escapeHtml(s.enrollment_no || s.enrollment || 'N/A')}</code></td>
                    <td>${escapeHtml(s.course)}</td>
                    <td>Year ${escapeHtml(String(s.year || 3))}</td>
                    <td><a href="mailto:${escapeHtml(s.email)}">${escapeHtml(s.email)}</a></td>
                </tr>
            `).join("");
        }

        renderPagination("studentPagination", currentStudentPage, totalPages, (p) => {
            currentStudentPage = p;
            renderStudents();
        });

        const countBadge = document.getElementById("studentsCountBadge");
        if (countBadge) countBadge.textContent = `${total} Students`;
    }

    // 3. FAQS RENDERING & SEARCH
    function renderFaqs() {
        const container = document.getElementById("faqsListContainer");
        if (!container) return;

        const searchInput = document.getElementById("faqSearch");
        const q = searchInput ? searchInput.value.toLowerCase().trim() : "";

        const filtered = faqsData.filter(f => {
            return !q || (f.question && f.question.toLowerCase().includes(q)) ||
                         (f.answer && f.answer.toLowerCase().includes(q)) ||
                         (f.q && f.q.toLowerCase().includes(q)) ||
                         (f.a && f.a.toLowerCase().includes(q));
        });

        if (filtered.length === 0) {
            container.innerHTML = `<p>No FAQs found matching your query.</p>`;
            return;
        }

        container.innerHTML = filtered.map((f, i) => {
            const question = f.question || f.q || "FAQ Question";
            const answer = f.answer || f.a || "Answer details.";
            return `
                <details>
                    <summary>${escapeHtml(question)}</summary>
                    <p>${escapeHtml(answer)}</p>
                </details>
            `;
        }).join("");
    }

    function populateCategoryFilter() {
        const select = document.getElementById("eventCategoryFilter");
        if (!select || select.children.length > 1) return;

        const categories = [...new Set(eventsData.map(e => e.category).filter(Boolean))];
        categories.forEach(cat => {
            const opt = document.createElement("option");
            opt.value = cat;
            opt.textContent = cat;
            select.appendChild(opt);
        });
    }

    function renderPagination(containerId, currentPage, totalPages, onPageChange) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = "";
            return;
        }

        let html = `<ul class="pagination pagination-sm justify-content-center mb-0">`;
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <button class="page-link" data-page="${currentPage - 1}">Previous</button>
                 </li>`;

        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <button class="page-link" data-page="${i}">${i}</button>
                     </li>`;
        }

        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <button class="page-link" data-page="${currentPage + 1}">Next</button>
                 </li>`;
        html += `</ul>`;

        container.innerHTML = html;
        container.querySelectorAll("button[data-page]").forEach(btn => {
            btn.addEventListener("click", function () {
                const targetPage = parseInt(this.getAttribute("data-page"), 10);
                if (targetPage >= 1 && targetPage <= totalPages && targetPage !== currentPage) {
                    onPageChange(targetPage);
                }
            });
        });
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

    // Attach listeners
    const eventSearch = document.getElementById("eventSearch");
    if (eventSearch) eventSearch.addEventListener("input", () => { currentEventPage = 1; renderEvents(); });

    const eventCat = document.getElementById("eventCategoryFilter");
    if (eventCat) eventCat.addEventListener("change", () => { currentEventPage = 1; renderEvents(); });

    const eventSort = document.getElementById("eventSort");
    if (eventSort) eventSort.addEventListener("change", () => { currentEventPage = 1; renderEvents(); });

    const studentSearch = document.getElementById("studentSearch");
    if (studentSearch) studentSearch.addEventListener("input", () => { currentStudentPage = 1; renderStudents(); });

    const studentCourse = document.getElementById("studentCourseFilter");
    if (studentCourse) studentCourse.addEventListener("change", () => { currentStudentPage = 1; renderStudents(); });

    const faqSearch = document.getElementById("faqSearch");
    if (faqSearch) faqSearch.addEventListener("input", renderFaqs);

    initializeData();
});
