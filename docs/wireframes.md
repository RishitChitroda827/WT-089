# StudentHub Portal - Low-Fidelity UI Wireframes
**CHAROTAR UNIVERSITY OF SCIENCE AND TECHNOLOGY (CHARUSAT)**  
**Faculty of Technology and Engineering (FTE / CSPIT)**  
**Subject:** Web Development Frameworks (AIUE203) — Practical 1

---

## 1. Homepage Wireframe (`index.php`)

```
+-----------------------------------------------------------------------------+
| [LOGO] CHAROTAR UNIVERSITY OF SCIENCE & TECHNOLOGY          [Practicals 1-15]|
+-----------------------------------------------------------------------------+
| [StudentHub]  Home  About  Events  FAQ  Data(P6)  Contact  Feedback   [Dark] [Login]|
+-----------------------------------------------------------------------------+
|                                                                             |
|  +-----------------------------------------------------------------------+  |
|  | [Prev]                     HERO SLIDER                         [Next] |  |
|  |  Welcome to StudentHub Portal                                         |  |
|  |  Central gateway for FTE / CSPIT students at CHARUSAT.                |  |
|  |  [ Browse Events ]    [ Learn More ]                                  |  |
|  +-----------------------------------------------------------------------+  |
|                                                                             |
|  +------------------+ +------------------+ +------------------+ +----------+  |
|  | ENROLLED STUDENTS| | ACTIVE EVENTS    | | REGISTRATIONS    | | MODULES  |  |
|  |      20+         | |       8          | |      25+         | |   15     |  |
|  +------------------+ +------------------+ +------------------+ +----------+  |
|                                                                             |
|  STUDENT SUPPORT MODULES:                                                   |
|  +------------------+ +------------------+ +------------------+ +----------+  |
|  | Placement Cell   | | Clubs & Society  | | Admissions/Exams | | Sports   |  |
|  | Placement Drives | | Hackathons & Tech| | Timetables & Form| | Athletics|  |
|  | [Query]          | | [Explore]        | | [FAQs]           | | [Events] |  |
|  +------------------+ +------------------+ +------------------+ +----------+  |
|                                                                             |
|  FEATURED CAMPUS EVENTS:                                                    |
|  +---------------------------+ +---------------------------+                |
|  | [Category]         [Open] | | [Category]         [Open] |                |
|  | CHARUSAT Hackathon 2026   | | Web Development Workshop  |                |
|  | Date: 15 Nov 2026         | | Date: 18 Nov 2026         |                |
|  | Venue: Computer Center    | | Venue: Seminar Hall       |                |
|  | [ Details & Register ]    | | [ Details & Register ]    |                |
|  +---------------------------+ +---------------------------+                |
+-----------------------------------------------------------------------------+
| FOOTER: Quick Links | Practicals Index | Campus Address | Copyright © 2026  |
+-----------------------------------------------------------------------------+
```

---

## 2. Student Registration Wireframe (`register.php`)

```
+-----------------------------------------------------------------------------+
|                              STUDENT REGISTRATION                           |
|       (Practical 5 Client Validation + Practical 9 Password Hashing)        |
+-----------------------------------------------------------------------------+
|                                                                             |
|  Full Legal Name:             [  Enter full name                  ]         |
|                               (letters and spaces, min 2 chars)             |
|                                                                             |
|  Enrollment Number:           [  23DCS089                         ]         |
|                               (alphanumeric university registry)            |
|                                                                             |
|  University Email:            [  student@charusat.edu.in          ]         |
|                                                                             |
|  Mobile Contact:              [  9876543210                       ]         |
|                               (10 digit number)                             |
|                                                                             |
|  Department / Course:         [ Select Course...                 v]         |
|                               (B.Tech AI-ML, CSE, IT, BCA)                  |
|                                                                             |
|  Study Year:                  [ Select Year...                   v]         |
|                                                                             |
|  Gender:                      (o) Male   ( ) Female   ( ) Other             |
|                                                                             |
|  Password:                    [ ********                          ]         |
|  Password Strength:           [==== Medium Strength =====         ]         |
|                                                                             |
|  Confirm Password:            [ ********                          ]         |
|                                                                             |
|  [X] I accept the terms and student conduct regulations.                    |
|                                                                             |
|             [ Reset Form ]                   [ Register Account ]           |
|                                                                             |
+-----------------------------------------------------------------------------+
```

---

## 3. Admin Dashboard Wireframe (`admin_dashboard.php`)

```
+-----------------------------------------------------------------------------+
| [ADMIN] StudentHub Administration Control Panel                             |
+-----------------------------------------------------------------------------+
|                                                                             |
|  +------------------+ +------------------+ +------------------+ +----------+  |
|  | TOTAL USERS      | | ENROLLED STUDENTS| | CAMPUS EVENTS    | | REGISTR. |  |
|  |       3          | |       21         | |       8          | |    8     |  |
|  +------------------+ +------------------+ +------------------+ +----------+  |
|                                                                             |
|  ADMINISTRATION MODULES:                                                    |
|  [ Manage Students (P11) ]  [ Events & Posters (P12) ]  [ AJAX CRUD (P13) ]  |
|                                                                             |
|  LATEST EVENT REGISTRATIONS:           | SECURITY AUDIT TRAIL:              |
|  +-----------------------------------+ | +--------------------------------+ |
|  | Student       | Event     | Date  | | | Action     | User    | Time     | |
|  |---------------+-----------+-------| | |------------+---------+----------| |
|  | Rishit Patel  | AI Hack   | 28 Oct| | | LOGIN      | admin   | 18:23:40 | |
|  | Aarav Shah    | React PHP | 28 Oct| | | ADD_STUDENT| admin   | 18:21:10 | |
|  | Diya Mehta    | Sports    | 27 Oct| | | FILE_UPLOAD| admin   | 18:15:20 | |
|  +-----------------------------------+ | +--------------------------------+ |
|                                                                             |
+-----------------------------------------------------------------------------+
```
