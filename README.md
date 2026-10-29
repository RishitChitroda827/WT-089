# StudentHub Portal — Complete Practicals Implementation (1 - 15)
**CHAROTAR UNIVERSITY OF SCIENCE AND TECHNOLOGY (CHARUSAT)**  
**FACULTY OF TECHNOLOGY AND ENGINEERING (FTE / CSPIT)**  
**DEPARTMENT:** Artificial Intelligence & Machine Learning / Computer Engineering  
**SUBJECT:** Web Development Frameworks (AIUE203) — Semester 3 (ODD 2026-27)  
**STUDENT ENROLLMENT:** 23DCS089  

---

## 📌 Project Overview
**StudentHub** is an integrated full-stack campus web portal engineered for the students and administration of CHARUSAT. The application unifies student directory administration, campus event lifecycle management, asynchronous JSON APIs, secure role-based authentication, and responsive accessible frontend design.

All **15 Laboratory Practicals** prescribed in the AIUE203 syllabus are fully implemented, connected to a normalized MySQL database, documented, and testable end-to-end.

---

## 🛠️ Technology Stack
- **Frontend:** Semantic HTML5, CSS3, CSS Grid, Flexbox, Bootstrap 5.3.3, Vanilla JavaScript (ES6+), Fetch API.
- **Backend:** PHP 8.2+ (Procedural and Object-Oriented, MySQLi prepared statements, PDO).
- **Database:** MySQL 8.0 / MariaDB (Normalized 3NF relational schema).
- **Environment:** Local Apache / XAMPP / PHP CLI Built-in Web Server.

---

## 📑 Practical Mapping & Evaluation Matrix (Practicals 1 to 15)

