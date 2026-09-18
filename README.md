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
- **Server:** XAMPP Apache required (clean URLs via `.htaccess` and a single-entry PHP router)

---

## Project Structure

```
Interview-Process-Management/
├── index.php                              ← Main entry point / router (clean URLs)
├── .htaccess                              ← Apache rewrite rules (all requests → index.php)
│
├── frontend/
│   ├── style.css                          ← Shared styles
│   ├── login.php                          ← Login page (served via router)
│   ├── register.php                       ← Registration page (served via router)
│   ├── report.php                         ← Reports page (served via router)
│   ├── score-card.php                     ← Interview scoring form (served via router)
│   ├── sessions.php                       ← All sessions list (served via router)
│   ├── assets/
│   │   └── NCIA-Logo-2022.png
│   ├── admin-pages/
│   │   ├── admin-dashboard.php            ← Admin landing page
│   │   └── create-session.php             ← Create interview session form
│   ├── hr-pages/
│   │   └── hr-dashboard.php               ← HR landing page
│   ├── ict-pages/
│   │   └── ict-dashboard.php              ← ICT landing page
│   └── applicant-pages/
│       ├── applicant-dashboard.php        ← Applicant landing page
│       └── profile.php                    ← Applicant profile (not implemented)
│
├── backend/
│   ├── config/
│   │   ├── app.php                        ← Environment detection (isLocal, APP_BASE)
│   │   ├── database.php                   ← PDO connection class (reads .env on production)
│   │   ├── env.php                        ← Lightweight .env parser
│   │   ├── .env                           ← Production credentials (gitignored)
│   │   ├── .env.example                   ← Template for credentials
│   │   ├── schema.sql                     ← 4 tables: employees, applicants, sessions, scores
│   │   └── .htaccess                      ← Blocks web access to config folder
│   ├── database/
│   │   └── schema.sql                     ← (Alias — schema moved to config/)
│   ├── login-validation.php               ← Auth + role-based redirects
│   ├── register-validation.php            ← Applicant registration logic
│   ├── seed-admin.php                     ← Seeds default admin account
│   ├── create-session.php                 ← Backend for session creation
│   └── submit-score.php                   ← Backend for score submission
│
├── README.md
├── AGENTS.md                              ← Agent guide (not pushed to remote)
└── .gitignore
```

### Routing

All requests go through `index.php`. The `.htaccess` file rewrites every URL to the router, which maps clean paths to the correct PHP file:

| Route | PHP File | RBAC Gate |
|-------|----------|-----------|
| `/login` | `frontend/login.php` | Public |
| `/register` | `frontend/register.php` | Public |
| `/admin` | `frontend/admin-pages/admin-dashboard.php` | admin |
| `/admin/create-session` | `frontend/admin-pages/create-session.php` | admin |
| `/admin/sessions` | `frontend/sessions.php` | admin |
| `/admin/reports` | `frontend/report.php` | admin |
| `/admin/departments` | 404 (not implemented) | admin |
| `/hr` | `frontend/hr-pages/hr-dashboard.php` | hr |
| `/hr/sessions` | `frontend/sessions.php` | hr |
| `/hr/reports` | `frontend/report.php` | hr |
| `/hr/manage-applicants` | 404 (not implemented) | hr |
| `/ict` | `frontend/ict-pages/ict-dashboard.php` | ict |
| `/ict/manage-users` | 404 (not implemented) | ict |
| `/ict/departments` | 404 (not implemented) | ict |
| `/applicant` | `frontend/applicant-pages/applicant-dashboard.php` | applicant |
| `/applicant/profile` | `frontend/applicant-pages/profile.php` | applicant |
| `/score?session=X` | `frontend/score-card.php` | (public for now) |

Backend files (e.g., `backend/login-validation.php`) are accessed directly via form POST actions and handle their own session/redirects.

### Environment Handling

The application auto-detects the environment using `$_SERVER['HTTP_HOST']`:

