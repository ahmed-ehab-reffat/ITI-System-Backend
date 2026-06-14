# ITI System — Backend API

RESTful API for the ITI Attendance & Grading Platform. Built with Laravel 13, PHP 8.3, and Sanctum token authentication.

## Overview

This API powers the full operational loop of an ITI training branch: managing tracks, cohorts, courses, lab groups, engagements, sessions, attendance (with QR check-in), grading, excuse workflows, submissions, billing, analytics, and role-based access control across four roles (Branch Manager, Track Admin, Instructor, Student).

## Tech Stack

- **PHP** 8.3+
- **Laravel** 13.x
- **Laravel Sanctum** 4.x (token auth)
- **SQLite** (default) / MySQL / PostgreSQL
- **AWS S3** (file storage via Flysystem)
- **PHPUnit** 12.x

## Requirements

- PHP 8.3+
- Composer
- Node.js & npm (for frontend dev server / Vite)
- SQLite (default) or MySQL/PostgreSQL

## Quick Setup

```bash
# Clone the repo
git clone <repo-url>
cd ITI-System-Backend

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Create SQLite database (if using SQLite)
touch database/database.sqlite

# Run migrations + seeders
php artisan migrate --seed

# Start the dev server
php artisan serve
```

The API runs at `http://localhost:8000/api` by default.

### Composer Scripts

| Script | Description |
|--------|-------------|
| `composer setup` | Full setup: install, env, key, migrate, npm build |
| `composer dev` | Run server, queue, logs, and Vite concurrently |
| `composer test` | Clear config and run PHPUnit |

## Authentication

Token-based auth via **Laravel Sanctum**. No public registration — all accounts are provisioned top-down.

**Login:**
```
POST /api/auth/login
Content-Type: application/json

{ "email": "user@iti.test", "password": "password" }

Response: { "token": "1\|...", "user": { ... } }
```

**Authenticated requests:**
```
Authorization: Bearer <token>
```

**Logout:**
```
POST /api/auth/logout
Authorization: Bearer <token>
```

### Middleware

| Middleware | Scope | Purpose |
|-----------|-------|---------|
| `auth:sanctum` | All authenticated routes | Validates bearer token |
| `account.active` | All authenticated routes | Rejects expired accounts (403) |
| `SetLocale` | Global API | Reads `Accept-Language` header (`en` / `ar`) |

## Seed Test Accounts

All seeded accounts use password: `password`

| Role | Name | Email |
|------|------|-------|
| Branch Manager | Sara Hassan | `manager@iti.test` |
| Track Admin (Web) | Ahmed Nour | `admin.web@iti.test` |
| Track Admin (Mobile) | Heba Mansour | `admin.mobile@iti.test` |
| Instructor (internal) | Dr. Hossam Eldin | `hossam@iti.test` |
| Instructor (external) | Eng. Tarek Naguib | `tarek@iti.test` |
| Student | students1@iti.test — students46@iti.test | `studentsN@iti.test` |

## Roles & Permissions

| Action | Branch Manager | Track Admin | Instructor | Student |
|--------|:-:|:-:|:-:|:-:|
| Manage tracks | Full | Read | - | - |
| Manage cohorts | Full | Read | Read (own) | - |
| Manage courses | - | Full | - | - |
| Manage lab groups | - | Full | Read (own) | - |
| Schedule engagements | - | Full | - | - |
| Create sessions | - | Full | - | - |
| Deliver sessions | - | Full | Own engagement | - |
| Record attendance | - | Full | Own sessions | - |
| View attendance ledger | Any | Any | Any | Self only |
| Approve/reject excuses | - | Full | - | - |
| Submit assignments | - | - | - | Self only |
| Grade submissions | - | - | Own lab group | - |
| Enter exam scores | - | Full | - | - |
| Override grades | - | Full | - | - |
| View analytics | Branch-wide | Cohort-wide | Own group | Self only |
| Manage announcements | Full | Full | Active window | Read only |
| View billing | Full | - | - | - |
| Manage users | Full | Instructors & students | - | - |

## API Endpoints

All endpoints are prefixed with `/api`. Authenticated routes require `Authorization: Bearer <token>`.

### Auth

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/auth/login` | Login (public) |
| `POST` | `/auth/logout` | Logout |
| `GET` | `/auth/me` | Current user profile |

### Users

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/users` | List users (filterable by `?role=` and `?expired=`) |
| `POST` | `/users` | Create user |
| `GET` | `/users/{user}` | Show user |
| `PATCH` | `/users/{user}` | Update user |
| `DELETE` | `/users/{user}` | Deactivate user (soft) |

