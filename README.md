# ASTU Management System

ASTU Management System is a university management platform for handling student records, departments, academic status tracking, schedules, and admin automation workflows.

## Purpose

This project is designed to help academic staff and students manage core campus activities in one place.

- Manage student and department information
- Organize calendar events and schedules
- Track student academic progress by year and semester
- Support admin-side automation chat and settings

## Main Areas

### 1. Authentication

- Secure login and logout flow
- Access control for protected admin pages

### 2. Admin Dashboard

- Overview cards for key academic entities
- Quick access to major management pages

### 3. Students Management

- Student listing with search and filters
- Student profile and academic-focused views

### 4. Departments Management

- Department directory and capacity overview
- Department creation and validation feedback

### 5. Academic Status (Student Side)

- Semester GPA and cumulative GPA summary
- Ranking and percentile display by cohort
- Yearly trend and subject performance charts
- Semester-separated mark panels across available years

### 6. Calendar Management

- Calendar base creation by academic grouping
- Event management for recurring and specific-date schedules
- Edit support for existing event entries

### 7. Automation Chat

- Admin chat workflow for assistant-driven tasks
- Session memory and recent history support
- Saved user automation preferences

## Academic Status Overview

The academic status page is built to present student performance in a clear and practical way.

- Supports Sem 1 and Sem 2 structure
- Works with multiple academic years
- Uses weighted GPA calculation based on subject credit hours
- Shows rank, percentile, and standing labels

For a full logic document, see: docs/academic-status-logic.md

## Getting Started

1. Install project dependencies.
2. Configure your environment file.
3. Run migrations and seed data.
4. Start the application and frontend build.

Typical commands:

- composer install
- npm install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate
- php artisan db:seed
- npm run dev
- php artisan serve

## Default Seed Account

If seed data is enabled, the project includes a basic test account:

- Email: test@example.com
- Password: password

## Project Status

Current state of the project:

- Core modules are operational
- Student academic status flow is active and controller-driven
- Admin calendar and automation pages are available

Planned improvements:

- Additional CRUD completeness in some admin modules
- More tests for critical flows
- Continued UI and reporting enhancements

## Maintainers Notes

- Keep academic rules aligned with ASTU semester policy (two-semester structure).
- Keep controller logic as the source of truth for calculations.
- Keep Blade templates focused on rendering prepared data.
