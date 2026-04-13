# ASTU Management System

A Laravel-based university management system with a Blade admin panel for student, department, and dashboard operations.

## Current Project Progress

### Completed
- Admin UI migrated to Blade views with shared admin layout.
- Public pages in Blade (`welcome`, `login`) are active.
- Dashboard route uses controller-driven stats.
- Dashboard overview cards show live values (students, departments, events, year).
- Students page has:
	- Server-side search by name or student ID.
	- Department and year filters.
	- Query-preserving pagination.
- Department management page has:
	- Directory table with capacity/load visualization.
	- Popup modal form to add a new department.
	- Department creation validation and success feedback.
- Calendar management now has:
	- Calendar base list page with modal form to create a base (department, semester, section, min GPA input).
	- Event page per base with weekly preview and 10-minute slot grid.
	- Event create form wired to database.
	- Support for both recurring weekly events and specific-date events (`event_date` nullable logic).
	- Event color picker with restricted palette and persisted selection.
	- Validation old-input restore for failed submissions.
	- Click-to-edit popup modal wired to update event records.
- Automation chat now has:
	- Direct AI chat flow for admins.
	- Session-based conversation memory.
	- Saved user automation settings and recent chat history.
	- Reload protection and draft/session restore in the browser.
	- New Chat reset for starting a fresh conversation.
- Data setup includes student factory-based seeding for test data.
- Core models prepared with fillable/casts and relations for major entities.

### In Progress / Next
- Add edit/delete actions for department records.
- Add delete flow for calendar events.
- Improve calendar preview to render overlapping events in the same slot.
- Add CRUD pages for additional admin modules (subjects, grades).
- Improve dashboard analytics to be fully data-driven (messages/graphs from DB).
- Add feature tests for auth-protected admin flows.
- Expand automation chat with tool execution if needed later.

## Main Modules
- Authentication (`/login`, `/logout`)
- Admin dashboard (`/admin/dashboard`)
- Students management (`/admin/students`)
- Departments management (`/admin/departments`)
- Calendar management (`/admin/calendar`, `/admin/calendar/Events/{id}`)
- Other admin modules: map, policy, automate

## Calendar Routes

- `GET /admin/calendar` -> list event bases
- `POST /admin/calendar` -> create event base
- `GET /admin/calendar/Events/{id}` -> open events page for one base
- `POST /admin/calendar/Events/{id}` -> create event under base
- `PUT /admin/calendar/Events/{id}/{event}` -> update event

## Calendar Data Rules

- `event_date` filled: event is treated as one-time (specific date).
- `event_date` empty: event is treated as recurring weekly using `day`.
- When `event_date` is provided, backend derives `day` from the date.

## Automation Chat

The admin automation page is now a working chat interface that sends a message to the backend, forwards it to Cerebras AI, and shows the response in the browser.

### Main Routes

- `GET /admin/automate` -> open the chat page
- `POST /admin/automate/chat` -> send a chat message to the AI controller
- `GET /admin/automation-settings` -> load saved chat settings and recent history
- `PUT /admin/automation-settings` -> save chat settings

### What the chat does

- Keeps a session id so the conversation continues across messages.
- Stores user and assistant messages in the database.
- Loads user automation settings into the AI prompt.
- Saves the draft and visible chat thread in the browser so reloads do not wipe the screen.
- Lets the user start a new chat with a clean session.


## Tech Stack
- Laravel 13
- Blade templates
- Eloquent ORM
- Tailwind CSS
- Cerebras AI chat completions API

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run dev
php artisan serve
```

## Default Test Account

The seeder creates a test account:

- Email: `test@example.com`
- Password: `password`

## Notes
- Admin routes are protected by `auth` middleware.
- Students and departments data are now displayed from live database values.
- The automation chat uses `chat_messages` and `automation_settings` tables to persist session state and user preferences.
