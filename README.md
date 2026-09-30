# Interview Process Management

A web-based system for managing interview sessions, scoring applicants, and tracking recruitment workflows. Built with vanilla PHP (XAMPP — Apache + MySQL) and vanilla HTML/CSS — no frameworks, no JS libraries, no Composer.

## Table of Contents

- [System Overview](#system-overview)
- [Tech Stack](#tech-stack)
- [Architecture & Key Concepts](#architecture--key-concepts)
- [Project Structure](#project-structure)
- [Routing Map](#routing-map)
- [File-by-File Documentation](#file-by-file-documentation)
- [Database Schema](#database-schema)
- [Implemented Features](#implemented-features)
- [Setup Instructions](#setup-instructions)
- [Deployment (CI/CD Notes)](#deployment-cicd-notes)
- [Security Notes](#security-notes)
- [Known Limitations / Gotchas](#known-limitations--gotchas)
- [Build Status & Roadmap](#build-status--roadmap)

---

## System Overview

### User Roles

| Role | Responsibilities |
|------|------------------|
| **Admin** | Create sessions, paste Zoom links, select candidates, view sessions list, view/export reports |
| **ICT** | Manage employee accounts (CRUD), view/delete applicants, view departments |
| **HR** | View all sessions, view reports, score applicants via score card |
| **Job Applicant** | Register, view dashboard (join session link), view/update profile |

### Core Flow

```
Applicant registers (frontend/register.php → backend/register-validation.php)
  → Admin creates interview session (frontend/admin-pages/create-session.php
      → backend/create-session.php)
  → backend/create-session.php inserts into `sessions`
      AND pre-creates a zeroed row in `scores` for that session
  → HR opens sessions list (frontend/sessions.php)
      → clicks Join → frontend/score-card.php?session_id=X
  → HR submits scores (→ backend/submit-score.php)
      → UPDATEs the pre-created `scores` row by adding new values
  → Admin/HR view frontend/report.php, optionally export CSV (?export=csv)
  → ICT manages employees/applicants via frontend/ict-pages/*
```

---

## Tech Stack

- **Backend:** Vanilla PHP 7.4+ (PDO, `$_SESSION`, `password_hash`/`password_verify`)
- **Frontend:** Vanilla HTML/CSS + tiny inline vanilla JS (URL-param messages, sidebar toggle) — no frameworks
- **Database:** MySQL 5.7+ / 8.0. Local XAMPP uses InnoDB + foreign keys. InfinityFree free tier only supports MyISAM (no FKs) — schema import must account for this (see Database Schema).
- **Server:** Apache required. Clean URLs via root `.htaccess` → single-entry PHP router (`index.php`).
- **Timezone:** `Africa/Nairobi` set via `php_value date.timezone` in root `.htaccess`.

---

## Architecture & Key Concepts

### 1. Single-entry PHP router (`index.php`)

All non-file, non-directory requests are rewritten to `index.php` (see root `.htaccess`). The router:

1. Strips the query string from `REQUEST_URI`.
2. Strips the `dirname(SCRIPT_NAME)` base folder (so `/Interview-Process-Management/login` → `/login` locally).
3. Normalises to `/` + trimmed path.
4. Looks up a `$routes` table mapping clean path → physical frontend PHP file.
5. Enforces access control: public routes (`/`, `/login`, `/register`) need no session; every other route requires `$_SESSION['user_id']`, plus a role-prefix check (`/admin*` → `admin`, `/hr*` → `hr`, `/ict*` → `ict`, `/applicant*` → `applicant`). Failures redirect to `/login?error=...` via `redirectTo()` (which prepends `APP_BASE`).
6. `require_once`s the target file (`index.php` already called `session_start()`, so routed frontend files must NOT call it again), or returns 500 (route mapped but file missing) / 404 (no route).

### 2. Environment handling (`backend/config/app.php` + `database.php`)

`backend/config/app.php` detects the host:

```php
$isLocal = (strpos($host,'localhost')!==false || strpos($host,'127.0.0.1')!==false);
define('APP_BASE', $isLocal ? '/Interview-Process-Management' : '');
```

| Environment | `APP_BASE` | DB credentials source |
|-------------|------------|----------------------|
| Local XAMPP (`localhost`) | `/Interview-Process-Management` | Constructor fallbacks in `Database::__construct()` (`localhost` / `interview_management_db` / `root` / `''`) |
| Production (e.g. InfinityFree) | `''` (app at domain root) | `backend/config/.env` loaded via `loadEnv()` — only when `!$isLocal` |

No manual switching. `.env` is gitignored and only exists on the server / local clone that needs it.

All asset URLs, nav links, form actions, and `header("Location: ...")` redirects use `<?= APP_BASE ?>` / `APP_BASE . "..."` so they resolve in both environments.

### 3. Backend-direct vs router-loaded files

- **Router-loaded (frontend):** `frontend/*.php`, `frontend/*-pages/*.php`. Loaded through `index.php`; session already started; use `$_SESSION` directly.
- **Backend-direct:** `backend/login-validation.php`, `register-validation.php`, `create-session.php`, `submit-score.php`, `create-user.php`, `edit-user.php`, `delete-user.php`, `delete-applicant.php`, `update-profile.php`, `seed-*.php`. Hit directly by form actions / links (e.g. `<?= APP_BASE ?>/backend/submit-score.php`). Each calls `session_start()` itself and ends with a `header("Location: " . APP_BASE . "...")` redirect.

### 4. Database layer

`backend/config/database.php` exposes class `Database` with `getConnection(): PDO`. Uses `mysql:host=...;dbname=...`, sets `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, echoes `Connection Error: ...` on failure. All queries use prepared statements with bound params.

`backend/config/env.php` provides `loadEnv($path)`: skips missing files, comments, lines without `=`, splits on first `=`, trims quotes/whitespace, sets `$_ENV` + `putenv()`.

---

## Project Structure

```
Interview-Process-Management/
├── index.php                        ← single-entry router + RBAC gate
├── .htaccess                        ← block .env, rewrite all → index.php, Nairobi TZ
├── README.md
├── AGENTS.md                        ← agent guide (gitignored, not pushed)
├── .gitignore
│
├── frontend/
│   ├── style.css                    ← 796 lines: variables, login, sidebar/cards,
│   │                                   tables, page-header/export-btn, hamburger,
│   │                                   responsive 1024px + 768px, applicant mobile-first
│   ├── login.php                    ← login form → POST /backend/login-validation.php
│   ├── register.php                 ← applicant signup → POST /backend/register-validation.php
│   ├── sessions.php                 ← sessions+applicants JOIN table, Join → /score-card?session_id=
│   ├── report.php                   ← scores report table + ?export=csv download
│   ├── score-card.php               ← applicant details + 5-criteria scoring form
│   │                                   → POST /backend/submit-score.php
│   ├── assets/
│   │   └── NCIA-Logo-2022.png
│   ├── admin-pages/
│   │   ├── admin-dashboard.php
│   │   └── create-session.php       ← applicant dropdown + zoom + datetime-local
│   │                                   → POST /backend/create-session.php
│   ├── hr-pages/
│   │   └── hr-dashboard.php
│   ├── ict-pages/
│   │   ├── ict-dashboard.php
│   │   ├── manage-users.php         ← list/add/edit employees (?action=list|add|edit)
│   │   ├── manage-applicants.php    ← list/delete applicants
│   │   └── departments.php          ← GROUP BY department counts
│   └── applicant-pages/
│       ├── applicant-dashboard.php  ← join-session card + profile/status cards
│       └── profile.php              ← readonly name/email + editable phone/resume/position
│                                       → POST /backend/update-profile.php
│
└── backend/
    ├── login-validation.php         ← employees-then-applicants auth + role redirects
    ├── register-validation.php      ← applicant signup + duplicate-email check
    ├── create-session.php           ← insert session + pre-create zeroed scores row
    ├── submit-score.php             ← atomic UPDATE scores SET col=col+:val ...
    ├── create-user.php              ← ICT: insert employee (role whitelist, dup check)
    ├── edit-user.php                ← ICT: update employee, optional password change
    ├── delete-user.php              ← ICT: GET delete employee, blocks self-delete
    ├── delete-applicant.php         ← ICT: GET delete applicant (toggles FK checks)
    ├── update-profile.php           ← applicant: update phone/resume_link/position
    ├── seed-admin.php               ← admin@ncia.com / admin123
    ├── seed-hr.php                  ← hr@ncia.com / hr123
    ├── seed-ict.php                 ← ict@ncia.com / ict123
    └── config/
        ├── app.php                  ← $isLocal + APP_BASE
        ├── env.php                  ← loadEnv()
        ├── database.php             ← Database class (conditional .env load)
        ├── schema.sql               ← 4 tables (InnoDB + FKs, local dev)
        ├── .env                     ← production creds (gitignored, server only)
        ├── .env.example             ← template
        └── .htaccess                ← Require all denied
```

Note: `backend/database/schema.sql` referenced in older docs no longer exists; canonical schema is `backend/config/schema.sql`.

---

## Routing Map

Actual `$routes` table in `index.php` (authoritative):

| Route | Physical file | Role gate |
|-------|---------------|-----------|
| `/`, `/login` | `frontend/login.php` | Public |
| `/register` | `frontend/register.php` | Public |
| `/admin` | `frontend/admin-pages/admin-dashboard.php` | admin |
| `/admin/create-session` | `frontend/admin-pages/create-session.php` | admin |
| `/sessions` | `frontend/sessions.php` | any logged-in user |
| `/hr/sessions` | `frontend/sessions.php` | hr (same file as above) |
| `/reports` | `frontend/report.php` | any logged-in user |
| `/score-card` (+ `?session_id=X`) | `frontend/score-card.php` | any logged-in user (no role check beyond login) |
| `/hr` | `frontend/hr-pages/hr-dashboard.php` | hr |
| `/ict` | `frontend/ict-pages/ict-dashboard.php` | ict |
| `/ict/manage-users` (+ `?action=`, `?id=`) | `frontend/ict-pages/manage-users.php` | ict |
| `/ict/manage-applicants` | `frontend/ict-pages/manage-applicants.php` | ict |
| `/ict/departments` | `frontend/ict-pages/departments.php` | ict |
| `/applicant` | `frontend/applicant-pages/applicant-dashboard.php` | applicant |
| `/applicant/profile` | `frontend/applicant-pages/profile.php` | applicant |

Linked-but-unrouted (→ 404): `/admin/manage-applicants` (linked from admin sidebar), `/admin/departments` (linked from admin card), `/hr/manage-applicants`, `/hr/score-applicants` (linked from HR sidebar/cards), `/applicant/application-status` (linked from applicant sidebar + card). See Known Limitations.

Backend endpoints (direct POST/GET, NOT routed): `/backend/login-validation.php`, `/backend/register-validation.php`, `/backend/create-session.php`, `/backend/submit-score.php`, `/backend/create-user.php`, `/backend/edit-user.php`, `/backend/delete-user.php?id=`, `/backend/delete-applicant.php?id=`, `/backend/update-profile.php`, `/backend/seed-*.php`.

---

## File-by-File Documentation

### Root

**`index.php` (108 lines)** — Router. Requires `backend/config/app.php`, calls `session_start()`. Strips query string and `dirname(SCRIPT_NAME)` base folder, normalises path. `$routes` table (see above). `redirectTo($path)` = `header("Location: " . APP_BASE . $path)`. Public routes bypass auth; others require `$_SESSION['user_id']` + role-prefix match. Loads target via `require_once`, else 500/404 HTML.

**`.htaccess` (19 lines)** — Denies `.env` over HTTP, `RewriteEngine On`, skips real files/dirs, routes everything else to `index.php`, sets `date.timezone Africa/Nairobi`.

### `backend/config/`

**`app.php` (9 lines)** — Reads `$_SERVER['HTTP_HOST']`, sets `$isLocal`, defines `APP_BASE`.

**`env.php` (36 lines)** — `loadEnv($path)`: silent no-op if file missing; parses `KEY=VALUE`, supports `=` in values, trims quotes.

**`database.php` (51 lines)** — Loads `env.php` + `app.php`; calls `loadEnv(.env)` only if `!$isLocal`. `Database` class: private host/db/user/pass/conn; constructor falls back to localhost XAMPP creds; `getConnection()` returns PDO with exceptions enabled.

**`schema.sql` (95 lines)** — Drops/creates `applicants → employees → sessions → scores` inside a transaction with `FOREIGN_KEY_CHECKS=0`. All `ENGINE=InnoDB`, `utf8mb4`. Unique keys on `applicants.email`, `applicants.name`, `employees.email`. FKs: `sessions(applicant_id)→applicants(id)`; `scores(session_id)→sessions(id)`, `scores(applicant_id)→applicants(id)`, and unusually `scores(applicant_name)→applicants(name)` — all `ON DELETE CASCADE`.

**`.htaccess` (2 lines)** — `Require all denied` for the config dir.

**`.env` / `.env.example`** — `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`. Real file gitignored.

### `backend/` auth & sessions

**`login-validation.php` (97 lines)** — POST `email`/`password` required. Queries `employees` first: on `password_verify` success sets `user_id/name/email/department/role` session vars and `switch(role)` → `/admin`, `/ict`, `/hr`, else `Unknown role`. Falls through to `applicants` table (sets role `applicant` → `/applicant`). Otherwise → `/login?error=Invalid+email+or+password`.

**`register-validation.php` (66 lines)** — POST `full_name/email/phone/password/confirm_password` all required; passwords must match; `SELECT applicants WHERE email` duplicate check; `password_hash()`; `INSERT applicants(name,email,phone,password)` → `/login?success=...`, else error back to `/register`.

**`create-session.php` (122 lines)** — POST `applicant_id` (int), `zoom_link`, `date_and_time` (`datetime-local` `Y-m-d\TH:i` validated then formatted to MySQL `Y-m-d H:i:s`). Looks up applicant name, `INSERT sessions(applicant_id,zoom_link,scheduled_date)`, `lastInsertId()`, re-joins to get `applicant_id/name`, calls `createRowForScoreSheet()` which `INSERT`s a zeroed `scores(session_id,applicant_id,applicant_name,0,0,0,0,0,0)` row. Success → `/admin/create-session?success=1`. Note: `$clean_date_and_time` is only set when parsing succeeds (no else/error path — see Gotchas).

**`submit-score.php` (60 lines)** — POST `session_id` + 5 criteria (int-cast, 0 default). Computes `$total`. Runs single atomic `UPDATE scores SET col=col+:val, ... total=total+:total WHERE session_id=:session_id` (accumulates — safe for concurrent scorers). Sets `$_SESSION["form_submitted"]=true`. Redirects to `/score-card?session_id=X&success=...`. Non-POST → `/score-card`. Requires the zeroed row from `create-session.php` to exist (UPDATE-only, no INSERT fallback).

### `backend/` user & profile management

**`create-user.php` (63 lines)** — ICT add-employee. Requires name/email/department/role/password; role whitelist `admin|ict|hr`; duplicate-email check in BOTH `employees` and `applicants`; hashes password; INSERT → `/ict/manage-users?success=1`.

**`edit-user.php` (63 lines)** — ICT edit-employee. Validates required fields + role whitelist; email-uniqueness check excluding current id; two UPDATE paths (with new hashed password, or without); → success list or error back to edit form.

**`delete-user.php` (39 lines)** — GET `?id=` only; requires `ict` role; blocks deleting own `user_id`; `DELETE employees WHERE id` → back to list.

**`delete-applicant.php` (38 lines)** — GET `?id=` only; requires `ict` role; wraps `DELETE applicants WHERE id` with `SET FOREIGN_KEY_CHECKS=0/1` → back to `/ict/manage-applicants`.

**`update-profile.php` (56 lines)** — Requires applicant session; POST `phone/resume_link/position` (at least one non-empty); `UPDATE applicants SET phone,resume_link,position WHERE id=:id` with try/catch + `error_log()` → `/applicant/profile?success|error=...`.

**`seed-admin.php` / `seed-hr.php` / `seed-ict.php` (27 lines each)** — Direct-run scripts inserting one `employees` row with `password_hash()`: `Admin/admin@ncia.com/admin123` (dept ICT), `HR Staff/hr@ncia.com/hr123`, `ICT User/ict@ncia.com/ict123`. No duplicate guard — re-running duplicates/errors on unique email.

### `frontend/` top-level

**`login.php` (58 lines)** — Posts to `/backend/login-validation.php`. Hidden `#error-message` / `#success-message` divs populated by inline JS reading `?error=` / `?success=` via `URLSearchParams`. Link to `/register`.

**`register.php` (60 lines)** — Fields full_name/email/phone/password/confirm_password. Posts to `/backend/register-validation.php`. Same JS error-display pattern. Link to `/login`.

**`sessions.php` (84 lines)** — Requires `database.php`; `SELECT sessions JOIN applicants ORDER BY scheduled_date DESC`. Back-link `/{user_role}`. Empty-state message. Table: Applicant, Position, Resume (link or N/A), Scheduled Date, Action (Join → `/score-card?session_id=` if zoom_link else "No link"). All output `htmlspecialchars()`-escaped.

**`report.php` (122 lines)** — `SELECT scores JOIN applicants JOIN sessions`. If `?export=csv`: sends `text/csv` + `interview-reports.csv` attachment with UTF-8 BOM, custom `$escapeField` closure (quotes fields containing `,"` or newlines), header row + one row per score, `exit()`. Otherwise renders table (Name, Position, 5 criteria, Total, Submitted At). Back-link `/{role}`. Export link is relative `?export=csv`.

**`score-card.php` (166 lines)** — Requires `database.php`. Reads `?success=`/`?error=` for banner. Requires numeric `?session_id=` else → `/hr?error=No+session+selected`; `JOIN sessions+applicants WHERE sessions.id`; false → `/hr?error=Session+not+found`. Renders applicant-details table (name/email/phone/position/resume/scheduled/zoom). Form posts to `/backend/submit-score.php` with hidden `session_id`/`applicant_name`, five `number min=1 max=5 required` inputs, submit button. Back-link is `/sessions` (not role dashboard).

### `frontend/admin-pages/`

**`admin-dashboard.php` (66 lines)** — Sidebar: Dashboard, Create Session, Manage Applicants (`/admin/manage-applicants` — unrouted). Cards: Create Session → `/admin/create-session`; Reports → `/reports`; Sessions → `/sessions`; Manage Employees → `/admin/departments` (unrouted). Static `Welcome, Admin` heading. Hamburger + overlay JS.

**`create-session.php` (101 lines)** — Shows `$_SESSION["errors"]` if set. On no-errors path loads DB, reads `?success=`/`?error=`, `SELECT id,name FROM applicants`; if zero rows it bare-`exit()`s (blank page — see Gotchas). Renders `<select name=applicant_id>` of applicants, `url` zoom input, `datetime-local` input → POST `/backend/create-session.php`. Back-link `/admin`.

### `frontend/hr-pages/`

**`hr-dashboard.php` (65 lines)** — Sidebar: Dashboard, All Sessions (`/hr/sessions`), Reports (`/reports`), Manage Applicants (`/hr/manage-applicants` — unrouted), Score Applicants (`/hr/score-applicants` — unrouted). Cards: All Sessions → `/sessions`; Reports → `/reports`; Manage Applicants → `/hr/manage-applicants` (unrouted). Heading `Welcome {user_name}`. Hamburger JS.

### `frontend/ict-pages/`

**`ict-dashboard.php` (56 lines)** — Sidebar: Dashboard, Manage Users, Departments. Cards: Manage Employees → `/ict/manage-users`; Manage Applicants → `/ict/manage-applicants` (exists but not in sidebar). Heading uses `$_SESSION["user_name"]`.

**`manage-users.php` (140 lines)** — Modes via `?action=list|add|edit` (+ `?id=`). Edit mode pre-fetches `employees WHERE id` else redirects `User not found`. Always fetches all employees `ORDER BY name`. Form posts to `/backend/create-user.php` (add) or `/backend/edit-user.php` (edit, hidden id); role `<select>` admin/ict/hr; password required on add, optional on edit. Table with Edit link (`?action=edit&id=`) and Delete link (`/backend/delete-user.php?id=` + JS confirm).

**`manage-applicants.php` (85 lines)** — Lists all applicants `ORDER BY name` (name/email/phone/position/date). Delete link → `/backend/delete-applicant.php?id=` with confirm warning about cascading sessions/scores.

**`departments.php` (65 lines)** — `SELECT department, COUNT(*) GROUP BY department` (non-empty only). Simple two-column table; empty-state if none.

### `frontend/applicant-pages/`

**`applicant-dashboard.php` (90 lines)** — Guards applicant session. `SELECT zoom_link FROM sessions WHERE applicant_id=:id` (first row only). `card-full` Join Session: external zoom link + Join Now, or disabled Waiting button. Cards to `/applicant/profile` and `/applicant/application-status` (latter unrouted). Sidebar mirrors same three links.

**`profile.php` (106 lines)** — Guards applicant session (also re-requires `app.php` for `APP_BASE`). Fetches own `name/email/phone/resume_link/position`. Readonly name/email; editable phone (`tel`), resume (`url`), position. Posts to `/backend/update-profile.php`. Shows `?success=`/`?error=` banner.

### `frontend/style.css` (796 lines)

CSS variables (`--primary-color` white, `--secondary-color` blue, `--third-color` orange). Sections: login pages, topbar, dashboard layout/sidebar/nav/cards, sub-page `.page-content` + `.back-link`, `.data-table` + `.table-wrapper`, `.page-header` + `.export-btn`, form messages, hamburger/overlay, `@media 1024px` (narrower sidebar/cards), `@media 768px` (off-canvas sidebar, single-column cards, scrollable tables, full-width buttons, 16px inputs anti-iOS-zoom, applicant mobile-first block).

---

## Database Schema

**Database name (local):** `interview_management_db`. Source: `backend/config/schema.sql`.

**1. `employees`** — staff (admin, ict, hr): `id` PK AI, `name` VARCHAR(100) NOT NULL, `department` VARCHAR(50) NULL, `role` VARCHAR(50) NULL (`admin|ict|hr`), `email` VARCHAR(150) UNIQUE NOT NULL, `password` VARCHAR(255) bcrypt, `created_at` TIMESTAMP default current_timestamp.

**2. `applicants`** — candidates: `id` PK AI, `name` VARCHAR(100) NOT NULL, `email` VARCHAR(150) UNIQUE NOT NULL, `phone` VARCHAR(20) NULL, `password` VARCHAR(255) bcrypt, `resume_link` VARCHAR(255) NULL, `position` VARCHAR(150) NULL, `created_at` TIMESTAMP. Extra `UNIQUE(name)` (`unique_name`).

**3. `sessions`** — interviews: `id` PK AI, `applicant_id` INT NOT NULL + index, `zoom_link` VARCHAR(255) NULL, `scheduled_date` DATETIME NULL, `created_at` TIMESTAMP. FK `sessions_ibfk_1`: `applicant_id → applicants(id) ON DELETE CASCADE`.

**4. `scores`** — one row per session (pre-created at session creation): `id` PK AI, `session_id` INT NOT NULL + index, `applicant_id` INT NOT NULL, `applicant_name` VARCHAR(100) NOT NULL, five INT criteria DEFAULT 0 (`technical_skills, communication, confidence, work_ethic, team_fit`), `total` INT DEFAULT 0, `submitted_at` TIMESTAMP. FKs: `scores_ibfk_1` (`session_id → sessions(id)`), `applicant_id_fk` (`applicant_id → applicants(id)`), `applicant_name_fk` (`applicant_name → applicants(name)`) — all CASCADE.

**Production note (InfinityFree):** free tier is MyISAM-only — `ENGINE=InnoDB` and all `FOREIGN KEY` constraints will fail/be ignored there. For production import you need a MyISAM variant of the schema with FK clauses removed (cascade deletes then rely on app code / `delete-applicant.php`'s FK-toggle pattern). `current_timestamp()` syntax requires MySQL 8.0-compatible server (InfinityFree provides 8.0/MariaDB 11.4).

**Default accounts** (via `backend/seed-*.php`): `admin@ncia.com/admin123`, `hr@ncia.com/hr123`, `ict@ncia.com/ict123`.

---

## Implemented Features

- Clean-URL router with RBAC gate (`index.php` + `.htaccess`)
- Local/production auto-config (`APP_BASE`, conditional `.env`)
- Auth: applicant registration + employees/applicants dual-table login, bcrypt, session + role redirects
- Admin: dashboard, create-session form + backend (with zeroed scores-row init), sessions list
- HR: dashboard, sessions list, score card + atomic accumulating score submit
- Reports: HTML table + BOM-safe CSV export
- ICT: employee list/add/edit/delete, applicant list/delete, department counts
- Applicant: dashboard with join-link card, profile view/update
- Responsive CSS (tablet + mobile, applicant mobile-first)
- Seed scripts for all three staff roles

---

## Setup Instructions

### Prerequisites

- XAMPP (Apache + MySQL running), PHP 7.4+, MySQL 5.7+
- Apache must serve the project so `.htaccess` + `index.php` routing works (`file://` will not work)

### Local setup

1. Clone/copy repo to `C:\xampp\htdocs\Interview-Process-Management`
2. Start Apache + MySQL in XAMPP
3. phpMyAdmin (`http://localhost/phpmyadmin`) → create DB `interview_management_db` (or let import create it) → Import `backend/config/schema.sql`
4. Seed staff: visit `http://localhost/Interview-Process-Management/backend/seed-admin.php` (repeat for `seed-hr.php`, `seed-ict.php` as needed)
5. App entry: `http://localhost/Interview-Process-Management/login` (root `/` also routes to login)

### Production setup (InfinityFree or similar)

1. Upload all files (FTP) so `index.php`/`.htaccess` sit at the domain docroot (`/htdocs/`)
2. Create MySQL DB + user in hosting panel; note host/db/user/pass
3. Create `backend/config/.env` on the server (never commit):
   ```
   DB_HOST=...
   DB_NAME=...
   DB_USER=...
   DB_PASS=...
   ```
4. Import a **MyISAM-compatible** variant of `backend/config/schema.sql` (strip `ENGINE=InnoDB` → `ENGINE=MyISAM`, remove `CONSTRAINT ... FOREIGN KEY` clauses) via hosting phpMyAdmin
5. Seed admin once (run seeder against production DB or insert equivalent row manually)
6. No code changes needed — `APP_BASE` becomes `''` automatically off-localhost

### Default logins

- Admin: `admin@ncia.com` / `admin123`
- HR: `hr@ncia.com` / `hr123`
- ICT: `ict@ncia.com` / `ict123`

---

## Deployment (CI/CD Notes)

InfinityFree has **no SSH / git-push deploy — FTP only** (`ftpupload.net:21`, user `epiz_XXXXXXXX`, server dir `/htdocs/`). The endorsed path is GitHub Actions + `SamKirkland/FTP-Deploy-Action` on push to `main`, excluding `.git*`, `node_modules/`, `.env`, `AGENTS.md`, `.github/`. Store FTP creds as repo secrets (`FTP_USERNAME`, `FTP_PASSWORD`). Schema changes and server `.env` remain manual (phpMyAdmin + FTP/file-manager), as does admin seeding.

---

## Security Notes

Good: PDO prepared statements everywhere, `password_hash`/`verify`, `htmlspecialchars()` on output, unique-email checks, role gates in router + per-script guards, config dir denied, `.env` blocked + gitignored.
To add: CSRF tokens on all POST forms, GET→POST for destructive deletes, central error handler (never echo PDO errors to users), security headers (CSP/X-Frame/X-Content-Type), rate-limiting login, stricter server-side validation (ranges/patterns/lengths).

---

## Known Limitations / Gotchas

- **Score button always disabled:** `score-card.php:157` uses `($_SESSION["form_submitted"]=true)` (assignment, always truthy) instead of comparison — button renders `disabled` on first load. `$_SESSION["form_submitted"]` is also set unconditionally in `submit-score.php` (global per user, not per session).
- **Score flow depends on pre-created row:** `submit-score.php` is UPDATE-only; scores for a session only work because `create-session.php` pre-inserts the zeroed row.
- **Unusual FK:** `scores.applicant_name → applicants(name)` couples scores to a mutable non-PK column; renaming an applicant breaks/ cascades unexpectedly. Prefer joining by id only.
- **Create-session edge cases:** invalid/missing `date_and_time` leaves `$clean_date_and_time` undefined (PHP warning + NULL insert); zero applicants causes bare `exit()` (blank page); `<option value=" id ">` has surrounding spaces (works only because `intval()` trims it).
- **Score-card redirects assume HR:** missing/invalid `session_id` redirects to `/hr` even for admin users.
- **404 links present in UI:** admin sidebar `/admin/manage-applicants`, admin card `/admin/departments`, HR sidebar/cards `/hr/manage-applicants` + `/hr/score-applicants`, applicant `/applicant/application-status` have no route.
- **Destructive GETs:** delete-user/delete-applicant run on GET (CSRF-able); applicant-delete toggles global `FOREIGN_KEY_CHECKS`.
- **Seeders have no idempotency:** re-running inserts duplicate (fails on unique email).
- **No CSRF, no rate limiting, no central logger** (see Security Notes).
- **DB portability:** local schema is InnoDB+FK; InfinityFree needs MyISAM variant (see Database Schema).

---

## Build Status & Roadmap

| Phase | Status |
|-------|--------|
| 1. Database schema + DB config | ✅ Done |
| 2. Login system | ✅ Done |
| 3. PHP Router + Environment Detection | ✅ Done |
| 4. Admin pages (dashboard + create session form + backend) | ✅ Done |
| 5. Admin/HR sessions list + reports (+CSV) | ✅ Done |
| 6. HR dashboard + score card + submit-score backend | ✅ Done |
| 7. ICT pages (dashboard, manage users/applicants, departments) + user CRUD backends | ✅ Done |
| 8. Applicant dashboard + profile + update-profile backend | ✅ Done |
| 9. Seeders for admin/HR/ICT | ✅ Done |
| 10. Fix score-button disabled bug + per-session submitted state | 🔲 Next |
| 11. Implement missing routes (admin manage-applicants/departments, HR manage-applicants/score-applicants, applicant application-status) | 🔲 Not started |
| 12. Harden validation + CSRF + POST-only deletes + central error handling | 🔲 Not started |
| 13. MyISAM production schema variant + deploy workflow (GitHub Actions → FTP) | 🔲 Not started |
| 14. Full reports filters/aggregates + polish | 🔲 Not started |
