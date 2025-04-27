# Docker Setup Guide

This guide will help you set up and run the Life Pharmacy API using Docker.

## Prerequisites

- Docker
- Docker Compose
- Git

## Setup Steps

1. Clone the repository:
```bash
git clone https://github.com/merajsiddiqui/life-pharmacy.git
cd life-pharmacy
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Update the `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=life_pharmacy
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

4. Build and start the Docker containers:
```bash
docker-compose up -d
```

5. Install PHP dependencies:
```bash
docker-compose exec app composer install
```

6. Generate application key:
```bash
docker-compose exec app php artisan key:generate
```

7. Run database migrations:
```bash
docker-compose exec app php artisan migrate
```

8. Set proper permissions:
```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## Docker Services

The application uses the following Docker services:

- **app**: PHP-FPM 8.2 application container
- **nginx**: Nginx web server
- **db**: MySQL 8.0 database
- **redis**: Redis cache server

## Accessing the Application

- API: http://localhost:8000/api
- Database: localhost:3306
- Redis: localhost:6379

## Development Commands

1. Start the development environment:
```bash
docker-compose up -d
```

2. View logs:
```bash
docker-compose logs -f
```

3. Stop the environment:
```bash
docker-compose down
```

## Testing

### Running Tests

1. Run all tests:
```bash
docker-compose exec app php artisan test
```

2. Run specific test file:
```bash
docker-compose exec app php artisan test tests/Unit/Services/ProductServiceTest.php
```

3. Run tests with coverage report:
```bash
docker-compose exec app php artisan test --coverage
```

### Test Categories

1. **Service Tests**
   - CRUD operations
   - Business logic
   - Data manipulation
   - Cache handling

2. **Controller Tests**
   - API endpoint functionality
   - Request validation
   - Response formatting
   - Error handling

3. **Middleware Tests**
   - Language switching
   - Request processing
   - Header handling

4. **Trait Tests**
   - API response formatting
   - Input sanitization
   - Helper methods 