### Tracks

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/tracks` | List tracks (includes active cohort) |
| `POST` | `/tracks` | Create track |
| `GET` | `/tracks/{track}` | Show track |
| `PATCH` | `/tracks/{track}` | Update track |
| `DELETE` | `/tracks/{track}` | Delete track |

### Cohorts

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/cohorts` | List cohorts |
| `POST` | `/cohorts` | Create cohort |
| `GET` | `/cohorts/{cohort}` | Show cohort |
| `PATCH` | `/cohorts/{cohort}` | Update cohort |
| `DELETE` | `/cohorts/{cohort}` | Delete cohort |

### Courses (nested under cohorts)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/cohorts/{cohort}/courses` | List courses for a cohort |
| `POST` | `/cohorts/{cohort}/courses` | Create course |
| `GET` | `/courses/{course}` | Show course |
| `PATCH` | `/courses/{course}` | Update course |
| `DELETE` | `/courses/{course}` | Delete course |

### Lab Groups (nested under cohorts)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/cohorts/{cohort}/lab-groups` | List lab groups |
| `POST` | `/cohorts/{cohort}/lab-groups` | Create lab group |
| `GET` | `/lab-groups/{labGroup}` | Show lab group |
| `PATCH` | `/lab-groups/{labGroup}` | Update lab group |
| `DELETE` | `/lab-groups/{labGroup}` | Delete lab group |
| `POST` | `/lab-groups/{labGroup}/students` | Assign students (sync) |
| `DELETE` | `/lab-groups/{labGroup}/students/{user}` | Remove student |

### Engagements (nested under cohorts)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/cohorts/{cohort}/engagements` | List engagements |
| `POST` | `/cohorts/{cohort}/engagements` | Create engagement |
| `GET` | `/engagements/{engagement}` | Show engagement |
| `PATCH` | `/engagements/{engagement}` | Update engagement |
| `DELETE` | `/engagements/{engagement}` | Delete engagement |

### Sessions (nested under engagements)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/engagements/{engagement}/sessions` | List sessions |
| `POST` | `/engagements/{engagement}/sessions` | Create session |
| `GET` | `/sessions/{session}` | Show session |
| `DELETE` | `/sessions/{session}` | Delete session (only if undelivered) |
| `PATCH` | `/sessions/{session}/deliver` | Mark as delivered (triggers billing) |

### Attendance

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/sessions/{session}/attendance` | List attendance for a session |
| `POST` | `/sessions/{session}/attendance` | Record attendance (auto-adjusts ledger) |
| `PATCH` | `/sessions/{session}/attendance/{record}` | Update attendance status |
| `GET` | `/students/{user}/attendance` | Student attendance history |

### Attendance Ledger

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/students/{user}/ledger` | Ledger balance + deduction history |

### Excuse Requests

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/excuse-requests` | List excuse requests |
| `POST` | `/excuse-requests` | Submit excuse (student, with attachment) |
| `GET` | `/excuse-requests/{excuseRequest}` | Show excuse request |
| `PATCH` | `/excuse-requests/{excuseRequest}/approve` | Approve (adjusts ledger) |
| `PATCH` | `/excuse-requests/{excuseRequest}/reject` | Reject |
| `GET` | `/excuse-requests/{excuseRequest}/attachment` | Download attachment (S3 temp URL) |

### Submissions

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/sessions/{session}/submissions` | List submissions for a session |
| `POST` | `/sessions/{session}/submissions` | Submit (URL or file, auto late penalty) |
| `GET` | `/sessions/{session}/submissions/{submission}/file` | Download file (S3 temp URL) |
| `PATCH` | `/sessions/{session}/submissions/{submission}/grade` | Grade a submission |
| `GET` | `/students/{user}/submissions` | All submissions for a student |

### Grades

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/courses/{course}/grades` | List grades for a course |
| `POST` | `/courses/{course}/grades` | Enter exam score (auto-normalizes) |
| `GET` | `/courses/{course}/grades/{grade}` | Show grade |
| `GET` | `/students/{user}/grades/summary` | Full grade summary (ledger + courses + total) |

### Grade Overrides

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/grades/{grade}/overrides` | Override history |
| `POST` | `/grades/{grade}/override` | Override a grade (records original for audit) |

### Student Tags

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/students/{student}/tags` | List tags for a student |
| `POST` | `/students/{student}/tags` | Add tag (predefined or free text) |
| `DELETE` | `/students/{student}/tags/{tag}` | Remove tag |

### Announcements (nested under cohorts)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/cohorts/{cohort}/announcements` | List announcements |
| `POST` | `/cohorts/{cohort}/announcements` | Post announcement |
| `GET` | `/announcements/{announcement}` | Show announcement |
| `PATCH` | `/announcements/{announcement}` | Update announcement |
| `DELETE` | `/announcements/{announcement}` | Delete announcement |

