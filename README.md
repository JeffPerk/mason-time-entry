# Mason Time Entry Interface

A Laravel + Vue Composition API take-home project for creating and viewing employee time entries.

The application allows users to create validated time entries for employees, projects, and tasks across multiple companies. It includes a New Entries workflow for bulk entry and a History tab for reviewing submitted time.

## Tech Stack

- Laravel
- Vue 3 Composition API
- Vite
- SQLite
- Tailwind CSS
- REST-style Laravel API endpoints

Authentication is intentionally not included because it was not required for the exercise.

## Features

### Backend

- Companies, employees, projects, tasks, and time entries
- Company-specific tasks
- Company-specific projects
- Employees can belong to multiple companies
- Employees can be assigned to one or more projects
- Bulk time entry creation
- Backend validation for relationship integrity
- Backend validation for business rules
- Seeded sample data

### Frontend

- Vue Composition API interface
- Global company filter
- New Entries tab
- History tab
- Company-dependent dropdowns
- Keyboard-friendly table inputs
- Add row
- Remove row
- Duplicate row
- Row-level validation messages
- History search
- History sorting
- Total hours summary

## Business Rules

The main business rule is:

> An employee can only work on one project per date.

However, an employee may work on multiple tasks for the same project on the same date.

For example, this is valid:

| Employee    | Date       | Project              | Task         | Hours |
| ----------- | ---------- | -------------------- | ------------ | ----- |
| Maria Lopez | 2026-05-03 | Warehouse Renovation | Framing      | 3     |
| Maria Lopez | 2026-05-03 | Warehouse Renovation | Site Cleanup | 2     |

This is invalid:

| Employee    | Date       | Project              | Task            | Hours |
| ----------- | ---------- | -------------------- | --------------- | ----- |
| Maria Lopez | 2026-05-03 | Warehouse Renovation | Framing         | 3     |
| Maria Lopez | 2026-05-03 | Office Buildout      | Inspection Prep | 2     |

This rule is enforced on the backend in `StoreTimeEntriesRequest`.

## Setup

Clone the repository and enter the project directory:

```bash
git clone <repository-url>
cd mason-time-entry
```

Install dependencies:

```bash
composer install
npm install
```

Create the environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

Configure SQLite:

```bash
touch database/database.sqlite
```

In `.env`, set:

```env
DB_CONNECTION=sqlite
```

If other database variables are present, they can be left empty or ignored for local SQLite usage.

Run migrations and seeders:

```bash
php artisan migrate:fresh --seed
```

Start the Laravel server:

```bash
php artisan serve
```

In another terminal, start Vite:

```bash
npm run dev
```

Open the app: [http://127.0.0.1:8000](http://127.0.0.1:8000)

## API Endpoints

### Companies

`GET /api/companies`

Returns companies for the global company filter.

### Company options

`GET /api/companies/{company}/options`

Returns employees, projects, and tasks for a selected company.

Used by the New Entries tab to populate valid dropdown options.

### Time entry history

`GET /api/time-entries`  
`GET /api/time-entries?company_id=1`

Returns submitted time entries.

The optional `company_id` query parameter filters history by company.

### Create time entries

`POST /api/time-entries`

Example request body:

```json
{
  "entries": [
    {
      "company_id": 1,
      "entry_date": "2026-05-03",
      "employee_id": 2,
      "project_id": 1,
      "task_id": 1,
      "hours": 3.5
    },
    {
      "company_id": 1,
      "entry_date": "2026-05-03",
      "employee_id": 2,
      "project_id": 1,
      "task_id": 2,
      "hours": 2
    }
  ]
}
```

Validation errors are returned as JSON when the request includes:

- `Accept: application/json`
- `Content-Type: application/json`

## Data model notes

The application uses the following main tables:

- `companies`
- `employees`
- `company_employee`
- `projects`
- `project_employee`
- `tasks`
- `time_entries`

Relationship summary:

- A company has many projects.
- A company has many tasks.
- A company belongs to many employees.
- An employee belongs to many companies.
- A project belongs to one company.
- A project belongs to many employees.
- A task belongs to one company.
- A time entry belongs to a company, employee, project, task, and date.

## UX decisions

The interface is designed for quick time entry.

Notable UX choices:

- The top company filter defaults to All.
- When All is selected, each new entry row can choose its own company.
- When a specific company is selected, new rows default to that company.
- Employee, project, and task dropdowns are loaded based on the selected company.
- The user can duplicate a row to speed up repetitive entry.
- Validation errors are shown next to the relevant row and field.
- The History tab includes search, sorting, and total hours.

## Performance considerations

For this take-home, the data size is small, but a few performance considerations were included:

- The History endpoint uses eager loading to avoid N+1 queries.
- The New Entries tab caches company option responses after they are loaded.
- The frontend avoids reloading employees/projects/tasks for the same company repeatedly.
- The History tab uses a company filter query parameter to avoid loading unrelated entries when a company is selected.
- Database indexes were added around common time entry lookup patterns such as company/date, employee/date, and project/date.

If this were expanded for production, the next improvements would be:

- Server-side pagination for History.
- Server-side search and sorting.
- API resource classes for response formatting.
- More robust concurrency protection around time entry creation.
- Policies/authentication.
- Soft deletes or audit logging.
- Dedicated tests for validation and business rules.

## AI usage

AI was used as a coding assistant throughout the project to help plan the data model, generate migrations, build seeders, design API endpoints, implement validation, and create the Vue interface.

The AI conversation log is included at:

`docs/ai-conversation-log.json`

This log summarizes each major development step and the implementation decisions made along the way.
