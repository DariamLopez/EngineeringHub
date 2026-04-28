# EngineeringHub API

A RESTful API built with **Laravel 12** that serves as a backend platform for managing engineering projects, their structural breakdown (domains, modules), engineering artifacts, and a full audit trail of every action performed across the system.

---

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Domain Model](#domain-model)
- [Roles & Permissions](#roles--permissions)
- [API Endpoints](#api-endpoints)
- [Local Setup](#local-setup)
- [Running Tests](#running-tests)
- [Deployment (Railway)](#deployment-railway)
- [Environment Variables Reference](#environment-variables-reference)
- [Possible Improvements](#possible-improvements)

---

## Overview

EngineeringHub is a structured project management API for engineering teams. It models the lifecycle of a software project from initial discovery through delivery, providing:

- **Project management** with status tracking (`draft → discovery → execution → delivered`)
- **Domain & Module breakdown** — hierarchical decomposition of projects into bounded domains and their modules
- **Artifacts** — typed engineering documents (strategic alignment, big picture, domain breakdown, module matrix, module engineering, system architecture, phase scope) stored as structured JSON and enriched dynamically at read time
- **Audit Trail** — immutable log of every create, update, status change, and delete across the system
- **Role-Based Access Control** using `spatie/laravel-permission` with four built-in roles

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.4 |
| Framework | Laravel 12 |
| Auth | Laravel Sanctum (token-based, 1-hour expiry) |
| Authorization | Spatie Laravel Permission 7.x (RBAC) |
| Database | MySQL 8 |
| Testing | PHPUnit 11 |
| Deployment | Railway (Nixpacks) |

---

## Architecture

The application follows a standard **Laravel MVC** structure with the following layers:

```
routes/api.php          → Route definitions (all under auth:sanctum middleware)
Http/Controllers/       → Request handling, authorization checks, response formatting
Http/Requests/          → Form Request validation (one class per operation)
Models/                 → Eloquent models with relationships and custom accessors
Policies/               → Authorization logic per resource/role
Enums/                  → Typed PHP 8.1+ backed enums for statuses and types
database/migrations/    → Schema definitions
database/seeders/       → Role, permission, and default user seeding
```

Every mutating operation (create, update, delete, status change) dispatches a record to the `audit_trails` table via `AuditTrail::logAction()`, storing the before and after JSON snapshots of the entity.

---

## Domain Model

```
Users
 └── has many Projects (created_by)
 └── belongs to many Projects (project_user pivot)

Projects
 ├── has many Domains
 ├── has many Modules
 └── has many Artifacts

Domains
 ├── belongs to Project
 ├── belongs to User (owner)
 └── has many Modules

Modules
 ├── belongs to Domain
 ├── belongs to Project
 └── self-referencing dependencies (stored as JSON array of IDs, hydrated to full objects on read)

Artifacts
 ├── belongs to Project
 ├── belongs to User (owner)
 └── content_json — dynamic structure depending on type (enriched at read with domain/module data)

AuditTrails
 └── polymorphic entity (project | artifact | module)
```

### Enums

| Enum | Values |
|---|---|
| `ProjectStatusEnum` | `draft`, `discovery`, `execution`, `delivered` |
| `ModuleStatusEnum` | `draft`, `validated`, `ready_for_build` |
| `ArtifactStatusEnum` | `not_started`, `in_progress`, `blocked`, `done` |
| `ArtifactTypeEnum` | `strategic_alignment`, `big_picture`, `domain_breakdown`, `module_matrix`, `module_engineering`, `system_architecture`, `phase_scope` |
| `AuditTrailsActionsEnum` | `created`, `updated`, `status_changed`, `validated`, `completed`, `deleted` |

---

## Roles & Permissions

Roles and permissions are seeded automatically by `PermissionSeeder`.

| Permission | admin | pm | engineer | viewer |
|---|:---:|:---:|:---:|:---:|
| `view_users` | ✓ | | | |
| `edit_users` | ✓ | | | |
| `view_roles` | ✓ | | | |
| `edit_roles` | ✓ | | | |
| `view_projects` | ✓ | ✓ | ✓ | ✓ |
| `edit_projects` | ✓ | ✓ | | |
| `view_artifacts` | ✓ | ✓ | ✓ | ✓ |
| `edit_artifacts` | ✓ | ✓ | | |
| `view_modules` | ✓ | ✓ | ✓ | ✓ |
| `edit_modules` | ✓ | ✓ | ✓ | |
| `view_audit` | ✓ | | | |

**Default seeded users:**

| Email | Password | Role |
|---|---|---|
| `admin@example.com` | `admin` | admin |
| `pm@example.com` | `pm` | pm |
| `engineer@example.com` | `engineer` | engineer |

> Change these credentials immediately in any non-development environment.

---

## API Endpoints

All endpoints (except `POST /api/v1/login`) require a Sanctum Bearer token.

### Authentication

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/v1/login` | Obtain a token (1-hour expiry) |
| `POST` | `/api/v1/logout` | Revoke current token |
| `POST` | `/api/v1/register` | Create a new user (admin only) |
| `GET` | `/api/v1/users` | List all users |
| `DELETE` | `/api/v1/users/{id}` | Delete a user |
| `GET` | `/api/v1/roles` | List all roles |

### Projects

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/projects` | List projects (filterable by `status`, `client_name`, `is_archived`, `created_by_id`) |
| `POST` | `/api/v1/projects` | Create a project |
| `GET` | `/api/v1/projects/{id}` | Get a project with domains, modules, artifacts |
| `PUT` | `/api/v1/projects/{id}` | Update a project |
| `DELETE` | `/api/v1/projects/{id}` | Delete a project |

### Domains

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/domains?project_id=` | List domains for a project |
| `POST` | `/api/v1/domains` | Create a domain |
| `POST` | `/api/v1/domains/massive` | Bulk create domains |
| `PUT` | `/api/v1/domains/{id}` | Update a domain |
| `PUT` | `/api/v1/domains/massive` | Bulk update domains |
| `DELETE` | `/api/v1/domains/{id}` | Delete a domain |
| `DELETE` | `/api/v1/domains/massive` | Bulk delete domains |

### Modules

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/modules` | List modules |
| `POST` | `/api/v1/modules` | Create a module |
| `POST` | `/api/v1/modules/massive` | Bulk create modules |
| `PUT` | `/api/v1/modules/{id}` | Update a module |
| `PUT` | `/api/v1/modules/massive` | Bulk update modules |
| `DELETE` | `/api/v1/modules/{id}` | Delete a module |
| `DELETE` | `/api/v1/modules/massive` | Bulk delete modules |

### Artifacts

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/artifacts?project_id=` | List artifacts (filterable by `type`, `status`, `owner_user_id`) |
| `POST` | `/api/v1/artifacts` | Create an artifact |
| `GET` | `/api/v1/artifacts/{id}` | Get an artifact (content_json enriched at read time) |
| `PUT` | `/api/v1/artifacts/{id}` | Update an artifact |
| `DELETE` | `/api/v1/artifacts/{id}` | Delete an artifact |

### Audit Trail

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/audit-trails` | List all audit trail entries |

All list endpoints support `order_by`, `order_dir`, and `per_page` query parameters for sorting and pagination.

---

## Local Setup

### Prerequisites

- PHP >= 8.4
- Composer
- MySQL 8 (or compatible)
- Node.js >= 18 & npm

### Steps

**1. Clone the repository**

```bash
git clone https://github.com/DariamLopez/EngineeringHubFront.git
cd EngineeringHub
```

**2. Install dependencies**

```bash
composer install
npm install
```

**3. Configure environment**

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=engineeringhub
DB_USERNAME=root
DB_PASSWORD=your_password
```

**4. Run migrations and seed**

```bash
php artisan migrate
php artisan db:seed
```

This creates all tables, seeds the four roles (`admin`, `pm`, `engineer`, `viewer`) with their permissions, and creates three default users (admin, pm, engineer). No default user is created for the `viewer` role.

**5. Start the development server**

```bash
composer run dev
```

This concurrently runs the PHP server, queue worker, log watcher, and Vite dev server.

Or individually:

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api/v1`.

---

## Running Tests

```bash
composer run test
```

Or directly:

```bash
php artisan test
```

Feature tests cover authentication, projects, domains, modules, and artifacts. The test suite uses an in-memory SQLite database by default (configure `phpunit.xml` as needed).

---

## Deployment (Railway)

The project includes a `nixpacks.toml` that configures Railway to use PHP 8.4 and automatically run migrations on startup.

### Required Environment Variables in Railway

| Variable | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | *(generate with `php artisan key:generate --show`)* |
| `APP_URL` | Your Railway public URL |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | MySQL service public host |
| `DB_PORT` | MySQL service public port |
| `DB_DATABASE` | `railway` |
| `DB_USERNAME` | `root` |
| `DB_PASSWORD` | *(from Railway MySQL service variables)* |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |

### Steps

1. Create a new Railway project
2. Add a **MySQL** service to the project
3. Add your Laravel app (connect your GitHub repo)
4. Set all environment variables listed above using the MySQL service's connection values
5. Deploy — migrations run automatically on startup via `nixpacks.toml`
6. Run the seeder once manually via Railway's shell: `php artisan db:seed`

---

## Environment Variables Reference

| Variable | Description | Default |
|---|---|---|
| `APP_NAME` | Application name | `EngineeringHub` |
| `APP_ENV` | Environment (`local`/`production`) | `local` |
| `APP_KEY` | Encryption key (required) | — |
| `APP_DEBUG` | Show detailed errors | `true` |
| `APP_URL` | Base URL | `http://localhost` |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Database name | `laravel` |
| `DB_USERNAME` | Database user | `root` |
| `DB_PASSWORD` | Database password | — |
| `SESSION_DRIVER` | Session storage driver | `database` |
| `QUEUE_CONNECTION` | Queue driver | `database` |
| `CACHE_STORE` | Cache driver | `database` |
| `MIN_VALIDATED_MODULES` | Min validated modules to mark `system_architecture` artifact as done | `3` |

---

## Possible Improvements

### Security & Authentication
- **Token refresh mechanism** — Current tokens expire after 1 hour with no refresh flow; implement a refresh endpoint or sliding expiration
- **Email verification** — The code has a commented-out email verification check; enable it for production
- **Rate limiting** — Add `throttle` middleware to login and register endpoints to prevent brute-force attacks
- **HTTPS enforcement** — Add `ForceHttps` middleware or configure it at the infrastructure level

### Architecture & Code Quality
- **API Resources / Transformers** — Replace raw `response()->json($model)` calls with dedicated `JsonResource` classes to decouple the API contract from the Eloquent model shape
- **Service layer** — Extract business logic (e.g., artifact enrichment, audit trail dispatching) from controllers into dedicated Service classes to improve testability and separation of concerns
- **Repository pattern** — Abstracting DB queries behind repositories would make swapping data sources easier and controllers thinner
- **Event/Listener pattern for Audit Trail** — Instead of calling `AuditTrail::logAction()` directly in every controller method, dispatch domain events and handle audit logging in a dedicated listener
- **DTO classes** — Use Data Transfer Objects for validated request data instead of passing raw arrays

### API Design
- ~~**API versioning** — Prefix routes with `/v1/` to allow non-breaking evolution of the API~~ *(implemented)*
- **Consistent pagination** — Enforce pagination by default on all collection endpoints; currently `per_page` is optional which can return unbounded result sets
- **HATEOAS / hypermedia links** — Include related resource links in responses
- **Standard error envelope** — Standardize all error responses with a consistent JSON structure (`{ "error": { "code": ..., "message": ... } }`)

### Features
- **File attachments on Artifacts** — Allow uploading files (PDFs, diagrams) linked to artifacts using Laravel Storage
- **Project member assignment** — The `project_user` pivot table and relationship exist but there are no endpoints to manage project membership
- **Soft delete recovery** — Projects have `softDeletes()` but there is no restore endpoint
- **Notifications** — Email/webhook notifications when artifact status changes or a module reaches `ready_for_build`
### Testing
- **Increase coverage** — Modules and artifact type-specific logic (enrichment accessors, `system_architecture` completion rules) lack dedicated unit tests
- **Contract/Integration tests** — Add tests that verify the exact JSON shape of each endpoint response
- **Factory coverage** — Ensure all factories produce valid data for all enum values to avoid flaky tests

### Infrastructure & DevOps
- **Queue worker as a separate Railway service** — The current `nixpacks.toml` only starts `php artisan serve`; there is no queue worker running in Railway, meaning any queued jobs (e.g. future notifications) would silently pile up unprocessed. Add a separate worker service or use a managed queue provider
- **Horizon** — Replace the basic queue worker with Laravel Horizon for real-time queue monitoring
- **Telescope** — Add Laravel Telescope in non-production environments for request/query/log inspection
- **Database read replica** — For high-traffic scenarios, configure a read replica for reporting queries (audit trail, listing endpoints)
- **CI/CD pipeline** — Add a GitHub Actions workflow to run the test suite on every pull request before merging

---

## License

MIT
