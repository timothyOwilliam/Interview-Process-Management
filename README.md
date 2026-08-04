# Interview-Process-Management

## 4 Roles:
1. **Admin** — Coordinates everyone, creates sessions, creates Zoom links, assigns interviewers, views reports, exports to spreadsheet
2. **ICT** — Manages user accounts (create, edit, delete, assign roles)
3. **HR** — Views all sessions, views reports, schedules interviews, scores applicants, manages applicants
4. **Job Applicant** — Creates profile, applies for jobs, views application status only

## Key Flows:
1. **Applicant applies** → **Admin creates session** → **Interviewers score** → **Reports generated**
2. **Admin creates session**: pastes Zoom link → picks interviewers → system sends scoring links
3. **Interviewers assigned manually** by admin

## Pages needed:

### Admin Dashboard:
- View all sessions/reports
- Create session (with Zoom link, candidate info, select interviewers)
- Export reports to spreadsheet
- View last interview

### ICT Dashboard:
- Manage user accounts (CRUD)
- Assign roles

### HR Dashboard:
- View all sessions
- View reports
- Schedule interviews
- Score applicants
- Manage applicants

### Job Applicant Dashboard:
- View profile (readonly?)
- View application status

### Shared/Standalone pages:
- Interview scoring page (link-based, anonymous)
- Login pageNow let me design the full plan with the updated schema and page structure.

Now I have a complete picture. Here's the revised comprehensive plan:

---

## Revised System Architecture

### 4 User Roles

| Role | Dashboard Purpose |
|------|-------------------|
| **Admin** | Create sessions, Zoom links, assign interviewers, view reports, export to spreadsheet |
| **ICT** | Manage user accounts (CRUD), assign roles |
| **HR** | View all sessions, view reports, schedule interviews, score applicants, manage applicants |
| **Job Applicant** | View profile, view application status (readonly) |

### Flow
```
Applicant creates profile & applies
  → Admin creates interview session (pastes Zoom link, selects candidate & interviewers)
  → System generates scoring links for assigned interviewers
  → Interviewers join Zoom, fill assessment via scoring link
  → Once all submit → session marked completed
  → Admin/HR view reports, Admin can export to spreadsheet
```

---

## Database Schema (Revised — 7 tables)

**1. `departments`**
| Column | Type |
|--------|------|
| `id` | INT PK AUTO_INCREMENT |
| `name` | VARCHAR(100) |

**2. `users`**
| Column | Type |
|--------|------|
| `id` | INT PK AUTO_INCREMENT |
| `name` | VARCHAR(100) |
| `email` | VARCHAR(150) UNIQUE |
| `password` | VARCHAR(255) |
| `role` | ENUM('admin','ict','hr','applicant') |
| `department_id` | INT FK → departments.id |
| `created_at` | TIMESTAMP |

**3. `applicants`** (extended profile for job applicants)
| Column | Type |
|--------|------|
| `id` | INT PK AUTO_INCREMENT |
| `user_id` | INT FK → users.id |
| `phone` | VARCHAR(20) |
| `resume_link` | VARCHAR(255) |
| `position_applied` | VARCHAR(150) |
| `status` | ENUM('applied','reviewing','interviewed','hired','rejected') |

**4. `sessions`** (interview sessions created by admin)
| Column | Type |
|--------|------|
| `id` | INT PK AUTO_INCREMENT |
| `candidate_name` | VARCHAR(150) |
| `candidate_email` | VARCHAR(150) |
| `position` | VARCHAR(150) |
| `zoom_link` | VARCHAR(255) |
| `created_by` | INT FK → users.id |
| `department_id` | INT FK → departments.id |
| `status` | ENUM('pending','active','completed') |
| `scheduled_date` | DATETIME |
| `created_at` | TIMESTAMP |

**5. `session_interviewers`** (assigned interviewers per session)
| Column | Type |
|--------|------|
| `id` | INT PK AUTO_INCREMENT |
| `session_id` | INT FK → sessions.id |
| `user_id` | INT FK → users.id |
| `unique_token` | VARCHAR(64) UNIQUE |
| `has_submitted` | BOOLEAN DEFAULT FALSE |
| `created_at` | TIMESTAMP |

**6. `scoring_criteria`** (predefined — seeded)
| Column | Type |
|--------|------|
| `id` | INT PK AUTO_INCREMENT |
| `name` | VARCHAR(100) |
| `max_score` | INT DEFAULT 10 |

**7. `scores`** (submitted scores)
| Column | Type |
|--------|------|
| `id` | INT PK AUTO_INCREMENT |
| `session_interviewer_id` | INT FK → session_interviewers.id |
| `criteria_id` | INT FK → scoring_criteria.id |
| `score` | INT |
| `submitted_at` | TIMESTAMP |

---

## Pages to Build

### Login
| File | Purpose |
|------|---------|
| `login.html` | **Redesign** — role-aware (redirects to correct dashboard after login) |

### Admin Dashboard
| File | Purpose |
|------|---------|
| `admin-dashboard.html` | Cards: Create Session, View Reports, Departments |
| `create-session.html` | Form: candidate info, Zoom link, select interviewers, send scoring links |
| `admin-reports.html` | Table of all sessions/reports + **Export to Spreadsheet** button |

### ICT Dashboard
| File | Purpose |
|------|---------|
| `ict-dashboard.html` | Cards: Manage Users, Departments |
| `manage-users.html` | Table of all users + Add/Edit/Delete user forms |

### HR Dashboard
| File | Purpose |
|------|---------|
| `hr-dashboard.html` | Cards: All Sessions, Reports, Manage Applicants, Score Applicants |
| `hr-sessions.html` | Table of all sessions with status |
| `hr-reports.html` | View reports (read-only) |
| `manage-applicants.html` | Table of applicants + status management |

### Applicant Dashboard
| File | Purpose |
|------|---------|
| `applicant-dashboard.html` | Profile view (readonly) + application status |

### Standalone Pages
| File | Purpose |
|------|---------|
| `interview-score.html` | Anonymous scoring form (accessed via unique token link) |

---

## Files to Create

### Backend
1. `backend/database/schema.sql` — Full schema + seed data
2. `backend/config/database.php` — PDO connection
3. `backend/login-validation.php` — Auth logic

### Frontend (HTML — you handle CSS)
4. `login.html` — Redesign for role-based redirect
5. `admin-dashboard.html` — Redesign for new cards
6. `create-session.html` — Session creation form
7. `admin-reports.html` — Reports table + export
8. `ict-dashboard.html` — ICT landing page
9. `manage-users.html` — User CRUD table
10. `hr-dashboard.html` — HR landing page
11. `hr-sessions.html` — All sessions table
12. `hr-reports.html` — Reports view
13. `manage-applicants.html` — Applicant management
14. `applicant-dashboard.html` — Applicant profile view
15. `interview-score.html` — Anonymous scoring form

---

### Build Order
| Phase | What | Why |
|-------|------|-----|
| **1** | Database schema + DB config | Foundation for everything |
| **2** | Login system (redesign) | Auth gate for all roles |
| **3** | Admin pages (dashboard, create session, reports) | Core functionality |
| **4** | ICT pages (manage users) | User management needed before HR/Applicant flows |
| **5** | HR pages (sessions, reports, applicants) | Depends on sessions existing |
| **6** | Applicant dashboard | Simplest — mostly readonly |
| **7** | Interview scoring page | Standalone, token-based |

That's **15 files** total (3 backend + 12 frontend). Would you like to adjust the scope or proceed with this plan?