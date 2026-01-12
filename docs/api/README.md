# API Documentation

This directory contains technical documentation for all API endpoints.

## Available Endpoints

| Endpoint | Method | Description | Documentation |
|----------|--------|-------------|---------------|
| `/api/login` | POST | User authentication | [login.md](./login.md) |
| `/api/posts` | GET | List all posts | [posts-list.md](./posts-list.md) |
| `/api/posts/{id}` | GET | Get single post | [posts-get.md](./posts-get.md) |
| `/api/posts` | POST | Create new post | [posts-create.md](./posts-create.md) |
| `/api/posts/{id}/publish` | POST | Publish a post | [posts-publish.md](./posts-publish.md) |
| `/api/users` | POST | Register new user | [users-register.md](./users-register.md) |

## Authentication

All endpoints except login require JWT Bearer token authentication.

### Getting a Token

```bash
curl -X POST http://localhost:8081/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@blog.com","password":"password"}'
```

### Using the Token

```bash
curl http://localhost:8081/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Test Users

| Email | Password | Role |
|-------|----------|------|
| admin@blog.com | password | ADMIN |
| user@blog.com | password | USER |

## Documentation Commands

Generate documentation for an endpoint:
```
/create-tech-doc {endpoint-path}
```

Update existing documentation:
```
/update-tech-doc {endpoint-path}
```
