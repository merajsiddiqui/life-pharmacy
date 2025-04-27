# Local Setup Guide

This guide will help you set up and run the Life Pharmacy API on your local machine without Docker.

## Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Redis
- Nginx or Apache
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
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=life_pharmacy
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

4. Install PHP dependencies:
```bash
composer install
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Run database migrations:
```bash
php artisan migrate
```

7. Set proper permissions:
```bash
chmod -R 775 storage bootstrap/cache
```

## Web Server Configuration

### Nginx Configuration

Create a new Nginx configuration file at `/etc/nginx/sites-available/life-pharmacy`:

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/life-pharmacy/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/life-pharmacy /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Apache Configuration

Create a new Apache configuration file at `/etc/apache2/sites-available/life-pharmacy.conf`:

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /path/to/life-pharmacy/public

    <Directory /path/to/life-pharmacy/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite life-pharmacy.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Accessing the Application

- API: http://localhost/api
- Database: localhost:3306
- Redis: localhost:6379

## Testing

### Running Tests

1. Run all tests:
```bash
php artisan test
```

2. Run specific test file:
```bash
php artisan test tests/Unit/Services/ProductServiceTest.php
```

3. Run tests with coverage report:
```bash
php artisan test --coverage
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