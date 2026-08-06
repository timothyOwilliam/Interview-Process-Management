# Interview Process Management

A web-based system for managing interview sessions, scoring applicants, and tracking recruitment workflows. Built with PHP (XAMPP) and vanilla HTML/CSS.

## System Overview

### User Roles

| Role | Responsibilities |
|------|------------------|
| **Admin** | Create sessions, generate Zoom links, assign interviewers, view/export reports |
| **ICT** | Manage user accounts (CRUD), assign roles |
| **HR** | View all sessions, view reports, schedule interviews, score applicants, manage applicants |
| **Job Applicant** | Create profile, apply for jobs, view application status |

### Core Flow

```
Applicant registers & creates profile
  → Admin creates interview session (pastes Zoom link, selects candidate & interviewers)
  → System generates scoring links for assigned interviewers
  → Interviewers join Zoom, fill assessment via scoring link
  → Once all submit → session marked completed
  → Admin/HR view reports, Admin can export to spreadsheet
```

---

## Tech Stack

- **Backend:** PHP (XAMPP — Apache + MySQL)
- **Frontend:** Vanilla HTML/CSS — no frameworks, no JS libraries
- **Database:** MySQL
- **Server:** XAMPP Apache required

---

## Project Structure

```
Interview-Process-Management/
├── frontend/
│   ├── style.css                    ← All shared styles
│   ├── login.html                   ← Login page (wired to backend)
│   ├── register.html                ← Applicant registration (wired to backend)
│   ├── assets/
│   │   └── NCIA-Logo-2022.png
│   └── dashboards/
│       ├── admin-dashboard.html     ← Admin landing page
│       ├── ict-dashboard.html       ← ICT landing page
│       ├── hr-dashboard.html        ← HR landing page
│       └── applicant-dashboard.html ← Applicant landing page
│
├── backend/
│   ├── config/
│   │   └── database.php             ← PDO connection class
│   ├── database/
│   │   └── schema.sql               ← Database schema (4 tables)
│   ├── login-validation.php         ← Auth + role-based redirects
│   ├── register-validation.php      ← Applicant registration logic
│   └── seed-admin.php               ← Seeds default admin account
│
├── README.md
├── AGENTS.md                        ← Agent guide (not pushed to remote)
└── .gitignore
```

---

## Database Schema

**Database name:** `interview_management_db`

### Tables

**1. `employees`** — Staff users (admin, ICT, HR)
| Column | Type | Notes |
|--------|------|-------|
| `id` | INT PK AUTO_INCREMENT | |
| `name` | VARCHAR(100) NOT NULL | |
| `department` | VARCHAR(50) | |
| `role` | VARCHAR(50) | admin, ict, hr |
| `email` | VARCHAR(150) UNIQUE NOT NULL | |
| `password` | VARCHAR(255) NOT NULL | bcrypt hashed |
| `created_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

**2. `applicants`** — Job applicants
| Column | Type | Notes |
|--------|------|-------|
| `id` | INT PK AUTO_INCREMENT | |
| `name` | VARCHAR(100) NOT NULL | |
| `email` | VARCHAR(150) UNIQUE NOT NULL | |
| `phone` | VARCHAR(20) | |
| `password` | VARCHAR(255) NOT NULL | bcrypt hashed |
| `resume_link` | VARCHAR(255) | |
| `position` | VARCHAR(150) | |
| `created_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

**3. `sessions`** — Interview sessions
| Column | Type | Notes |
|--------|------|-------|
| `id` | INT PK AUTO_INCREMENT | |
| `applicant_id` | INT NOT NULL | FK → applicants(id) ON DELETE CASCADE |
| `zoom_link` | VARCHAR(255) | |
| `status` | ENUM('pending','active','completed') | Default: pending |
| `scheduled_date` | DATETIME | |
| `created_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

**4. `scores`** — Interview scores
| Column | Type | Notes |
|--------|------|-------|
| `id` | INT PK AUTO_INCREMENT | |
| `session_id` | INT NOT NULL | FK → sessions(id) ON DELETE CASCADE |
| `applicant_name` | VARCHAR(100) NOT NULL | |
| `technical_skills` | INT DEFAULT 0 | Score 0–10 |
| `communication` | INT DEFAULT 0 | Score 0–10 |
| `confidence` | INT DEFAULT 0 | Score 0–10 |
| `work_ethic` | INT DEFAULT 0 | Score 0–10 |
| `team_fit` | INT DEFAULT 0 | Score 0–10 |
| `total` | INT DEFAULT 0 | Sum of all criteria |
| `submitted_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

### Default Admin Account

Created via `backend/seed-admin.php`:
- **Email:** admin@ncia.com
- **Password:** admin123

---

## Current Status

### Completed

| Component | Status | Details |
|-----------|--------|---------|
| Database schema | ✅ Done | 4 tables in `backend/database/schema.sql` |
| DB connection | ✅ Done | PDO class in `backend/config/database.php` |
| Login system | ✅ Done | Role-based auth, session handling, redirects |
| Registration | ✅ Done | Applicant signup with validation |
| Login page | ✅ Done | Wired to backend, error/success messages |
| Register page | ✅ Done | Wired to backend, error messages |
| Admin dashboard | ✅ Done | 4 cards: Create Session, Reports, Last Interview, Departments |
| ICT dashboard | ✅ Done | 2 cards: Manage Users, Departments |
| HR dashboard | ✅ Done | 4 cards: All Sessions, Reports, Manage Applicants, Score Applicants |
| Applicant dashboard | ✅ Done | 3 cards: Join Session, My Profile, Application Status |
| CSS design system | ✅ Done | 265 lines — blue/orange/white theme, cards, sidebar, topbar |

### Not Started

| Component | Priority |
|-----------|----------|
| `create-session.html` | High — core admin feature |
| `reports.html` | High — used by admin + HR |
| `manage-users.html` | High — ICT core feature |
| `hr-sessions.html` | Medium |
| `manage-applicants.html` | Medium |
| `score-applicants.html` | Medium |
| `departments.html` | Medium |
| `profile.html` | Low — applicant readonly |
| `application-status.html` | Low — applicant readonly |
| `interview-score.html` | Low — standalone scoring form |

---

## Setup Instructions

### Prerequisites
- XAMPP (Apache + MySQL running)
- PHP 7.4+
- MySQL 5.7+

### Database Setup
1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2. Import `backend/database/schema.sql`
3. Run `backend/seed-admin.php` to create the default admin account (`http://localhost/Interview-Process-Management/backend/seed-admin.php`)

### Access
- **Login:** `http://localhost/Interview-Process-Management/frontend/login.html`
- **Register:** `http://localhost/Interview-Process-Management/frontend/register.html`

---

## Build Order

| Phase | Status |
|-------|--------|
| 1. Database schema + DB config | ✅ Done |
| 2. Login system | ✅ Done |
| 3. Admin pages (sub-pages) | 🔲 Not started |
| 4. ICT pages | 🔲 Not started |
| 5. HR pages | 🔲 Not started |
| 6. Applicant sub-pages | 🔲 Not started |
| 7. Interview scoring page | 🔲 Not started |
