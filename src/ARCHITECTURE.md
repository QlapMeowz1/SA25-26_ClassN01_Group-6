# BadNet Architecture Overview

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                      USER BROWSER                                │
└──────────────────────────────┬──────────────────────────────────┘
                               │ HTTP/HTTPS
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                    NEXT.JS FRONTEND (Port 3000)                  │
│  ┌────────────┬──────────────┬──────────┬─────────────────┐     │
│  │ Components │ Custom Hooks │ Services │ State Management│     │
│  └────────────┴──────────────┴──────────┴─────────────────┘     │
│                   (React 18 + TypeScript)                        │
└──────────────────────────────┬──────────────────────────────────┘
                               │ REST API Calls
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                   EXPRESS BACKEND (Port 3001)                    │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                    Routes Layer                          │   │
│  │  /api/users  /api/matches  /api/teams  /api/social etc. │   │
│  └──────────────────────────────────────────────────────────┘   │
│                              │                                    │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                Controllers Layer                         │   │
│  │  Handle HTTP requests, validate, call services         │   │
│  └──────────────────────────────────────────────────────────┘   │
│                              │                                    │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │             Services & Business Logic Layer              │   │
│  │  Core application logic, validations, transformations   │   │
│  └──────────────────────────────────────────────────────────┘   │
│                              │                                    │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │           Repository/Data Access Layer                  │   │
│  │  Database queries, entity mapping, ORM operations       │   │
│  └──────────────────────────────────────────────────────────┘   │
└──────────────────────────────┬──────────────────────────────────┘
                               │ SQL Queries
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│               PostgreSQL Database (Port 5432)                    │
│  ┌────────────────────────────────────────────────────────┐     │
│  │ Users │ Matches │ Teams │ Tournaments │ Challenges │   │     │
│  │ Bets │ Posts │ Comments │ Friendships │ Notifications │     │
│  └────────────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────────────┘
```

## Clean Architecture Pattern

BadNet follows **Clean Architecture** principles with clear separation of concerns:

### Layer 1: Controllers (HTTP Interface)
- Receive HTTP requests
- Validate input
- Call services
- Return responses

### Layer 2: Services (Business Logic)
- Core application logic
- Orchestrate between repositories
- Business rule validation
- Data transformations

### Layer 3: Repositories (Data Access)
- Abstract database operations
- Entity mapping
- Query execution
- Transaction handling

### Layer 4: Models (Data Structures)
- Database entities
- TypeScript interfaces
- Relationships definition

### Layer 5: DTOs (Data Transfer)
- Input validation schemas
- Request/response structures
- Data transformation rules

## Module Structure

Each module follows this consistent pattern:

```
module-name/
├── controllers/       ← HTTP handlers
├── services/         ← Business logic
├── models/          ← Entities & interfaces
├── repositories/    ← Database access
├── routes/          ← API routes
├── dto/            ← Validation schemas
└── index.ts        ← Module export
```

### Example: User Module Flow

```
HTTP Request
    ↓
routes/user.routes.ts
    ↓
controllers/auth.controller.ts (validates request)
    ↓
services/auth.service.ts (business logic)
    ↓
repositories/user.repository.ts (database query)
    ↓
models/user.entity.ts (database model)
    ↓
PostgreSQL Database
    ↓
