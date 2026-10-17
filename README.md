# uts-pl-24-534267-SV-24022-ver1

## Project Tracker — Todo Management

Aplikasi manajemen proyek dan tugas untuk seorang Project Manager yang membawahi banyak anggota tim.

### Tech Stack
- **Backend**: Laravel 11 + Laravel Sanctum + Laravel Octane (FrankenPHP)
- **Frontend**: Vue 3 + Vite + Pinia + Axios
- **Database**: PostgreSQL 16
- **Containerization**: Docker + Docker Compose
- **CI/CD**: GitHub Actions → GHCR

### Quick Start

```bash
# 1. Clone repository
git clone https://github.com/KEPL2026/uts-pl-24-534267-SV-24022-ver1.git
cd uts-pl-24-534267-SV-24022-ver1

# 2. Copy environment file
cp .env.example .env
# Edit .env and set APP_KEY (generate with: php artisan key:generate --show)

# 3. Run with Docker Compose
docker compose up -d --build

# 4. Run migrations and seed
docker compose exec backend php artisan migrate --seed

# 5. Open browser
# Frontend: http://localhost:5173
# Backend API: http://localhost:8000/api
```

### Demo Credentials
| Email | Password | Role |
|---|---|---|
| admin@example.com | password123 | PM |
| budi@example.com | password123 | Member |
| siti@example.com | password123 | Member |

### API Endpoints
| Method | Endpoint | Description |
|---|---|---|
| POST | /api/register | Register new user |
| POST | /api/login | Login (returns token) |
| POST | /api/logout | Logout (revoke token) |
| GET | /api/projects | List all projects |
| POST | /api/projects | Create project (201) |
| GET | /api/projects/{id} | Show project detail |
| PUT | /api/projects/{id} | Update project |
| DELETE | /api/projects/{id} | Delete project |
| GET | /api/tasks | List all tasks |
| POST | /api/tasks | Create task (201) |
| GET | /api/tasks/{id} | Show task detail |
| PUT | /api/tasks/{id} | Update task |
| DELETE | /api/tasks/{id} | Delete task |

### Running Tests
```bash
cd backend
php artisan test
```

### Postman Collection
Import `backend/postman/ProjectTracker.postman_collection.json` into Postman.
