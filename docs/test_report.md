# StudentHub Portal - Comprehensive Test Report
**CHAROTAR UNIVERSITY OF SCIENCE AND TECHNOLOGY (CHARUSAT)**  
**Faculty of Technology and Engineering (FTE / CSPIT)**  
**Subject:** Web Development Frameworks (AIUE203) — Practical 15  
**Candidate ID:** 23DCS089 (AI & ML)

---

## 1. Test Execution Summary

- **Total Test Cases Executed:** 15
- **Passed:** 15
- **Failed:** 0
- **Pass Rate:** 100%
- **Environment:** Windows 11 x64, PHP 8.2.12 (ZTS), MySQL Server 8.0, Modern Chromium & Firefox Browsers.

---

## 2. Practical-by-Practical Test Cases & Results

| Test ID | Practical Module | Test Scenario & Input | Expected Result | Actual Result | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **TC-01** | **P1: Requirements & Sitemap** | Verify folder organization, sitemap, wireframes, and GitHub setup | Complete navigable hierarchy, README, and wireframes available | Documented in `docs/` and accessible via `practicals.php` | **PASS** |
| **TC-02** | **P2: Semantic HTML5** | Inspect 10+ pages for HTML5 tags (`<header>`, `<nav>`, `<main>`, `<article>`, `<aside>`, `<footer>`) | W3C semantic structure and accessible skip-links present | Verified on `about.php`, `index.php`, `contact.php`, etc. | **PASS** |
| **TC-03** | **P3: Responsive UI** | Resize viewport to 375px (Mobile), 768px (Tablet), and 1440px (Desktop) | Layout flexes without horizontal scrolling; navbar collapses into hamburger menu | Grid and Flexbox adapt cleanly across all devices | **PASS** |
| **TC-04** | **P4: DOM Interactivity** | 1. Toggle dark/light mode button.<br>2. Open & close modal dialog.<br>3. Toggle accordion FAQ. | 1. Theme persists via `localStorage`.<br>2. Modal opens with overlay.<br>3. Accordion toggles answer panel. | All dynamic DOM manipulations execute without console errors | **PASS** |
| **TC-05** | **P5: Client-side Validation** | Input invalid mobile (`12345`), mismatching passwords, empty name on `register.php` | Real-time inline feedback and strength meter updates; submission blocked | Blocked with precise red validation messages; strong password badge shown | **PASS** |
| **TC-06** | **P6: JSON Fetch & Filter** | Fetch `events.json`, search "hackathon", filter by "Technical", change page | Dynamically renders event cards, filters list, updates pagination | Fetch API loads data; debounced search and pagination functional | **PASS** |
| **TC-07** | **P7: Server CSV Storage** | Submit contact form via POST on `contact.php` | Server sanitizes inputs and safely appends row to `data/contacts.csv` | Record appended to CSV; viewable on `view_csv.php` | **PASS** |
| **TC-08** | **P8: Database Schema** | Execute `setup_db.php` connecting with MySQLi | Creates normalized database `studenthub` with 6 tables and foreign keys | Connection successful with smart password fallback (`123456`/empty) | **PASS** |
| **TC-09** | **P9: Secure Registration** | Register new student on `register.php` with valid inputs | Password hashed with `password_hash()`, duplicate check runs, user saved to DB | User added to `users` and `students` tables; plain text never stored | **PASS** |
| **TC-10** | **P10: Session RBAC** | Login as `student/student123` then attempt to open `admin_dashboard.php` | Access blocked with HTTP 403 Forbidden; redirects to student dashboard | RBAC enforced; timeout after 15 min inactivity operational | **PASS** |
| **TC-11** | **P11: Student CRUD** | Add student, search by name, edit course, delete student, export to CSV | All CRUD operations succeed; CSV file downloads immediately | 21+ students loaded; MySQLi prepared statements prevent SQL injection | **PASS** |
| **TC-12** | **P12: Event Poster Upload** | Upload 3MB file or `.exe` file vs valid 500KB `.png` image | Invalid types rejected; valid image renamed cryptographically and saved | File size & MIME checked via `finfo`; thumbnail rendered in table | **PASS** |
| **TC-13** | **P13: AJAX CRUD** | Create and delete event on `ajax_crud.php` without reloading browser | Asynchronous `fetch()` calls `api/events.php`; table updates dynamically | Full asynchronous CRUD without full page refresh; status toast shown | **PASS** |
| **TC-14** | **P14: Admin Dashboard** | Login as `admin/admin123` and view `admin_dashboard.php` | Dynamic counters reflect exact database rows; audit trail logged | Total users, students, events, registrations accurately aggregated | **PASS** |
| **TC-15** | **P15: Integration** | Navigate complete portal workflow across all 15 practicals | End-to-end user journeys complete without 404s or broken links | Unified portal deployable via XAMPP or PHP built-in web server | **PASS** |

---

## 3. Security Verification Checklist

- [x] **SQL Injection Protection:** All database queries utilize prepared statements with parameter binding (`$stmt->bind_param`).
- [x] **XSS Mitigation:** Output sanitization using `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
- [x] **Password Security:** One-way irreversible hashing using standard `password_hash($pass, PASSWORD_DEFAULT)` and verified via `password_verify()`.
- [x] **Session Fixation Prevention:** Session ID regenerated upon authentication via `session_regenerate_id(true)`.
- [x] **Inactivity Timeout:** Sessions automatically terminated after 15 minutes of idle time.
- [x] **File Upload Security:** Upload directory forbidden from PHP execution; MIME validation using `finfo` and `getimagesize`; files cryptographically renamed.
