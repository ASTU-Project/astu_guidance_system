# ASTU Management System

A university management platform for Adama Science and Technology University. It handles student records, departments, academic status tracking, schedules, campus navigation, policies, and AI-powered automation workflows.

## Tech Stack

- PHP 8.3 / Laravel 13
- Blade templates + Tailwind CSS 3
- Vite
- Laravel Sanctum
- Laravel MCP
- SQLite

## Modules

### Authentication
- Separate login flows for admins (`/admin/login`) and students (`/login`)
- Guard-based access control: `auth:web` for admins, `auth:student` for students

### Admin Dashboard (`/admin`)
- Overview cards: total students, total departments, total calendar bases
- Top 3 departments by student count with percentage share
- Historical average GPA chart grouped by academic year

### Students (`/admin/students`)
- Paginated listing with search by name or student ID
- Filter by department and year level
- Fields: name, student_id, phone, email, department, current_semester, current_year, current_section, cgpa
- Create / edit / delete is scaffolded but not yet implemented

### Departments (`/admin/departments`)
- Lists all departments with live student count
- Fields: name, code, spot_limit, min_gpa
- Department creation is scaffolded but disabled

### Calendar (`/admin/calendar`, `/student/calendar`)
- Admin creates calendar bases (EventBase) scoped to department + semester + section
- Admin adds and edits events per base
- Students view their matched calendar base and add personal events
- Events support weekly recurring (day-based) and specific-date modes
- Overlap detection prevents conflicting time slots
- 5 color options per event

### Campus Map (`/admin/map`, `/student/navigate`)
- Admin manages map locations: name, description, lat/lng, category, icon, optional image
- Students view all locations on an interactive map

### Policy Rules (`/admin/policy`)
- Admin creates, updates, and deletes policy entries: title, category, content, active flag
- Active policies are surfaced to students through the AI assistant

### Academic Status (`/student/status`)
- Semester GPA and cumulative GPA computed from Grade records with credit-hour weighting
- Year-level overview table: Sem 1 GPA, Sem 2 GPA, year GPA, CGPA, delta vs previous year
- Cohort rank and percentile within the same department and year level
- Standing: Excellent Standing (≥ 3.5), Good Standing (≥ 2.0), At Risk
- Performance category: Excellent (top 20%), Good (top 50%), Needs Improvement
- Subject performance chart with current-semester or all-time toggle
- Yearly GPA trend chart
- Semester panels listing all subjects with score, letter grade, and credit hours
- Filters: year, semester (Sem 1 / Sem 2), view mode

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
- LLM-powered chat using the Cerebras backend with tool-calling support
- Tools toggled per admin via AutomationSettings (departments, students, policies, etc.)
- Session memory stored in ChatMessage records
- In-flight lock prevents duplicate concurrent requests

### Student AI Assistant (`/student/ai-assistant`)
- **Assistant mode** — LLM chat with access to department_list and policy_list tools
- **Guide mode** — forwards the question to an external Academic Guide microservice, returns the answer with cited sources and supports conversation history (last 4 turns)

### Profiles
- Admin and student profiles each support updating name/email and changing password via separate forms

### Community & Department Guide
- Static informational pages at `/student/community` and `/student/department-guide`

## Data Models

| Model | Key Fields |
|-------|-----------|
| Student | name, student_id, email, phone, department, current_year, current_semester, current_section, cgpa |
| Department | name, code, spot_limit, min_gpa |
| EventBase | department, semester, section |
| Event | event_id, task, day, event_date, start_hour, start_min, end_hour, end_min, color, source, student_id |
| Grade | student_id, subject_id, score, year, semester |
| Subject | name, code, credit_hours, year |
| MapLocation | name, description, latitude, longitude, category, icon, image_url |
| Policy | title, category, content, is_active |
| ChatMessage | user_id, session_id, role, content |
| AutomationSetting | user_id, enabled tool group flags |

## Project Status

| Feature | Status |
|---------|--------|
| Admin dashboard | ✅ Complete |
| Student academic status | ✅ Complete |
| Calendar (admin + student) | ✅ Complete |
| Campus map | ✅ Complete |
| Policy CRUD | ✅ Complete |
| Admin automation chat | ✅ Complete |
| Student AI assistant | ✅ Complete |
| Student / admin profiles | ✅ Complete |
| Student CRUD | 🚧 Scaffolded, not implemented |
| Department creation | 🚧 Scaffolded, disabled |
| Community & Department Guide | 🚧 Static placeholders |
