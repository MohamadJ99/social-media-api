# MJ Social Media App — Backend

> 🚧 This project is currently under active development.

A RESTful API backend for the **MJ Social Media App**, built with **Laravel**, **PHP**, **MySQL**, and **Laravel Sanctum**.

The backend provides authentication, user profiles, posts, friendships, notifications, real-time messaging, stories, media management, and AI-powered post generation for the separate Next.js frontend application.

---

## Tech Stack

- **Laravel**
- **PHP**
- **MySQL / MariaDB**
- **Laravel Sanctum**
- **Laravel Reverb**
- **Eloquent ORM**
- **REST API**
- **OpenRouter API**
- **Git & GitHub**

---

## Features

### Authentication

- User registration
- User login
- User logout
- Token-based authentication with Laravel Sanctum
- Authenticated user endpoint
- Protected API routes
- Forgot password
- Reset password
- Custom password reset frontend URL
- Authentication rate limiting

### User Profiles

- View user profiles
- Update own profile
- Avatar upload
- Cover image upload
- Profile statistics
- User media data
- Authorization for protected profile actions

### Posts

- Create posts
- View posts
- Update posts
- Delete posts
- Image and media support
- Post ownership authorization
- Pagination
- Infinite-scroll API support
- Dedicated post creation rate limiting

### Likes

- Like posts
- Unlike posts
- Like comments
- Unlike comments
- User-specific like states

### Comments & Replies

- Create comments
- Update comments
- Delete comments
- Replies
- Comment authorization
- Comment likes

### Friendships

- Send friend requests
- Accept friend requests
- Reject friend requests
- Cancel outgoing requests
- Remove friends
- Friends list
- Incoming friend requests
- Outgoing friend requests

### Notifications

- User notifications
- Unread notification count
- Mark notifications as read
- Notification actor information
- Friend-request notifications
- Real-time notification updates

### Real-Time Messaging

- User conversations
- Private messages
- Create conversations
- Send messages
- Edit messages
- Delete messages
- Read states
- Unread message tracking
- Real-time message delivery
- Private broadcasting channels
- Laravel Reverb integration

### Stories

- Create image stories
- Create video stories
- Optional story captions
- Public visibility
- Friends-only visibility
- Multiple active stories per user
- 24-hour story expiration
- Automatic expired-story cleanup
- Story feed grouping by user
- Current user's stories prioritized
- Seen / unseen story states
- Story view tracking
- Story viewers list
- Viewer timestamps
- Owner-only viewers access
- Delete own stories
- Story authorization through Laravel Policies
- Video metadata processing
- Video duration validation
- Media cleanup

### AI Post Generation

- AI-powered social media post generation
- Generate posts from a user-provided topic
- Supported writing tones:
  - Professional
  - Casual
  - Friendly
  - Educational
- Supported languages:
  - English
  - Arabic
- AI requests processed through a dedicated service layer
- OpenRouter API integration
- Secure server-side API key handling
- AI provider error handling
- Dedicated AI rate limiting

---

## API Endpoints

All protected routes use Laravel Sanctum authentication.

### Authentication

```text
POST /api/register
POST /api/login
POST /api/logout
GET  /api/user
```

### Password Reset

```text
POST /api/forgot-password
POST /api/reset-password
```

### Profile

```text
GET   /api/me
PATCH /api/me

POST /api/me/avatar
POST /api/me/cover

GET /api/users/{id}
```

### Posts

```text
GET    /api/posts
POST   /api/posts
GET    /api/posts/{post}
PATCH  /api/posts/{post}
DELETE /api/posts/{post}
```

### Likes

```text
POST   /api/posts/{post}/like
DELETE /api/posts/{post}/like

POST   /api/comments/{comment}/like
DELETE /api/comments/{comment}/like
```

### Comments

```text
GET    /api/posts/{post}/comments
POST   /api/posts/{post}/comments

PATCH  /api/comments/{comment}
DELETE /api/comments/{comment}
```

### Friends

```text
GET  /api/friends

POST /api/users/{user}/friend

POST /api/friendships/{friendship}/accept
POST /api/friendships/{friendship}/reject

DELETE /api/friendships/{friendship}/cancel
DELETE /api/friendships/{friendship}
```

### Conversations & Messages

```text
GET  /api/conversations
POST /api/conversations

GET  /api/conversations/{conversation}/messages
POST /api/conversations/{conversation}/messages
```

### Stories

```text
GET    /api/stories
POST   /api/stories

POST   /api/stories/{story}/view
GET    /api/stories/{story}/viewers

DELETE /api/stories/{story}
```

### AI Post Generation

```text
POST /api/ai/posts/generate
```

Example request:

```json
{
  "topic": "Building REST APIs with Laravel",
  "tone": "professional",
  "language": "english"
}
```

Example response:

