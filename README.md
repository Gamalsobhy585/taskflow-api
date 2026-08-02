# Electro Pi Laravel API Assessment

A modular Laravel REST API created as a technical assessment for **Electro Pi**.

The project demonstrates a clean, testable backend architecture for managing users, projects, tasks, dashboard statistics, Redis caching, permissions, queues, scheduled jobs, and notifications.

## Table of Contents

- [Project Overview](#project-overview)
- [Main Features](#main-features)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Project Structure](#project-structure)
- [Domain Model](#domain-model)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Running the Application](#running-the-application)
- [Authentication](#authentication)
- [Permissions and Ownership](#permissions-and-ownership)
- [API Endpoints](#api-endpoints)
- [Authentication Module](#authentication-module)
- [Projects Module](#projects-module)
- [Tasks Module](#tasks-module)
- [Dashboard Module](#dashboard-module)
- [Redis Caching](#redis-caching)
- [Queues and Overdue Notifications](#queues-and-overdue-notifications)
- [API Response Format](#api-response-format)
- [Testing](#testing)
- [Code Quality](#code-quality)
- [Useful Commands](#useful-commands)
- [Security Rules](#security-rules)
- [Git Workflow](#git-workflow)
- [Postman Workspace](#postman-workspace)
- [Additional Documentation](#additional-documentation)

---

## Project Overview

The application is organized as a **modular monolith** with four main business modules:

```text
Authentication
Projects
Tasks
Dashboard
```

The assessment demonstrates:

- Laravel Sanctum API authentication
- Spatie role and permission authorization
- Resource ownership enforcement
- Modular folder organization
- Form Request validation
- Typed Data Transfer Objects
- Service and repository layers
- Interfaces and dependency injection
- Custom domain exceptions
- Redis caching
- Soft deletes and bulk delete operations
- Queue-based overdue-task notifications
- Scheduler integration
- API Resources and consistent JSON responses
- Unit and feature tests

---

## Main Features

### Authentication

- Register a user
- Login and issue a Sanctum token
- Logout and revoke the current token
- Change the authenticated user's password
- Retrieve authenticated user information

### Projects

- Create projects
- List and paginate owned projects
- View an owned project
- Update an owned project
- Soft-delete an owned project
- Bulk soft-delete owned projects
- Cache list and detail responses in Redis

### Tasks

- Create tasks inside owned projects
- List, paginate, search, and filter tasks
- View and update owned tasks
- Soft-delete tasks
- Bulk soft-delete tasks
- Store priority and status through backed enums
- Cache task list and detail responses
- Send overdue-task notifications through queued jobs

### Dashboard

- Total projects
- Active projects
- Total tasks
- Completed tasks
- Pending tasks
- Overdue tasks

All dashboard statistics are scoped to the authenticated user.

---

## Technology Stack

| Area | Technology |
|---|---|
| Framework | Laravel |
| Language | PHP 8.2+ |
| Database | MySQL or MariaDB |
| Authentication | Laravel Sanctum |
| Authorization | Spatie Laravel Permission |
| Cache | Redis |
| Queue | Redis queue driver |
| Notifications | Mail and database notifications |
| Testing | PHPUnit through `php artisan test` |
| Code formatting | Laravel Pint |
| API testing | Postman |

Redis can use either `phpredis` or `predis`, depending on the installed client. The `.env` value must match the client available in the environment.

---

## Architecture

The project follows a layered modular architecture.

```text
HTTP Request
    ↓
Route
    ↓
Sanctum Authentication Middleware
    ↓
Permission Middleware / Form Request Authorization
    ↓
Form Request Validation
    ↓
Controller
    ↓
DTO
    ↓
Service Interface
    ↓
Service Implementation
    ↓
Cache Interface / Redis Implementation
    ↓
Repository Interface
    ↓
Repository Implementation
    ↓
Eloquent Model
    ↓
Database
```

The response path is:

```text
Model / Service Result
    ↓
API Resource
    ↓
ResponseTrait
    ↓
JSON Response
```

### Architectural Principles

- Controllers remain thin.
- Request objects stop at the controller layer.
- DTOs transfer structured data between layers.
- Services contain business rules and transaction coordination.
- Repositories contain database access only.
- Redis logic is isolated behind cache interfaces.
- API Resources define response shapes.
- Domain exceptions represent business failures.
- Controllers depend on interfaces rather than concrete implementations.
- Cache invalidation happens only after successful database writes.

### Applied Patterns

- Modular Monolith
- Service Layer Pattern
- Repository Pattern
- DTO Pattern
- Dependency Injection
- Dependency Inversion
- Interface Segregation
- Single Responsibility Principle
- Cache Abstraction
- Resource Transformation
- Domain-Specific Exceptions
- Queue-Based Background Processing
- Role and Permission-Based Access Control
- Ownership-Based Authorization

---

## Project Structure

```text
app/
├── Enums/
│   ├── ProjectStatus.php
│   ├── TaskPriority.php
│   └── TaskStatus.php
├── Jobs/
│   └── SendOverdueTaskNotificationJob.php
├── Models/
│   ├── User.php
│   ├── Project.php
│   └── Task.php
├── Notifications/
│   └── OverdueTaskNotification.php
├── Providers/
├── Traits/
│   └── ResponseTrait.php
└── Modules/
    ├── Authentication/
    ├── Projects/
    ├── Tasks/
    └── Dashboard/
```

A module may contain:

```text
Controllers/
DTOs/
Exceptions/
Repositories/
    Interface/
    Implementation/
Services/
    Interface/
    Implementation/
Requests/
Resources/
Routes/
Seeders/
Cache/
    Interface/
    Implementation/
```

Tests mirror the application modules:

```text
tests/
├── Concerns/
├── Unit/
│   └── Modules/
│       ├── Authentication/
│       ├── Projects/
│       ├── Tasks/
│       └── Dashboard/
└── Feature/
    └── Modules/
        ├── Authentication/
        ├── Projects/
        ├── Tasks/
        └── Dashboard/
```

---

## Domain Model

```text
User
  1
  └── hasMany Projects
          1
          └── hasMany Tasks
```

### Relationships

```php
// User.php
public function projects(): HasMany
{
    return $this->hasMany(Project::class);
}
```

```php
// Project.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function tasks(): HasMany
{
    return $this->hasMany(Task::class);
}
```

```php
// Task.php
public function project(): BelongsTo
{
    return $this->belongsTo(Project::class);
}
```

### Foreign Keys

```text
projects.user_id → users.id
ON UPDATE CASCADE
ON DELETE CASCADE

 tasks.project_id → projects.id
ON UPDATE CASCADE
ON DELETE CASCADE
```

### Enum Values

#### Project Status

```text
1 = Active
2 = Completed
3 = Archived
```

#### Task Priority

```text
1 = Low
2 = Medium
3 = High
```

#### Task Status

```text
1 = Todo
2 = In Progress
3 = Done
```

Backed enums avoid magic numbers and allow API Resources to return both values and readable labels.

---

## Installation

### Requirements

Install the following before starting:

```text
PHP 8.2 or later
Composer
MySQL or MariaDB
Redis
Git
Node.js and npm, only if frontend assets are required
```

Recommended local environments include Laragon, Laravel Herd, Docker, or Linux with PHP, MySQL, and Redis.

### 1. Clone the Repository

```bash
git clone <repository-url> electro-pi-assessment
cd electro-pi-assessment
```

### 2. Install PHP Dependencies

```bash
composer install
```

If the project uses Predis and it is not installed:

```bash
composer require predis/predis
```

### 3. Create the Environment File

Linux or Git Bash:

```bash
cp .env.example .env
```

Windows Command Prompt:

```cmd
copy .env.example .env
```

PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Generate the Application Key

```bash
php artisan key:generate
```

### 5. Create the Database

```sql
CREATE DATABASE electro_pi_assessment
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

### 6. Configure `.env`

Configure the database, Redis, cache, queue, and mail settings as described below.

### 7. Run Migrations and Seeders

For a fresh local database:

```bash
php artisan migrate:fresh --seed
```

For an existing database that must not be cleared:

```bash
php artisan migrate --seed
```

### 8. Create Queue and Notification Tables When Missing

Run these commands only when the corresponding migrations are not already included:

```bash
php artisan notifications:table
php artisan make:queue-table
php artisan make:queue-failed-table
php artisan migrate
```

### 9. Clear Cached Configuration

```bash
php artisan optimize:clear
```

---

## Environment Configuration

### Application and Database

```env
APP_NAME="Electro Pi Assessment"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=electro_pi_assessment
DB_USERNAME=root
DB_PASSWORD=
```

### Redis with PhpRedis

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

### Redis with Predis

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

Older Laravel versions may use:

```env
CACHE_DRIVER=redis
```

instead of:

```env
CACHE_STORE=redis
```

Use the variable configured by the project's `config/cache.php`.

Verify Redis:

```bash
redis-cli ping
```

Expected response:

```text
PONG
```

### Mail

For local development, the log mailer is sufficient:

```env
MAIL_MAILER=log
```

Mail output is written to:

```text
storage/logs/laravel.log
```

Use SMTP credentials when real email delivery is required.

---

## Running the Application

### Start the API Server

```bash
php artisan serve
```

Default URL:

```text
http://127.0.0.1:8000
```

### Start the Queue Worker

```bash
php artisan queue:work redis --tries=3
```

Restart queue workers after changing job code:

```bash
php artisan queue:restart
```

### Start the Scheduler Locally

```bash
php artisan schedule:work
```

### Recommended Local Startup Order

```text
1. Start MySQL.
2. Start Redis.
3. Run php artisan serve.
4. Run php artisan queue:work redis --tries=3.
5. Run php artisan schedule:work.
6. Open the Postman workspace.
7. Register or log in.
8. Add the Bearer token to protected requests.
```

---

## Authentication

The API uses Laravel Sanctum.

Protected requests require:

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
Content-Type: application/json
```

Login creates an access token:

```php
$user->createToken('default_token')->plainTextToken;
```

Logout revokes only the token used for the current request:

```php
$user->currentAccessToken()->delete();
```

The password is hashed through the `User` model cast:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

Do not hash a password twice when this cast is enabled.

---

## Permissions and Ownership

The system applies two separate authorization checks.

### Permission Authorization

Spatie permissions answer:

> Is the user generally allowed to perform this action?

Examples:

```text
create-project
list-project
view-project
update-project
delete-project
create-task
list-task
view-task
update-task
delete-task
view-dashboard
```

### Ownership Authorization

Ownership answers:

> Does this resource belong to the authenticated user?

Project ownership query:

```php
Project::query()
    ->whereKey($projectId)
    ->where('user_id', $userId)
    ->first();
```

Task ownership query:

```php
Task::query()
    ->whereKey($taskId)
    ->whereHas(
        'project',
        fn ($query) => $query->where('user_id', $userId)
    )
    ->first();
```

A user may have permission to view projects generally but still cannot view another user's project.

Missing or unowned projects and tasks return `404 Not Found` to avoid exposing the existence of another user's records.

### Spatie Middleware Aliases

Laravel applications that configure middleware in `bootstrap/app.php` must register the Spatie aliases:

```php
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'permission' => PermissionMiddleware::class,
        'role' => RoleMiddleware::class,
        'role_or_permission' => RoleOrPermissionMiddleware::class,
    ]);
})
```

Without these aliases, Laravel may throw:

```text
Target class [permission] does not exist.
```

Reset the permission cache after permission changes:

```bash
php artisan permission:cache-reset
```

---

## API Endpoints

All paths below assume the routes are loaded under Laravel's `/api` prefix.

### Authentication

| Method | Endpoint | Authentication | Description |
|---|---|---:|---|
| POST | `/api/auth/register` | No | Register a user |
| POST | `/api/auth/login` | No | Login and issue a token |
| POST | `/api/auth/logout` | Yes | Revoke the current token |
| PUT | `/api/auth/renew-password` | Yes | Change the password |
| GET | `/api/auth/user-info` | Yes | Get authenticated user data |

### Projects

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/api/projects` | `list-project` | List owned projects |
| POST | `/api/projects` | `create-project` | Create a project |
| GET | `/api/projects/{project}` | `view-project` | View an owned project |
| PUT/PATCH | `/api/projects/{project}` | `update-project` | Update an owned project |
| DELETE | `/api/projects/{project}` | `delete-project` | Soft-delete an owned project |
| DELETE | `/api/projects/bulk-delete` | `delete-project` | Bulk soft-delete owned projects |

### Tasks

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/api/tasks` | `list-task` | List and filter tasks |
| POST | `/api/tasks` | `create-task` | Create a task |
| GET | `/api/tasks/{task}` | `view-task` | View an owned task |
| PUT | `/api/tasks/{task}` | `update-task` | Update an owned task |
| DELETE | `/api/tasks/{task}` | `delete-task` | Soft-delete an owned task |
| DELETE | `/api/tasks/bulk-delete` | `delete-task` | Bulk soft-delete tasks |

### Dashboard

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/api/dashboard` | `view-dashboard` | Get user-scoped dashboard statistics |

Static routes such as `bulk-delete` must be declared before dynamic routes such as `/{project}` or `/{task}`.

---

## Authentication Module

### Request Flow

```text
Request
→ AuthController
→ Authentication DTO
→ IAuthService
→ AuthService
→ IUser
→ UserRepository
→ User Model
```

### Register

```http
POST /api/auth/register
```

Request:

```json
{
  "name": "Gamal Sobhy",
  "email": "gamal@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Successful response:

```http
201 Created
```

```json
{
  "status": true,
  "message": "User registered successfully.",
  "data": {
    "id": 1,
    "name": "Gamal Sobhy",
    "email": "gamal@example.com"
  }
}
```

### Login

```http
POST /api/auth/login
```

Request:

```json
{
  "email": "gamal@example.com",
  "password": "password123"
}
```

Successful response:

```json
{
  "status": true,
  "message": "Login completed successfully.",
  "data": {
    "token": "1|generated-sanctum-token",
    "user_data": {
      "id": 1,
      "name": "Gamal Sobhy",
      "email": "gamal@example.com"
    }
  }
}
```

Invalid email and invalid password return the same generic response:

```http
401 Unauthorized
```

```json
{
  "status": false,
  "message": "Invalid credentials."
}
```

### Logout

```http
POST /api/auth/logout
```

No request body is required.

### Renew Password

```http
PUT /api/auth/renew-password
```

Request:

```json
{
  "old_password": "password123",
  "new_password": "newPassword123",
  "new_password_confirmation": "newPassword123"
}
```

The user's email is obtained from the authenticated request and must not be accepted from client input.

### User Information

```http
GET /api/auth/user-info
```

The result may be cached using a key such as:

```text
user:info:{user_id}
```

The cache should be cleared after updating or deleting the user.

### Main Authentication Exceptions

| Exception | Status | Purpose |
|---|---:|---|
| `EmailAlreadyExistsException` | 422 | Registration email already exists |
| `InvalidCredentialsException` | 401 | Credentials or old password are invalid |
| `UserNotFoundException` | 404 | User cannot be found |

---

## Projects Module

### Request Flow

```text
ProjectController
→ Project DTO
→ IProjectService
→ ProjectService
→ IProjectCache / RedisProjectCache
→ IProjectRepository
→ ProjectRepository
→ Project Model
```

### Business Rules

1. Every project belongs to one user.
2. Users access only their own projects.
3. Permissions control allowed actions.
4. Projects use soft deletes.
5. List results are paginated.
6. Project status must be a valid `ProjectStatus` value.
7. Redis caches list and detail reads.
8. Writes execute inside database transactions.
9. Cache invalidation happens after successful writes.
10. Bulk deletion affects only projects owned by the authenticated user.
11. Missing and unowned projects return `404`.

### Create Project

```http
POST /api/projects
```

Example request:

```json
{
  "name": "Electro PI Assessment",
  "description": "Laravel API technical assessment",
  "status": 1
}
```

Example response:

```json
{
  "status": true,
  "message": "Project created successfully.",
  "data": {
    "id": 10,
    "name": "Electro PI Assessment",
    "description": "Laravel API technical assessment",
    "status": {
      "value": 1,
      "label": "Active"
    }
  }
}
```

The authenticated user ID is assigned server-side and must not be accepted from request input.

### List Projects

```http
GET /api/projects?per_page=15&page=1
```

Only the authenticated user's non-deleted projects are returned.

### View, Update, and Delete

```http
GET    /api/projects/{project}
PUT    /api/projects/{project}
DELETE /api/projects/{project}
```

The project must belong to the authenticated user.

### Bulk Delete

```http
DELETE /api/projects/bulk-delete
```

Request:

```json
{
  "project_ids": [10, 11, 12]
}
```

Response:

```json
{
  "status": true,
  "message": "Projects deleted successfully.",
  "data": {
    "deleted_count": 3
  }
}
```

Only owned projects are deleted and counted.

### Project Cache Strategy

User tag:

```text
projects:user:{userId}
```

List key:

```text
projects:list:page:{page}:per_page:{perPage}
```

Detail key:

```text
projects:item:{projectId}
```

Recommended TTL:

```text
600 seconds
```

### Main Project Exceptions

| Exception | Status | Purpose |
|---|---:|---|
| `ProjectNotFoundException` | 404 | Project is missing or not owned |
| `ProjectDeletionFailedException` | 500 | Soft deletion failed |

---

## Tasks Module

### Request Flow

```text
TaskController
→ Task DTO
→ ITaskService
→ TaskService
→ ITaskCache / RedisTaskCache
→ ITaskRepository
→ TaskRepository
→ Task Model
```

### Database Fields

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key |
| `project_id` | foreign ID | References `projects.id` |
| `title` | string | Required |
| `description` | text | Nullable |
| `priority` | tiny integer | `TaskPriority` backed enum |
| `status` | tiny integer | `TaskStatus` backed enum |
| `due_date` | datetime | Nullable |
| `overdue_notified_at` | timestamp | Nullable duplicate-notification guard |
| `created_at` | timestamp | Laravel timestamp |
| `updated_at` | timestamp | Laravel timestamp |
| `deleted_at` | timestamp | Soft delete |

### Business Rules

- A task belongs to one project.
- The project must belong to the authenticated user.
- A user can manage only tasks inside their own projects.
- Tasks use soft deletes.
- Updating a task resets `overdue_notified_at` to `null`.
- Redis caches task list and detail reads.
- Write operations invalidate the owner's task cache.

### Create Task

```http
POST /api/tasks
```

Request:

```json
{
  "project_id": 1,
  "title": "Create Tasks Module",
  "description": "Implement task APIs.",
  "priority": 3,
  "status": 1,
  "due_date": "2026-08-05 18:00:00"
}
```

Response:

```json
{
  "status": true,
  "message": "Task created successfully.",
  "data": {
    "id": 10,
    "project": {
      "id": 1,
      "name": "Electro PI Assessment"
    },
    "title": "Create Tasks Module",
    "description": "Implement task APIs.",
    "priority": {
      "value": 3,
      "label": "High"
    },
    "status": {
      "value": 1,
      "label": "Todo"
    },
    "due_date": "2026-08-05T18:00:00.000000Z",
    "is_overdue": false
  }
}
```

A missing or foreign project returns `404 Not Found`.

### List and Filter Tasks

```http
GET /api/tasks
```

Supported query parameters:

| Parameter | Type | Description |
|---|---|---|
| `project_id` | integer | Filter by project |
| `status` | integer | Filter by task status |
| `priority` | integer | Filter by priority |
| `search` | string | Search by title |
| `per_page` | integer | Page size |
| `page` | integer | Page number |

Examples:

```http
GET /api/tasks?status=1
GET /api/tasks?priority=3
GET /api/tasks?search=assessment
GET /api/tasks?project_id=5&status=2&priority=3
```

### Update Task

```http
PUT /api/tasks/{task}
```

Request:

```json
{
  "title": "Updated Task Title",
  "description": "Updated description",
  "priority": 2,
  "status": 2,
  "due_date": "2026-08-10 12:00:00"
}
```

A successful update resets:

```text
overdue_notified_at = null
```

This allows the notification process to evaluate the updated task again.

### Bulk Delete Tasks

```http
DELETE /api/tasks/bulk-delete
```

Request:

```json
{
  "task_ids": [10, 11, 12]
}
```

Only tasks owned through the authenticated user's projects are deleted.

### Task Cache Rules

| Operation | Cache action |
|---|---|
| List | Read or store task list |
| View | Read or store task detail |
| Create | Flush user's task cache |
| Update | Flush user's task cache |
| Delete | Flush user's task cache |
| Bulk delete | Flush user's task cache |
| Overdue notification update | Flush task owner's cache |

### Main Task Exceptions

| Exception | Status | Purpose |
|---|---:|---|
| `ProjectNotFoundException` | 404 | Project is missing or not owned |
| `TaskNotFoundException` | 404 | Task is missing or not owned |
| `TaskDeletionFailedException` | 500 | Task deletion failed |

---

## Dashboard Module

The Dashboard module aggregates data from the existing `projects` and `tasks` tables. It does not require a dedicated migration or model.

### Request Flow

```text
GET /api/dashboard
    ↓
Authentication Middleware
    ↓
permission:view-dashboard
    ↓
DashboardController
    ↓
IDashboardService
    ↓
DashboardService
    ↓
IDashboardRepository
    ↓
DashboardRepository
    ↓
Projects and Tasks Tables
```

### Statistics

#### Total Projects

All non-deleted projects owned by the authenticated user.

#### Active Projects

Owned projects where:

```text
status = ProjectStatus::ACTIVE
```

#### Total Tasks

All non-deleted tasks belonging to the authenticated user's projects.

#### Completed Tasks

Tasks where:

```text
status = TaskStatus::DONE
```

#### Pending Tasks

Tasks where status is:

```text
TaskStatus::TODO
TaskStatus::IN_PROGRESS
```

#### Overdue Tasks

Tasks where:

```text
due_date IS NOT NULL
AND due_date < now()
AND status != TaskStatus::DONE
```

A task may be both pending and overdue because these statistics describe different properties.

### Endpoint

```http
GET /api/dashboard
```

Success response:

```json
{
  "status": true,
  "message": "Dashboard statistics retrieved successfully.",
  "data": {
    "total_projects": 5,
    "active_projects": 3,
    "total_tasks": 20,
    "completed_tasks": 8,
    "pending_tasks": 12,
    "overdue_tasks": 4
  }
}
```

The repository uses aggregate database queries rather than loading every project and task into memory.

### Dashboard Exception

| Exception | Status | Purpose |
|---|---:|---|
| `DashboardStatisticsException` | 500 | Statistics could not be retrieved |

---

## Redis Caching

Redis is used for:

- Project list caching
- Project detail caching
- Task list caching
- Task detail caching
- User information caching where enabled
- Queue processing

### Cache Architecture

```text
Controller
    ↓
Service
    ↓
Cache Interface
    ├── Cache hit  → Return cached value
    └── Cache miss → Repository → Database
```

MySQL remains the source of truth.

### Invalidation Rules

| Operation | Action |
|---|---|
| Create | Flush the related user's cache |
| Update | Flush the related user's cache |
| Delete | Flush the related user's cache |
| Bulk delete | Flush the related user's cache when records changed |
| Overdue notification state update | Flush the task owner's cache |

Cache invalidation must occur after a successful transaction.

Incorrect:

```php
$this->projectCache->flushForUser($userId);

DB::transaction(function (): void {
    // Write operation
});
```

Correct:

```php
$result = DB::transaction(function () {
    // Write operation
});

$this->projectCache->flushForUser($userId);
```

### Service Container Bindings

Interfaces are bound to implementations in a service provider:

```php
$this->app->bind(
    IProjectRepository::class,
    ProjectRepository::class
);

$this->app->bind(
    IProjectService::class,
    ProjectService::class
);

$this->app->bind(
    IProjectCache::class,
    RedisProjectCache::class
);

$this->app->bind(
    ITaskRepository::class,
    TaskRepository::class
);

$this->app->bind(
    ITaskService::class,
    TaskService::class
);

$this->app->bind(
    ITaskCache::class,
    RedisTaskCache::class
);

$this->app->bind(
    IDashboardRepository::class,
    DashboardRepository::class
);

$this->app->bind(
    IDashboardService::class,
    DashboardService::class
);
```

---

## Queues and Overdue Notifications

A task is overdue when:

```text
due_date IS NOT NULL
AND due_date < now()
AND status != DONE
```

Duplicate notifications are prevented by requiring:

```text
overdue_notified_at IS NULL
```

After notification delivery:

```text
overdue_notified_at = current timestamp
```

### Flow

```text
Scheduler
    ↓
Console Command
    ↓
SendOverdueTaskNotificationJob
    ↓
Redis Queue
    ↓
Queue Worker
    ↓
OverdueTaskNotification
    ↓
Mail and Database Channels
```

### Scheduler Example

```php
Schedule::command('tasks:dispatch-overdue-notifications')
    ->hourly()
    ->withoutOverlapping();
```

### Run Manually

```bash
php artisan tasks:dispatch-overdue-notifications
```

### Run the Queue Worker

```bash
php artisan queue:work redis --tries=3
```

### Run the Scheduler Locally

```bash
php artisan schedule:work
```

### Production Cron

```cron
* * * * * cd /path/to/electro-pi-assessment && php artisan schedule:run >> /dev/null 2>&1
```

---

## API Response Format

### Success

```json
{
  "status": true,
  "message": "Operation completed successfully.",
  "data": {}
}
```

### Business Error

```json
{
  "status": false,
  "message": "Operation failed."
}
```

### Validation Error

```http
422 Unprocessable Entity
```

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": [
      "Validation message."
    ]
  }
}
```

### Common Status Codes

| Status | Meaning |
|---:|---|
| 200 | Successful operation |
| 201 | Resource created |
| 401 | Unauthenticated or invalid credentials |
| 403 | Missing permission |
| 404 | Resource missing or not owned |
| 422 | Validation or business input failure |
| 500 | Internal business operation failure |

---

## Testing

The project includes unit and feature tests.

### Unit Tests

Unit tests isolate services and DTOs by mocking repository and cache interfaces.

They verify:

- DTO mapping
- Repository calls
- Business rules
- Domain exceptions
- Ownership handling
- Cache reads
- Cache invalidation
- Dashboard service results

### Feature Tests

Feature tests verify the complete request lifecycle:

```text
Route
→ Middleware
→ Form Request
→ Controller
→ DTO
→ Service
→ Repository
→ Database
→ Resource
→ JSON Response
```

They cover:

- Authentication
- Permissions
- Ownership
- Validation
- Pagination
- Search and filtering
- Soft deletes
- Bulk deletion
- Database changes
- Queue and notification behavior
- Dashboard aggregation

### Run All Tests

```bash
php artisan test
```

### Authentication Tests

```bash
php artisan test tests/Unit/Modules/Authentication
php artisan test tests/Feature/Modules/Authentication
```

### Project Tests

```bash
php artisan test tests/Unit/Modules/Projects
php artisan test tests/Feature/Modules/Projects
```

### Task Tests

```bash
php artisan test tests/Unit/Modules/Tasks
php artisan test tests/Feature/Modules/Tasks
```

### Dashboard Tests

```bash
php artisan test tests/Unit/Modules/Dashboard
php artisan test tests/Feature/Modules/Dashboard
```

### Filtered Tests

```bash
php artisan test --filter=Authentication
php artisan test --filter=Project
php artisan test --filter=Task
php artisan test --filter=Dashboard
```

### Redis Test Isolation

`RefreshDatabase` resets the database but does not clear Redis.

Use a dedicated Redis cache database for tests:

```xml
<env name="CACHE_STORE" value="redis"/>
<env name="REDIS_CLIENT" value="predis"/>
<env name="REDIS_HOST" value="127.0.0.1"/>
<env name="REDIS_PORT" value="6379"/>
<env name="REDIS_CACHE_DB" value="15"/>
```

Never point automated tests to the production Redis database.

Task feature tests may mock `ITaskCache` through a shared `MocksTaskCache` concern so Redis is not required for those tests.

---

## Code Quality

Run Laravel Pint.

Linux or Git Bash:

```bash
./vendor/bin/pint
```

Windows:

```cmd
vendor\bin\pint
```

---

## Useful Commands

### Inspect Routes

```bash
php artisan route:list
```

### Inspect the Application

```bash
php artisan about
```

### Clear Laravel Caches

```bash
php artisan optimize:clear
```

### Reset Spatie Permission Cache

```bash
php artisan permission:cache-reset
```

### Check Redis

```bash
redis-cli ping
```

### Open the Redis Cache Database

```bash
redis-cli -n 1
```

### Check Scheduled Tasks

```bash
php artisan schedule:list
```

### Check Failed Queue Jobs

```bash
php artisan queue:failed
```

### Retry Failed Queue Jobs

```bash
php artisan queue:retry all
```

### Restart Queue Workers

```bash
php artisan queue:restart
```

---

## Security Rules

- Never accept `user_id` from project or task request input.
- Obtain the user ID from the authenticated Sanctum user.
- Scope every project query by `user_id`.
- Scope every task query through the project's owner.
- Use both permission and ownership checks.
- Return `404` for foreign resources to prevent information leakage.
- Validate enum fields against their backed enum classes.
- Hash passwords through Laravel's hashing support.
- Return generic invalid-credential responses.
- Use Form Requests for validation.
- Use API Resources instead of exposing Eloquent models directly.
- Keep database queries out of controllers.
- Keep Redis logic out of repositories.
- Use transactions for write operations.
- Invalidate cache only after successful commits.
- Use separate Redis databases for application and tests.
- Protect all non-public routes with `auth:sanctum`.

---

## Git Workflow

Recommended lightweight Git Flow:

```text
main
develop
feature/*
release/*
hotfix/*
```

Example feature branches:

```text
feature/authentication
feature/projects-module
feature/tasks-module
feature/overdue-task-notifications
feature/dashboard-endpoint
feature/tests-and-documentation
```

Features are created from `develop`, tested, merged through a Pull Request or `--no-ff`, and deleted after merge.

Suggested release flow:

```text
release/1.0.0
    ↓
main
    ↓
v1.0.0 tag
```

---

## Postman Workspace

The API collection and examples are available in the Electro Pi assessment workspace:

https://www.postman.com/martian-shadow-736975/workspace/electro-pi-assessment

Use it to test:

- Registration and login
- Bearer-token authentication
- Project CRUD and bulk deletion
- Task CRUD, filters, and bulk deletion
- Dashboard statistics

---

## cURL Examples

### Register

```bash
curl --request POST \
  --url http://localhost:8000/api/auth/register \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --data '{
    "name": "Gamal Sobhy",
    "email": "gamal@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login

```bash
curl --request POST \
  --url http://localhost:8000/api/auth/login \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --data '{
    "email": "gamal@example.com",
    "password": "password123"
  }'
```

### Create Project

```bash
curl --request POST \
  --url http://localhost:8000/api/projects \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  --header 'Content-Type: application/json' \
  --data '{
    "name": "Electro PI Assessment",
    "description": "Laravel API technical assessment",
    "status": 1
  }'
```

### Create Task

```bash
curl --request POST \
  --url http://localhost:8000/api/tasks \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  --header 'Content-Type: application/json' \
  --data '{
    "project_id": 1,
    "title": "Create Tasks Module",
    "description": "Implement task APIs.",
    "priority": 3,
    "status": 1,
    "due_date": "2026-08-05 18:00:00"
  }'
```

### Get Dashboard Statistics

```bash
curl --request GET \
  --url http://localhost:8000/api/dashboard \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

---

## Additional Documentation

The repository can keep the detailed module documents beside this consolidated README:

```text
AUTHENTICATION_MODULE.md
PROJECT-MODULE.md
TASKS-MODULE.md
DASHBOARD.md
ARCHITECTURE.md
GETTING_STARTED.md
```

This `README.md` serves as the main project entry point, while the module documents provide deeper implementation examples, contracts, exceptions, cache behavior, route definitions, and test cases.

---

## Final Notes

- The Dashboard module has no dedicated table or model.
- Projects and tasks use soft deletes.
- Database cascade rules apply to physical deletion, not normal soft deletion.
- Permissions and ownership solve different authorization concerns.
- Redis improves repeated reads but does not replace MySQL as the source of truth.
- The overdue notification flow requires both a running queue worker and scheduler.
- Keep route prefixes and response shapes consistent with the final application implementation.