| Practical No. | Practical Title | Core Technologies | CO Mapping | Primary Files & Launch Links |
| :---: | :--- | :--- | :---: | :--- |
| **Prac 1** | Project Initiation, Requirements, Sitemap, Wireframe & GitHub Setup | Requirements, Sitemap, Wireframing, Git | CO1 | [`docs/sitemap.md`](file:///d:/Charusat%20Clg/089_WT/docs/sitemap.md), [`docs/wireframes.md`](file:///d:/Charusat%20Clg/089_WT/docs/wireframes.md) |
| **Prac 2** | Semantic HTML5 Pages with Accessibility-Ready Structure | HTML5 semantic elements, WCAG AA, Skip Links | CO1, CO2 | [`about.php`](file:///d:/Charusat%20Clg/089_WT/about.php), [`index.php`](file:///d:/Charusat%20Clg/089_WT/index.php) |
| **Prac 3** | Responsive UI Design using CSS Grid and Flexbox | CSS3, Media Queries, CSS Grid, Flexbox | CO2 | [`assets/css/style.css`](file:///d:/Charusat%20Clg/089_WT/assets/css/style.css), [`events.php`](file:///d:/Charusat%20Clg/089_WT/events.php) |
| **Prac 4** | JavaScript DOM Manipulation, Event Handling & UI Interactivity | DOM events, Accordion, Modal, Theme Switcher, localStorage | CO3, CO4 | [`faq.php`](file:///d:/Charusat%20Clg/089_WT/faq.php), [`assets/js/main.js`](file:///d:/Charusat%20Clg/089_WT/assets/js/main.js) |
| **Prac 5** | Registration Form with Frontend Validation & Error Handling | Regular Expressions, Real-time Validation, Password Strength | CO1, CO3 | [`register.php`](file:///d:/Charusat%20Clg/089_WT/register.php), [`assets/js/validation.js`](file:///d:/Charusat%20Clg/089_WT/assets/js/validation.js) |
| **Prac 6** | Rendering External JSON Data using Fetch API, Search & Filter | Fetch API, Array filter/sort, Pagination, JSON cache | CO3, CO4 | [`json_data.php`](file:///d:/Charusat%20Clg/089_WT/json_data.php), [`pr6.html`](file:///d:/Charusat%20Clg/089_WT/pr6.html) |
| **Prac 7** | PHP Form Processing with Server Validation & CSV/JSON Storage | PHP POST, Sanitization, CSV (`fputcsv`), JSON storage | CO1, CO5 | [`contact.php`](file:///d:/Charusat%20Clg/089_WT/contact.php), [`view_csv.php`](file:///d:/Charusat%20Clg/089_WT/view_csv.php) |
| **Prac 8** | MySQL Schema Design, ER Model and Database Connectivity | 3NF Schema, MySQLi/PDO, Connection Fallback | CO1, CO5 | [`database.sql`](file:///d:/Charusat%20Clg/089_WT/database.sql), [`includes/db.php`](file:///d:/Charusat%20Clg/089_WT/includes/db.php) |
| **Prac 9** | Secure Registration with DB Insert, Duplicate Check & Hashing | `password_hash()`, Duplicate Check, Prepared Statements | CO1, CO3, CO5 | [`register.php`](file:///d:/Charusat%20Clg/089_WT/register.php) |
| **Prac 10** | Secure Login/Logout, Sessions, RBAC, Timeout & Remember-Me | `password_verify()`, `session_regenerate_id()`, RBAC, Timeout | CO1, CO5 | [`login.php`](file:///d:/Charusat%20Clg/089_WT/login.php), [`logout.php`](file:///d:/Charusat%20Clg/089_WT/logout.php) |
| **Prac 11** | Student Management CRUD with Search, Filter & CSV Export | MySQLi CRUD, Search, Course Filter, CSV Streaming | CO1, CO4, CO5 | [`students_manage.php`](file:///d:/Charusat%20Clg/089_WT/students_manage.php) |
| **Prac 12** | Event Management CRUD with Poster Upload & File Validation | MIME validation (`finfo`), File size limit, Event CRUD | CO1, CO4, CO5 | [`events_manage.php`](file:///d:/Charusat%20Clg/089_WT/events_manage.php) |
| **Prac 13** | PHP JSON API & AJAX/Fetch-Based CRUD without Page Reload | REST JSON API, Fetch API, Debounced Search, No Reload | CO3, CO4, CO5 | [`ajax_crud.php`](file:///d:/Charusat%20Clg/089_WT/ajax_crud.php), [`api/events.php`](file:///d:/Charusat%20Clg/089_WT/api/events.php) |
| **Prac 14** | Admin Dashboard with Role-Based Access Control & Audit Log | Dynamic Counters, SQL Aggregation, Audit Trail Log | CO1, CO4, CO5 | [`admin_dashboard.php`](file:///d:/Charusat%20Clg/089_WT/admin_dashboard.php), [`audit_logs.php`](file:///d:/Charusat%20Clg/089_WT/audit_logs.php) |
| **Prac 15** | Final Integration, Testing, Documentation & Local Deployment | Full-Stack Integration, Test Cases, Deployment | CO2, CO4, CO5 | [`index.php`](file:///d:/Charusat%20Clg/089_WT/index.php), [`docs/test_report.md`](file:///d:/Charusat%20Clg/089_WT/docs/test_report.md) |

---

## 🚀 Quick Setup & Local Deployment

### 1. Database Initialization (1-Click Automated Setup)
The project includes a smart database installer that works on both local setups (MySQL root password: `123456`) and default XAMPP environments (empty root password):
- **Option A (Browser):** Navigate to `http://localhost/setup_db.php` (or `http://localhost:8000/setup_db.php`)
- **Option B (Command Line):**
  ```powershell
  php setup_db.php
  ```
- **Option C (phpMyAdmin manual import):** Import `database.sql` directly into MySQL.

### 2. Starting the Application
You can run the portal using either XAMPP Apache or the PHP built-in web server:

- **Via PHP Built-in Server (Instant Testing):**
  ```powershell
  # Inside the project folder:
  php -S localhost:8000
  ```
  Then open [http://localhost:8000](http://localhost:8000) in your browser.

- **Via XAMPP:**
  Copy/symlink this directory to `C:\xampp\htdocs\089_WT` and visit [http://localhost/089_WT/](http://localhost/089_WT/).

---

## 🔑 Test User Credentials

| Role | Username / Identifier | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin` or `admin123` | Full administrative control, student CRUD, event CRUD, audit trail |
| **Student (General)** | `student` | `student` or `student123` | Student dashboard, event registrations, student profile |
| **Student (Roll 089)** | `rishit` | `student123` | 23DCS089 enrolled student profile (B.Tech AI-ML) |

---

## 🧪 Project Execution
Launch the local server using `run_server.bat` or run:
```powershell
php -S localhost:8000
```
Open **[http://localhost:8000/index.php](http://localhost:8000/index.php)** in your web browser.
