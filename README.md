# Gym Planner API (Laravel)

A RESTful API for a personal training platform where **trainers** build workout plans, assign them to **clients**, and track execution and progress. Pairs with the React frontend.

**Frontend repo:** https://github.com/Goferov/gym-planner-trainer

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Database Design and Structure](#database-design-and-structure)
- [Installation](#installation)
- [Running the app](#running-the-app)
- [Testing](#testing)
- [API Endpoints](#api-endpoints)
- [Authorization & Policies](#authorization--policies)
- [Media Uploads](#media-uploads)
- [Scheduled Tasks](#scheduled-tasks)
- [Conventions](#conventions)

---

## Features

**Trainer**
- Register & login (JWT), dashboard metrics.
- CRUD for **Exercises** (with muscle groups, optional media: image/GIF/video URL).
- CRUD for **Clients** (notes, address, phone).
- CRUD for **Plans** (weeks → days → day-exercises).
- Assign / unassign plans to clients; activate/deactivate.
- View client activity and history (exercise logs, difficulties).
- Auto‑progress & completion tracking per plan.

**Client**
- Login (JWT).
- See today’s workout (or next) + missed workouts list.
- Start day → logs are created.
- Mark individual exercises as **completed** or **report difficulty** (1–5 + optional comment).
- View plan progress & history.

---

## Tech Stack

- **Laravel 11**, PHP 8.2+
- **PostgreSQL** (prod/dev) or **SQLite** (testing)
- **JWT Auth** (`tymon/jwt-auth`) as API guard (`auth:api`)
- Eloquent, Policies, Form Requests
- PHPUnit


---

## Database Design and Structure

### Core tables

- `users`
    - `id`, `name`, `email`, `password`, `role` (`trainer`|`user`), `trainer_id` (FK to users), `last_login_at`, `notes`, `address`, `phone`, timestamps
- `muscle_groups`
    - `id`, `name`
- `exercises`
    - `id`, `name`, `description`, `video_url` (nullable), `media_type` (`image`|`gif`|`video_url`), `media_path` (nullable), `user_id` (FK; **system** exercises belong to a seeded system user), timestamps
- `exercise_muscle_group` (pivot)
    - `exercise_id`, `muscle_group_id`
- `plans`
    - `id`, `trainer_id` (FK users), `name`, `description`, `duration_weeks`, timestamps
- `plan_days`
    - `id`, `plan_id` (FK), `week_number`, `day_number`, `description`
- `plan_day_exercises`
    - `id`, `plan_day_id` (FK), `exercise_id` (FK), `sets`, `reps`, `rest_time`, `tempo`, `notes`
- `plan_user` (assignment pivot **with state**)
    - `id`, `plan_id`, `user_id`, `assigned_at`, `started_at`, `completed_at`, `active` (bool), timestamps
- `exercise_logs`
    - `id`, `plan_user_id` (FK), `plan_day_exercise_id` (FK), `date`, `completed` (bool), `difficulty_reported` (1–5, nullable), `difficulty_comment` (text, nullable), timestamps
- `plan_day_user` (materialized schedule per assignment)
    - `id`, `plan_user_id` (FK), `plan_day_id` (FK), `scheduled_date` (date), `status` (`pending`|`completed`|`missed`), `completed_at` (nullable), timestamps


---

## Installation

### Prerequisites
- PHP 8.2+ with extensions required by Laravel
- Composer
- PostgreSQL (or SQLite for quick start)
- Node.js (optional, for dev tooling)

### Steps

1. **Clone**
   ```bash
   git clone <your-backend-repo-url>
   cd backend
   ```

2. **Env**
   ```bash
   cp .env.example .env
   # Edit DB_*, APP_URL, MAIL_*, etc.
   ```

3. **Install deps**
   ```bash
   composer install
   ```

4. **Generate app key & JWT secret**
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

5. **Migrate (+ seed optional)**
   ```bash
   php artisan migrate
   # php artisan db:seed
   ```

6. **Storage symlink (for media)**
   ```bash
   php artisan storage:link
   ```

7. **Serve**
   ```bash
   php artisan serve
   # API base: http://127.0.0.1:8000/api
   ```

---

## Running the app

- **Auth guard**: `auth:api` (JWT). Include header: `Authorization: Bearer <token>`.
- Default rate limiting: `throttle:api` on sensitive routes.

---

## Testing

- **PHPUnit** / **Pest**
    - Quick setup with SQLite in-memory:
        - `.env.testing`:
          ```dotenv
          APP_ENV=testing
          DB_CONNECTION=sqlite
          DB_DATABASE=:memory:
          DB_FOREIGN_KEYS=true
          ```
        - run: `php artisan test`

- **Mail testing**
    - Use Mailhog / Mailtrap, or `MAIL_MAILER=log` to log to `storage/logs/laravel.log`.

---

## API Endpoints

### Auth
```
POST   /api/register           # {name,email,password,password_confirmation,role='trainer'}
POST   /api/login              # {email,password} -> {token,user}
POST   /api/logout
GET    /api/getUser            # current user
```

### Exercises
```
GET    /api/exercises?muscle_group_id=...
POST   /api/exercises          # name, description?, video_url?, muscle_group_ids[]?, media_file?
GET    /api/exercises/{id}
PUT    /api/exercises/{id}     # same as POST (trainer can edit only own)
DELETE /api/exercises/{id}     # only own
GET    /api/exercises/muscle-groups
```

### Clients (trainer only)
```
GET    /api/clients
POST   /api/clients            # name,email,password, notes?, address?, phone?
GET    /api/clients/{id}
PUT    /api/clients/{id}
DELETE /api/clients/{id}
```

### Plans (trainer only)
```
GET    /api/plans
POST   /api/plans              # name,description?,duration_weeks?, plan_days[...days.exercises[...] ]
GET    /api/plans/{id}
PUT    /api/plans/{id}         # full replace of days & exercises
DELETE /api/plans/{id}

POST   /api/plans/{plan}/assign    # {user_ids:[...]} (only own clients; skips already assigned)
DELETE /api/plans/{plan}/unassign  # {user_ids:[...]} -> set inactive
```

### PlanUser (assigned plans & daily workflow)
```
GET    /api/plan-user                  # client: own; trainer: ?user_id=
POST   /api/plan-user/{planUser}/start

# Daily
GET    /api/plan-user/{planUser}/day          # today’s (or next/rest) with pending days
POST   /api/plan-user/{planUser}/day/start    # materialize logs for that day
GET    /api/plan-user/{planUser}/day/summary  # {date?=today}

# Aggregates
GET    /api/plan-user/{planUser}              # show (with progress)
GET    /api/plan-user/{planUser}/history
```

### Exercise Logs (client)
```
POST   /api/exercise-logs/{log}/mark-complete       # {completed?:boolean}
POST   /api/exercise-logs/{log}/report-difficulty   # {difficulty_reported:1..5, difficulty_comment?}
```

### Account Settings
```
PUT    /api/me/profile     # {name?}
PUT    /api/me/password    # {current_password,new_password,new_password_confirmation}
```

> Most routes are protected by policies: trainers see only their data; clients see only their own assignments.

---

## Authorization & Policies

- **ExercisePolicy** – trainer can create/edit/delete only own exercises; system ones are read-only.
- **ClientPolicy (User policy for role=user)** – trainer can CRUD only own clients.
- **PlanPolicy** – trainer can CRUD only own plans.
- **PlanUserPolicy** – client/trainer access restricted to owner/assignee; `start` can be called only once by the owner.
- **ExerciseLogPolicy** – client can modify only own logs.

---

## Media Uploads

- Endpoint accepts `media_file` (image/GIF) via `multipart/form-data`.
- Stored under `storage/app/public/exercises/...` and exposed via `storage:link` at `/storage/...`.
- `media_type` guides the frontend to show image/gif vs `video_url` embed.

---

## Scheduled Tasks

- Daily job (optional) to mark missed days:
  ```php
  PlanDayUser::where('status','pending')
    ->whereDate('scheduled_date','<', today())
    ->update(['status'=>'missed']);
  ```

Use Laravel’s scheduler (`app/Console/Kernel.php`).

---

## Conventions

- Use **UTC** dates in API; client formats with locale.
- Use **week_number** (1‑based) and **day_number** (1‑based) in plan definition.
- Prefer **materialized** schedule (`plan_day_user`) to avoid runtime date math.

---

