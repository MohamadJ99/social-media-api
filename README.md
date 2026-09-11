# MJ Social Media App — Backend

> 🚧 This project is currently under development.

A RESTful API backend for the **MJ Social Media App**, built with **Laravel**, **PHP**, **MySQL**, and **Laravel Sanctum**.

This repository contains the backend API consumed by the separate Next.js frontend application.

## Tech Stack

* Laravel
* PHP
* MySQL
* Laravel Sanctum
* REST API
* Eloquent ORM
* Git & GitHub

## Features

* Authentication & Authorization
* Token-based authentication with Laravel Sanctum
* User Profiles
* Profile Updates
* Avatar & Cover Image Uploads
* Posts CRUD
* Image Uploads
* Likes
* Comments & Replies
* Friend Requests
* Friends System
* Policies & Authorization
* Form Request Validation
* API Rate Limiting
* Pagination
* Infinite Scroll API Support

## API Endpoints

### Authentication

```text
POST /api/register
POST /api/login
POST /api/logout
GET  /api/user
```

### Posts

```text
GET    /api/posts
POST   /api/posts
PATCH  /api/posts/{post}
DELETE /api/posts/{post}
```

### Profile

```text
GET   /api/me
PATCH /api/me

POST /api/me/avatar
POST /api/me/cover

GET /api/users/{id}
```

### Friends

```text
GET    /api/friends
POST   /api/users/{user}/friend

POST   /api/friendships/{friendship}/accept
POST   /api/friendships/{friendship}/reject

DELETE /api/friendships/{friendship}/cancel
DELETE /api/friendships/{friendship}
```

### Comments

```text
GET    /api/posts/{post}/comments
POST   /api/posts/{post}/comments

PATCH  /api/comments/{comment}
DELETE /api/comments/{comment}
```

### Likes

```text
POST   /api/posts/{post}/like
DELETE /api/posts/{post}/like

POST   /api/comments/{comment}/like
DELETE /api/comments/{comment}/like
```

## Frontend

The API is consumed by a separate Next.js frontend application.

**Frontend Repository:**
https://github.com/MohamadJ99/Social-Media-App

## Getting Started

### 1. Clone the repository

```bash
git clone [backend-repository-url]
cd [project-folder]
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure your database connection in `.env`.

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Create storage link

```bash
php artisan storage:link
```

### 6. Start the development server

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000
```

## Architecture

The backend follows a structured Laravel API architecture using:

* **Controllers** — Handle HTTP requests and responses
* **Form Requests** — Handle request validation
* **Services** — Handle business logic
* **Policies** — Handle authorization
* **API Resources** — Provide consistent API responses
* **Eloquent ORM** — Handle database relationships and queries
* **Sanctum** — Handle API token authentication

## Project Structure

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
│
├── Models/
├── Policies/
└── Services/

routes/
└── api.php

database/
├── migrations/
└── seeders/
```

## Status

🚧 **Ongoing Project**

The API is actively being developed alongside the Next.js frontend.

## Author

**Mohammad Jawad Al-Shanableh**

GitHub: `MohamadJ99`
