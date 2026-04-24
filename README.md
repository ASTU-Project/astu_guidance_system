# ASTU Management System

A university management platform built with Laravel 13, Blade, Tailwind CSS, and Vite. It handles student records, departments, academic status tracking, schedules, campus navigation, policies, and AI-powered automation workflows.

## Tech Stack

- PHP 8.3 / Laravel 13
- Blade templates + Tailwind CSS 3
- Vite + React (Inertia.js available)
- Laravel Sanctum (API tokens)
- Laravel MCP
- Pest (testing)
- SQLite (default database)

## Main Modules

### Authentication
- Separate login flows for admin (`/admin/login`) and students (`/login`)
- Guard-based access control: `auth:web` for admin, `auth:student` for students

### Admin Dashboard (`/admin`)
- Overview cards: total students, departments, calendar bases
- Top 3 departments by student count with percentage share
- Historical average GPA chart (by academic year)

### Students Management (`/admin/students`)
- Paginated listing with search by name or student ID
- Filter by department and year level
- Student model fields: name, student_id, phone, email, department, current_semester, current_year, current_section, cgpa

### Departments Management (`/admin/departments`)
- Department directory with live student count per department
- Department model fields: name, code, spot_limit, min_gpa
- Department creation is scaffolded but currently disabled (commented out)

### Calendar Management (`/admin/calendar`)
- Admin creates calendar bases (EventBase) scoped to department + semester + section
- Admin adds, edits events per base (`/admin/calendar/Events/{id}`)
- Students view their matched calendar base and add personal events
- Events support weekly recurring (day-based) and specific-date modes
- Overlap detection prevents conflicting time slots
- 5 color options per event

### Campus Map (`/admin/map`)
- Admin manages map locations: name, description, lat/lng, category, icon, optional image upload
- Students view locations on the navigate page (`/student/navigate`)

### Policy Rules (`/admin/policy`)
- Admin creates, updates, and deletes policy entries (title, category, content, active flag)
- Policies are surfaced to students via the AI assistant tool

### Academic Status — Student Side (`/student/status`)
- Semester GPA and cumulative GPA computed from Grade records with credit-hour weighting
- Year-level overview table: Sem 1 GPA, Sem 2 GPA, year GPA, CGPA, delta vs previous year
- Cohort rank and percentile within same department + year level
- Standing labels: Excellent Standing (≥ 3.5), Good Standing (≥ 2.0), At Risk
- Performance category: Excellent (top 20%), Good (top 50%), Needs Improvement
- Subject performance chart (current semester or all-time toggle)
- Yearly GPA trend chart
- Semester panels listing all subjects with score, letter grade, and credit hours
- Filters: year, semester (Sem 1 / Sem 2), view mode (Current / All Time)

Grade scale:

| Score | Points | Letter |
|-------|--------|--------|
| ≥ 90  | 4.0    | A+     |
| ≥ 85  | 4.0    | A      |
| ≥ 80  | 3.7    | A-     |
| ≥ 75  | 3.3    | B+     |
| ≥ 70  | 3.0    | B      |
| ≥ 65  | 2.7    | B-     |
| ≥ 60  | 2.0    | C      |
| ≥ 50  | 1.0    | D      |
| < 50  | 0.0    | F      |

### Admin Automation Chat (`/admin/automate`)
- LLM-powered chat (Cerebras backend) with tool-calling support
- Tools enabled per user automation settings (departments, students, policies, etc.)
- Session memory via ChatMessage records
- In-flight lock prevents duplicate concurrent requests
- Configurable via AutomationSettings per admin user

### Student AI Assistant (`/student/ai-assistant`)
- Two modes: **assistant** (LLM with department_list and policy_list tools) and **guide** (external Academic Guide service via HTTP)
- Guide mode supports conversation history (last 4 turns) and returns cited sources
- Assistant mode uses the same Cerebras LLM backend as admin chat

### Student Profile & Admin Profile
- Update display name / email
- Change password (separate form)

### Community & Department Guide
- Static pages available at `/student/community` and `/student/department-guide`

## Getting Started

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if not present
php artisan migrate
php artisan db:seed
npm run dev
php artisan serve
```

Or use the composer shortcut:

```bash
composer setup
composer dev   # starts server, queue worker, and Vite concurrently
```

## Environment Variables

Key values to configure in `.env`:

```
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite

# LLM provider for admin automation and student assistant
CEREBRAS_API_KEY=
# or configure via services.cerebras.key

# Academic Guide microservice (student guide mode)
ACADEMIC_GUIDE_ENDPOINT=http://127.0.0.1:8000/v1/chat
ACADEMIC_GUIDE_TOP_K=5
ACADEMIC_GUIDE_TIMEOUT=180
```

## Default Seed Account

```
Email:    test@example.com
Password: password
```

## Running Tests

```bash
composer test
# or
php artisan test
```

Tests use Pest. Unit tests are in `tests/Unit/`, feature tests in `tests/Feature/`.

## Project Status

- Core admin and student modules are operational
- Academic status flow is fully controller-driven with weighted GPA calculation
- Calendar overlap detection is active for both recurring and date-specific events
- Campus map with image upload is functional
- Policy CRUD is complete
- Department creation is scaffolded but disabled pending validation UI
- Student CRUD (create/edit/delete) is scaffolded but not yet implemented
- Community and Department Guide pages are static placeholders

## Maintainer Notes

- Academic rules follow ASTU two-semester structure (Sem 1 / Sem 2)
- GPA calculation lives in `StatusController` — keep it as the single source of truth
- Calendar base matching uses department + semester + section from the Student model
- Blade templates receive pre-computed data from controllers; avoid logic in views
