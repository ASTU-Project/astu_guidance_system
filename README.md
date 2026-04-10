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
- Data setup includes student factory-based seeding for test data.
- Core models prepared with fillable/casts and relations for major entities.

### In Progress / Next
- Add edit/delete actions for department records.
- Add CRUD pages for additional admin modules (events, subjects, grades).
- Improve dashboard analytics to be fully data-driven (messages/graphs from DB).
- Add feature tests for auth-protected admin flows.

## Main Modules
- Authentication (`/login`, `/logout`)
- Admin dashboard (`/admin/dashboard`)
- Students management (`/admin/students`)
- Departments management (`/admin/departments`)
- Other admin stubs: calendar, map, policy, blog, message, automate

## Tech Stack
- Laravel 12
- Blade templates
- Eloquent ORM
- Tailwind CSS

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
