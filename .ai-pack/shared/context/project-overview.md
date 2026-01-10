# Project Overview

## Project Name
AI-Assisted Development Teams Demo

## Description
This is a modern full-stack web application built to demonstrate AI-assisted development workflows and team collaboration patterns.

## Technology Stack

### Frontend
- **Framework**: React 18+ with TypeScript
- **State Management**: Redux Toolkit / Zustand
- **Styling**: CSS Modules / Styled Components
- **UI Library**: Material-UI / Ant Design / Custom components
- **Testing**: Jest + React Testing Library
- **E2E Testing**: Playwright / Cypress
- **Build Tool**: Vite / Create React App

### Backend
- **Runtime**: Node.js (v18+)
- **Framework**: Express.js
- **Language**: TypeScript
- **API Style**: RESTful API
- **Authentication**: JWT + Passport.js
- **Validation**: Joi / Zod
- **Testing**: Jest + Supertest

### Database
- **Primary**: PostgreSQL / MongoDB
- **ORM/ODM**: TypeORM / Prisma / Mongoose
- **Migrations**: TypeORM migrations / Prisma migrate
- **Caching**: Redis (optional)

### DevOps
- **Containerization**: Docker + Docker Compose
- **CI/CD**: GitHub Actions
- **Hosting**: AWS / GCP / Vercel / Netlify
- **Monitoring**: Prometheus + Grafana / Datadog
- **Logging**: Winston / Pino

## Project Structure

```
project-root/
├── frontend/                 # React frontend application
│   ├── src/
│   │   ├── components/      # Reusable UI components
│   │   ├── pages/           # Page components
│   │   ├── hooks/           # Custom React hooks
│   │   ├── store/           # Redux/Zustand store
│   │   ├── services/        # API service layer
│   │   ├── utils/           # Utility functions
│   │   ├── types/           # TypeScript type definitions
│   │   └── tests/           # Test files
│   ├── public/              # Static assets
│   └── package.json
│
├── backend/                  # Node.js backend application
│   ├── src/
│   │   ├── routes/          # API route definitions
│   │   ├── controllers/     # Request handlers
│   │   ├── services/        # Business logic
│   │   ├── models/          # Database models
│   │   ├── middleware/      # Express middleware
│   │   ├── utils/           # Utility functions
│   │   ├── config/          # Configuration files
│   │   └── tests/           # Test files
│   ├── migrations/          # Database migrations
│   └── package.json
│
├── shared/                   # Shared code between frontend/backend
│   ├── types/               # Shared TypeScript types
│   └── constants/           # Shared constants
│
├── docs/                     # Documentation
├── scripts/                  # Build and deployment scripts
├── .ai-pack/                # AI development assistant configs
└── docker-compose.yml       # Docker services configuration
```

## Key Features

1. **User Authentication & Authorization**
   - JWT-based authentication
   - Role-based access control (RBAC)
   - OAuth integration (Google, GitHub)

2. **User Management**
   - User registration and login
   - Profile management
   - Password reset

3. **Data Management**
   - CRUD operations for core entities
   - Search and filtering
   - Pagination
   - Data validation

4. **Real-time Features**
   - WebSocket support
   - Live notifications
   - Real-time updates

5. **File Management**
   - File upload and download
   - Image optimization
   - Cloud storage integration

## Development Workflow

1. **Feature Development**: Use `/develop-feature` workflow
2. **Bug Fixes**: Use `/fix-bug` workflow
3. **Code Refactoring**: Use `/refactor` workflow
4. **Release**: Use `/prepare-release` workflow

## Code Standards

### Naming Conventions
- **Files**: kebab-case (e.g., `user-service.ts`)
- **Components**: PascalCase (e.g., `UserProfile.tsx`)
- **Variables/Functions**: camelCase (e.g., `getUserData`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `API_BASE_URL`)
- **Types/Interfaces**: PascalCase (e.g., `UserData`)

### File Organization
- One component per file
- Co-locate tests with source files
- Group related files in directories
- Use index files for public exports

### Code Style
- Use TypeScript for all new code
- Prefer functional components and hooks
- Use async/await over promises
- Prefer const over let
- Use meaningful variable names
- Keep functions small and focused
- Add comments for complex logic

## Testing Strategy

### Unit Tests
- Test individual functions and components
- Mock external dependencies
- Aim for >80% code coverage

### Integration Tests
- Test API endpoints
- Test database operations
- Test module interactions

### E2E Tests
- Test critical user workflows
- Test across different browsers
- Test responsive design

## Performance Standards

- **Frontend**:
  - First Contentful Paint < 1.5s
  - Time to Interactive < 3s
  - Lighthouse score > 90

- **Backend**:
  - API response time < 200ms (p95)
  - Database query time < 100ms
  - Concurrent users support: 1000+

## Security Requirements

- Input validation on all endpoints
- SQL injection prevention
- XSS protection
- CSRF protection
- Rate limiting
- Secure password storage (bcrypt)
- Environment variables for secrets
- Regular dependency updates

## Accessibility Requirements

- WCAG 2.1 Level AA compliance
- Keyboard navigation support
- Screen reader compatibility
- Proper ARIA labels
- Color contrast ratios met

## Browser Support

- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)
- Mobile browsers (iOS Safari, Chrome Android)

## Environment Variables

See `.env.example` for required environment variables.

## Getting Started

```bash
# Clone repository
git clone <repo-url>

# Install dependencies
npm install

# Setup database
npm run db:setup

# Start development servers
npm run dev

# Run tests
npm test

# Build for production
npm run build
```

## Useful Commands

- `npm run dev` - Start development server
- `npm test` - Run all tests
- `npm run lint` - Lint code
- `npm run format` - Format code
- `npm run type-check` - Check TypeScript types
- `npm run build` - Build for production
- `npm run migrate` - Run database migrations

## Documentation

- [API Documentation](./docs/api/README.md)
- [Component Library](./docs/components/README.md)
- [Architecture](./docs/architecture.md)
- [Contributing Guide](./CONTRIBUTING.md)

## Team & Roles

- **Architect**: System design and technical decisions
- **Frontend Developers**: UI/UX implementation
- **Backend Developers**: API and business logic
- **QA Engineers**: Testing and quality assurance
- **DevOps Engineers**: Deployment and infrastructure

## Contact & Support

- **Issue Tracker**: GitHub Issues
- **Documentation**: /docs directory
- **Team Chat**: Slack/Discord channel