Response returned through same path
```

## Data Models

### Core Entities

#### User
```typescript
{
  id: uuid
  email: string
  username: string
  password: hashed
  profile: {
    firstName: string
    lastName: string
    avatar: string
    bio: string
  }
  stats: {
    wins: number
    losses: number
    rating: number
  }
  createdAt: timestamp
  updatedAt: timestamp
}
```

#### Match
```typescript
{
  id: uuid
  title: string
  participants: User[]
  date: datetime
  location: string
  status: enum(SCHEDULED, ONGOING, COMPLETED, CANCELLED)
  score: {
    player1: number
    player2: number
  }
  createdBy: User
  createdAt: timestamp
}
```

#### Team
```typescript
{
  id: uuid
  name: string
  description: string
  captain: User
  members: User[]
  wins: number
  losses: number
  createdAt: timestamp
}
```

#### Challenge
```typescript
{
  id: uuid
  challenger: User
  opponent: User
  status: enum(PENDING, ACCEPTED, DECLINED, COMPLETED)
  match: Match (optional)
  createdAt: timestamp
}
```

#### Tournament
```typescript
{
  id: uuid
  name: string
  description: string
  startDate: datetime
  endDate: datetime
  participants: User[] | Team[]
  structure: enum(SINGLE_ELIM, DOUBLE_ELIM, ROUND_ROBIN)
  bracket: BracketData
  status: enum(DRAFT, ONGOING, COMPLETED)
  createdAt: timestamp
}
```

#### Bet
```typescript
{
  id: uuid
  bettor: User
  match: Match | Tournament
  prediction: string
  amount: decimal
  odds: decimal
  potential_return: decimal
  status: enum(PENDING, WON, LOST)
  createdAt: timestamp
  settledAt: timestamp
}
```

#### Post (Social)
```typescript
{
  id: uuid
  author: User
  content: string
  media: File[] (optional)
  likes: number
  comments: Comment[]
  visibility: enum(PUBLIC, FRIENDS, PRIVATE)
  createdAt: timestamp
  updatedAt: timestamp
}
```

## Authentication & Authorization

### JWT-Based Authentication

```
1. User logs in → credentials sent to /api/users/login
2. Backend validates → generates JWT token
3. Token stored in httpOnly cookie or localStorage
4. Token included in Authorization header for all subsequent requests
5. Backend validates token on each request
```

### Authorization Levels
- **Public** - No auth required
- **User** - Authenticated user required
- **Owner** - Must be resource owner
- **Admin** - Administrator privileges required

## Database Schema

### PostgreSQL Tables

#### users
```sql
id (UUID primary key)
email (unique)
username (unique)
password_hash
first_name
last_name
avatar_url
bio
rating (decimal)
created_at
updated_at
```

#### matches
```sql
id (UUID primary key)
title
location
scheduled_date
status
created_by (FK: users)
created_at
updated_at
```

#### match_participants
```sql
match_id (FK: matches)
user_id (FK: users)
score
position (1 or 2)
```

#### teams
```sql
id (UUID primary key)
name (unique)
description
captain_id (FK: users)
created_at
updated_at
```

#### team_members
```sql
team_id (FK: teams)
user_id (FK: users)
joined_at
```

#### tournaments
```sql
id (UUID primary key)
name
description
start_date
end_date
structure (enum)
status (enum)
created_by (FK: users)
created_at
```

#### challenges
```sql
id (UUID primary key)
challenger_id (FK: users)
opponent_id (FK: users)
status (enum)
match_id (FK: matches, nullable)
created_at
```

#### bets
```sql
id (UUID primary key)
bettor_id (FK: users)
match_id (FK: matches, nullable)
tournament_id (FK: tournaments, nullable)
prediction
amount (decimal)
odds (decimal)
status (enum)
created_at
settled_at (nullable)
```

#### posts
```sql
id (UUID primary key)
author_id (FK: users)
content
visibility (enum)
likes_count
created_at
updated_at
```

#### comments
```sql
id (UUID primary key)
post_id (FK: posts)
author_id (FK: users)
content
created_at
updated_at
```

## API Response Format

### Successful Response (200, 201, etc.)
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* response data */ },
  "timestamp": "2024-01-01T12:00:00Z"
}
```

### Error Response (4xx, 5xx)
```json
{
  "success": false,
  "error": "Error code",
  "message": "Human readable error message",
  "details": { /* additional error info */ },
  "timestamp": "2024-01-01T12:00:00Z"
}
```

## Middleware Pipeline

```
Request
  ↓
[1] CORS Middleware - Handle cross-origin requests
  ↓
[2] Body Parser - Parse JSON/form data
  ↓
[3] Logger Middleware - Log requests
  ↓
[4] Auth Middleware - Verify JWT token
  ↓
[5] Validation Middleware - Validate DTO
  ↓
[6] Rate Limiter - Prevent abuse
  ↓
Route Handler
  ↓
[7] Error Handler - Catch exceptions
  ↓
Response
```

## Security Architecture

### Authentication
- JWT tokens with expiration
- Refresh token rotation
- HTTP-only cookies for token storage
- Bcrypt password hashing

### Authorization
- Role-based access control (RBAC)
- Resource ownership verification
- Permission decorators

### Input Validation
- DTO validation with class-validator
- Request sanitization
- SQL injection prevention via ORM

### Data Protection
- HTTPS/TLS encryption
- CORS restrictions
- Rate limiting
- SQL parameterization

## Deployment Architecture

### Development
```
Local Machine
├── Frontend: http://localhost:3000
├── Backend: http://localhost:3001
└── Database: PostgreSQL on localhost:5432
```

### Production
```
Cloud Infrastructure (e.g., AWS)
├── Frontend: Next.js on Vercel/CloudFront
├── Backend: Express on EC2/ECS
├── Database: RDS PostgreSQL
├── CDN: CloudFront
├── Storage: S3 (for media)
└── Cache: Redis (optional)
```

## Performance Considerations

### Frontend Optimization
- Code splitting with Next.js
- Image optimization
- Lazy component loading
- Local state caching

### Backend Optimization
- Database indexing on frequently queried fields
- Connection pooling
- Query optimization
- Response caching

### Database Optimization
- Proper indexing strategy
- Query optimization
- Connection pooling
- Regular maintenance

## Scalability Strategy

### Horizontal Scaling
- Stateless backend servers
- Load balancer (NGINX/ALB)
- Database replication
- Distributed caching (Redis)

### Vertical Scaling
- Increase server resources
- Database optimization
- Caching strategies

## Monitoring & Logging

### Application Logging
- Request/response logging
- Error tracking (Sentry)
- Performance metrics

### Database Monitoring
- Slow query logs
- Connection pool monitoring
- Backup verification

## CI/CD Pipeline

```
Code Push
  ↓
[1] Linting & Static Analysis
  ↓
[2] Unit Tests
  ↓
[3] Integration Tests
  ↓
[4] Build
  ↓
[5] Deploy to Staging
  ↓
[6] E2E Tests
  ↓
[7] Deploy to Production
  ↓
[8] Monitoring & Alerts
```

---

This architecture provides a solid foundation for a scalable, maintainable badminton social platform.