```json
{
  "content": "Generated social media post..."
}
```

---

## Authentication

Protected routes require a Sanctum Bearer token:

```http
Authorization: Bearer YOUR_TOKEN
```

Example:

```http
GET /api/user

Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

---

## Backend Architecture

The project follows a structured Laravel API architecture with separation of responsibilities.

```text
HTTP Request
     ↓
Route
     ↓
Middleware
     ↓
Form Request
     ↓
Controller
     ↓
Service / Business Logic
     ↓
Model / Eloquent ORM
     ↓
API Response
```

The backend uses:

- **Controllers** — Handle HTTP requests and responses
- **Form Requests** — Handle validation
- **Services** — Encapsulate business and external API logic
- **Policies** — Handle authorization
- **API Resources** — Provide structured API responses
- **Models** — Manage data and relationships
- **Events / Broadcasting** — Power real-time functionality
- **Rate Limiters** — Protect authentication, posts, and AI endpoints
- **Sanctum** — Handle API token authentication

---

## AI Architecture

AI provider credentials are kept entirely on the backend and are never exposed to the frontend.

```text
Next.js Frontend
       ↓
Laravel API
       ↓
AIPostService
       ↓
OpenRouter API
       ↓
AI Model
```

The frontend sends only the requested topic, tone, and language to Laravel.

Laravel is responsible for:

- Validating the request
- Authenticating the user
- Applying rate limits
- Building the AI prompt
- Calling OpenRouter
- Handling provider errors
- Returning the generated content

---

## Stories Architecture

Stories are handled entirely through the Laravel API.

```text
Story Request
      ↓
StoryController
      ↓
Validation
      ↓
StoryPolicy
      ↓
Media Storage
      ↓
Story Model
```

Stories automatically expire after **24 hours**.

The backend also handles:

- Story visibility
- View tracking
- Seen / unseen state
- Story viewers
- Media cleanup
- Expired-story cleanup

---

## Real-Time Architecture

Real-time messaging and notifications are powered by Laravel broadcasting.

```text
Laravel Event
      ↓
Broadcast
      ↓
Laravel Reverb
      ↓
Private Channel
      ↓
Next.js Client
```

Private channels ensure that user-specific real-time events are only received by authorized users.

---

## Rate Limiting

The API uses Laravel rate limiters to protect application resources.

Separate limits are used for areas such as:

- General authenticated API requests
- Authentication
- Password reset requests
- Post creation
- AI post generation

This prevents abuse while allowing normal social-media usage.

---

## Project Structure

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── API/
│   │   ├── AI/
│   │   └── ...
│   │
│   ├── Requests/
│   │   ├── AI/
│   │   └── ...
│   │
│   └── Resources/
│
├── Models/
├── Policies/
├── Services/
│   └── AI/
│
└── Providers/

routes/
└── api.php

database/
├── migrations/
├── factories/
└── seeders/
```

---

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/MohamadJ99/social-media-api.git
cd social-media-api
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

Generate the Laravel application key:

```bash
php artisan key:generate
```

Configure your database connection in `.env`.

Example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_media
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Configure the frontend URL

Configure the Next.js frontend URL:

```env
FRONTEND_URL=http://localhost:3000
```

### 5. Configure OpenRouter

Create an OpenRouter API key and add it to your `.env`:

```env
OPENROUTER_API_KEY=your_openrouter_api_key
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1
OPENROUTER_MODEL=openrouter/free
```

> Never commit your real API key to GitHub.

### 6. Run migrations

```bash
php artisan migrate
```

### 7. Create the storage link

```bash
php artisan storage:link
```

### 8. Clear cached configuration

```bash
php artisan optimize:clear
```

### 9. Start Laravel

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000
```

Configure and run Laravel Reverb separately when using the real-time messaging and notification features.

---

## Frontend

The API is consumed by a separate **Next.js + TypeScript** frontend application.

**Frontend Repository:**

https://github.com/MohamadJ99/Social-Media-App

---

## Security

The backend includes several security and authorization mechanisms:

- Laravel Sanctum authentication
- Bearer-token protected routes
- Laravel Policies
- Form Request validation
- Ownership checks
- Private broadcast channel authorization
- API rate limiting
- Secure server-side AI API keys
- Restricted story viewer access
- Password hashing
- API validation error responses

---

## Project Status

🚧 **Ongoing Project**

Core functionality is implemented, including authentication, user profiles, posts, comments, likes, friendships, notifications, real-time messaging, stories, password reset, media handling, and AI-assisted post generation.

Further testing, optimization, additional features, and UI/API refinements are continuing as the application evolves.

---

## Author

**Mohammad Jawad Al-Shanableh**

Junior Full Stack Developer

- GitHub: https://github.com/MohamadJ99
- LinkedIn: https://www.linkedin.com/in/mohamad-jawad99