| Environment | `APP_BASE` | Database Credentials |
|-------------|------------|----------------------|
| **Local (XAMPP)** | `/Interview-Process-Management` | Constructor fallbacks (localhost, root, empty password) |
| **Production (InfinityFree)** | *(empty)* | Loaded from `backend/config/.env` |

No manual switching required. The `.env` file is only used in production and is excluded from git.

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
| Database schema | ✅ Done | 4 tables in `backend/config/schema.sql` |
| DB connection | ✅ Done | PDO class in `backend/config/database.php` |
| PHP Router | ✅ Done | Single-entry `index.php` with `.htaccess` rewrite rules |
| Environment Detection | ✅ Done | `APP_BASE` auto-switches between local/production paths |
| `.env` Credential Management | ✅ Done | Production creds in `.env` (gitignored); local uses DB fallbacks |
| Login system | ✅ Done | Role-based auth, session handling, redirects to correct dashboards |
| Registration | ✅ Done | Applicant signup with email check, password hashing |
| Login page | ✅ Done | Wired to backend, JS for error/success messages |
| Register page | ✅ Done | Wired to backend, JS for error messages |
| Admin dashboard | ✅ Done | 4 cards: Create Session, Reports, Sessions, Departments |
| Create Session form | ✅ Done | PHP form — fetches applicants from DB, dropdown select, zoom link, datetime |
| Create Session backend | ✅ Done | Inserts into sessions table, redirects to sessions list |
| Submit Score backend | ✅ Done | Inserts/updates scores table, prevents duplicate submissions |
| Score Card page | ✅ Done | Fetches session details, displays criteria, submits scores |
| All pages use `APP_BASE` | ✅ Done | All assets, links, redirects work across environments |

### Not Started

| Component | Priority | Linked From |
|-----------|----------|-------------|
| `applicant/profile.php` | High | Applicant dashboard card |
| `reports.php` (full logic) | High | Admin + HR dashboards |
| `ict/manage-users.php` | High | ICT dashboard card |
| `admin/departments.php` | Medium | Admin + ICT dashboards |
| `hr/manage-applicants.php` | Medium | HR dashboard card |
| Applicant application status page | Low | Applicant dashboard card |

---

## Setup Instructions

### Prerequisites
- XAMPP (Apache + MySQL running)
- PHP 7.4+
- MySQL 5.7+

### Local Setup
1. Clone the repo into `C:\xampp\htdocs\Interview-Process-Management`
2. Open phpMyAdmin (`http://localhost/phpmyadmin`)
3. Import `backend/config/schema.sql`
4. Run the seeder: `http://localhost/Interview-Process-Management/backend/seed-admin.php`
5. Access the app via: `http://localhost/Interview-Process-Management/login`

### Production Setup (InfinityFree or similar)
1. Upload all files to your hosting account
2. Create the MySQL database and user via the hosting control panel
3. Edit `backend/config/.env` with your production credentials
4. Import `backend/config/schema.sql` via phpMyAdmin
5. Run `backend/seed-admin.php` once to create the admin account
6. The app auto-detects the environment — no further configuration needed

### Default Login
- **Email:** admin@ncia.com
- **Password:** admin123

---

## Build Order

| Phase | Status |
|-------|--------|
| 1. Database schema + DB config | ✅ Done |
| 2. Login system | ✅ Done |
| 3. PHP Router + Environment Detection | ✅ Done |
| 4. Admin pages (dashboard + create session form) | ✅ Done |
| 5. Admin sub-pages (reports, sessions) | ✅ Done |
| 6. HR pages (dashboard, sessions, reports) | ✅ Done |
| 7. Score card + Submit score backend | ✅ Done |
| 8. Applicant sub-pages (profile, application status) | 🔲 Not started |
| 9. ICT pages (manage users, departments) | 🔲 Not started |
| 10. HR manage-applicants page | 🔲 Not started |
| 11. Admin departments page | 🔲 Not started |
| 12. Full reports with export functionality | 🔲 Not started |