### Billing

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/billing` | All instructors with rolled-up compensation |
| `GET` | `/billing/{user}` | Session-by-session billing for one instructor |

### Analytics

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/analytics/branch` | Cross-track summary (branch manager) |
| `GET` | `/analytics/cohorts/{cohort}` | Grader consistency + early warning |
| `GET` | `/analytics/instructor` | Instructor dashboard |
| `GET` | `/analytics/student` | Student dashboard (grades, attendance, trend) |
| `GET` | `/analytics/at-risk` | At-risk students (ledger < 150 or grade < 60) |

### QR Attendance

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/qr/session/{session}` | Generate HMAC-signed QR payload (valid 10 min) |
| `POST` | `/qr/scan` | Scan QR to check in / check out |

## Database Schema

### Core Tables

| Table | Purpose |
|-------|---------|
| `users` | All users (UUID PK, role, compensation, expiry) |
| `tracks` | Training tracks (Web Dev, Mobile Dev, etc.) |
| `cohorts` | Cohorts per track (status: open/active/closed) |
| `courses` | Courses per cohort (lab_weight, exam_weight) |
| `lab_groups` | Lab groups per cohort |
| `engagements` | Teaching bookings (type, instructor, date range, hours) |
| `sessions` | Individual sessions within engagements |

### Pivot Tables

| Table | Purpose |
|-------|---------|
| `cohort_track_admin` | Track admins assigned to cohorts |
| `lab_group_student` | Students assigned to lab groups |

### Operational Tables

| Table | Purpose |
|-------|---------|
| `attendance_records` | Per-session attendance (arrived_at, left_at, status) |
| `attendance_ledger` | Per-student point balance (starts at 250) |
| `excuse_requests` | Excuse workflow (requested -> approved/rejected) |
| `submissions` | Lab deliverables (URL or file, late penalty) |
| `course_grades` | Per-student exam scores + computed totals |
| `grade_overrides` | Audit trail for grade overrides |
| `student_tags` | Tags and notes on students |
| `announcements` | Cohort-wide announcements |
| `billing_records` | Billable hours per instructor per session |

## Business Logic

### Attendance Ledger

Each student starts with **250 points**. Deductions are automatic:

| Event | Effect |
|-------|--------|
| Unexcused absence | -25 points |
| Approved excuse | -5 points (reduced from -25) |

Ledger balance is capped at 0 (floor) and 250 (ceiling).

### Late Submission Penalty

Lab deliverables are worth **10 points**. Each full day late deducts **25%** (2.5 points). After 4 full days late, the score reaches 0.

```
penalty = daysLate * 0.25 * 10
finalScore = max(0, rawScore - penalty)
```

### Grade Computation

Courses are scored out of 100, split across:
- **Lab deliverables** (configurable weight, default 40%)
- **Exam/final project** (configurable weight, default 60%)

Normalization formula:
```
componentScore = (rawScore / rawMax) * componentWeight
```

**Grand Total** = Attendance Ledger (out of 250) + Sum of all Course Scores (each out of 100)

### Billing

Auto-calculated when a session is marked as delivered:
- **External instructors**: `totalHours * hourlyRate`
- **Internal (Track Admin)**: `fixedSalary + (totalHours * hourlyRate)`

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/     # 19 API controllers
│   ├── Middleware/            # Account-active check, locale detection
│   ├── Requests/              # Form request validation (26 request classes)
│   └── Resources/             # JSON API resources (16 resource classes)
├── Models/                    # 16 Eloquent models (UUID primary keys)
├── Policies/                  # 13 authorization policies
├── Services/
│   ├── AttendanceLedgerService.php   # Ledger CRUD + balance adjustments
│   ├── BillingService.php            # Session billing + compensation calc
│   ├── GradeComputationService.php   # Normalization + grade totals
│   ├── LatePenaltyService.php        # Late submission penalty
│   └── QrService.php                 # HMAC-signed QR generation/verification
config/
├── attendance.php   # Starting balance, deduction amounts
├── grading.php      # Late penalty multiplier, base points
database/
├── migrations/      # 21 migration files
└── seeders/         # 11 seeders with realistic test data
routes/
└── api.php          # All API routes
```

## Configuration

Custom config files:

- **`config/attendance.php`** — Starting balance (250), deduction values (absent: 25, excused: 5)
- **`config/grading.php`** — Late penalty multiplier (0.25), base points (10)

## API Documentation

A full Postman collection is included at `postman_collection.json`. Import it into Postman to explore all endpoints with pre-configured variables and auth.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Authors

| Name | GitHub |
|------|--------|
| Ahmed Ehab Ahmed | [@ahmed-ehab-reffat](https://github.com/ahmed-ehab-reffat) |
| Ahmed Ehab Farouq | [@ahmedehhab](https://github.com/ahmedehhab) |
| Khaled Cherif | [@Khaleddd11](https://github.com/Khaleddd11) |
| Ahmed Wael | [@notahmedwael](https://github.com/notahmedwael) |
| Menna Mohamed | [@menna7634](https://github.com/menna7634) |

## License

This project is open source and available under the [MIT License](LICENSE).
