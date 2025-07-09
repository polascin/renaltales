# RenalTales - Deployment Guide

## Overview

This document provides comprehensive guidance for deploying RenalTales to various environments including staging, testing, and production.

## Table of Contents

1. [Pre-deployment Requirements](#pre-deployment-requirements)
2. [Environment Configurations](#environment-configurations)
3. [Database Deployment](#database-deployment)
4. [Application Deployment](#application-deployment)
5. [Security Hardening](#security-hardening)
6. [Performance Optimization](#performance-optimization)
7. [Monitoring Setup](#monitoring-setup)
8. [Backup and Recovery](#backup-and-recovery)
9. [Troubleshooting](#troubleshooting)

## Pre-deployment Requirements

### System Requirements

#### Minimum Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum
- **Disk Space**: 2GB free space minimum

#### Recommended Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher / MariaDB 10.6+
- **Web Server**: Nginx 1.20+ (preferred for production)
- **Memory**: 2GB RAM or higher
- **Disk Space**: 10GB free space

### PHP Extensions Required
```bash
# Core extensions
php-json
php-mbstring
php-openssl
php-pdo
php-pdo-mysql
php-tokenizer
php-xml
php-ctype
php-curl
php-gd
php-intl
php-zip
php-fileinfo

# Optional but recommended
php-redis
php-opcache
php-imagick
```

### SSL Certificate
- Valid SSL certificate for production environments
- Let's Encrypt recommended for cost-effective solution

## Environment Configurations

### Development Environment
```env
# .env.development
APP_NAME="Renal Tales"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/renaltales

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=renaltales
DB_USERNAME=root
DB_PASSWORD=

# Logging
LOG_LEVEL=debug
LOG_CHANNEL=file

# Cache
CACHE_DRIVER=file

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Security
BCRYPT_ROUNDS=10
```

### Staging Environment
```env
# .env.staging
APP_NAME="Renal Tales - Staging"
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.renaltales.com

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=renaltales_staging
DB_USERNAME=staging_user
DB_PASSWORD=secure_staging_password

# Logging
LOG_LEVEL=info
LOG_CHANNEL=file

# Cache
CACHE_DRIVER=file

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Security
BCRYPT_ROUNDS=12
```

### Production Environment
```env
# .env.production
APP_NAME="Renal Tales"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://renaltales.com

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=renaltales_production
DB_USERNAME=production_user
DB_PASSWORD=very_secure_production_password

# Logging
LOG_LEVEL=error
LOG_CHANNEL=file

# Cache
CACHE_DRIVER=redis

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=60

# Security
BCRYPT_ROUNDS=12

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=redis_password
REDIS_DB=0
REDIS_CACHE_DB=1

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=noreply@renaltales.com
MAIL_PASSWORD=mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@renaltales.com
MAIL_FROM_NAME="Renal Tales"
```

## Database Deployment

### Database Setup Steps

1. **Create Database User**
```sql
-- For production
CREATE USER 'production_user'@'localhost' IDENTIFIED BY 'very_secure_production_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON renaltales_production.* TO 'production_user'@'localhost';
FLUSH PRIVILEGES;
```

2. **Create Database**
```sql
CREATE DATABASE renaltales_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. **Run Migrations**
```bash
php database/migrate.php
```

4. **Seed Database**
```bash
php database/seeders/001_seeder.sql
```

### Database Configuration

#### MySQL Configuration (my.cnf)
```ini
[mysqld]
# Performance
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 1
innodb_file_per_table = 1

# Security
bind-address = 127.0.0.1
skip-name-resolve

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

## Application Deployment

### Deployment Steps

1. **Clone Repository**
```bash
git clone https://github.com/yourusername/renaltales.git
cd renaltales
```

2. **Install Dependencies**
```bash
composer install --no-dev --optimize-autoloader
```

3. **Set Environment Configuration**
```bash
cp .env.example .env
# Edit .env with production values
```

4. **Set Directory Permissions**
```bash
chmod -R 755 storage/
chmod -R 755 resources/lang/
chmod -R 755 public/assets/
chown -R www-data:www-data storage/
chown -R www-data:www-data resources/lang/
```

5. **Configure Web Server**

#### Apache Configuration (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [QSA,L]

# Security headers
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set Content-Security-Policy "default-src 'self'"

# Deny access to sensitive files
<FilesMatch "\.(env|log|sql|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name renaltales.com www.renaltales.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name renaltales.com www.renaltales.com;
    
    root /var/www/renaltales/public;
    index index.php;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to sensitive files
    location ~ /\.(env|git|svn) {
        deny all;
    }
    
    location ~ \.(log|sql|md)$ {
        deny all;
    }
}
```

## Security Hardening

### Application Security

1. **Environment Variables**
```bash
# Ensure .env is not publicly accessible
chmod 600 .env
```

2. **File Permissions**
```bash
# Application files
find /var/www/renaltales -type f -exec chmod 644 {} \;
find /var/www/renaltales -type d -exec chmod 755 {} \;

# Executable files
chmod +x scripts/*
```

3. **Database Security**
```sql
-- Remove default accounts
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
```

### Server Security

1. **Firewall Configuration**
```bash
# UFW (Ubuntu)
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

# Fail2ban
apt install fail2ban
systemctl enable fail2ban
systemctl start fail2ban
```

2. **SSL/TLS Configuration**
```bash
# Let's Encrypt
certbot --nginx -d renaltales.com -d www.renaltales.com
```

## Performance Optimization

### PHP Configuration (php.ini)
```ini
# Performance
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0

# File uploads
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20

# Memory and execution
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
```

### Caching Strategy

1. **Redis Configuration**
```bash
# Install Redis
apt install redis-server

# Configure Redis
# Edit /etc/redis/redis.conf
maxmemory 512mb
maxmemory-policy allkeys-lru
```

2. **Application Caching**
```php
// Enable caching in production
$config['cache']['default'] = 'redis';
$config['session']['driver'] = 'redis';
```

### Database Optimization

1. **Indexes**
```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_stories_user_id ON stories(user_id);
CREATE INDEX idx_stories_created_at ON stories(created_at);
CREATE INDEX idx_comments_story_id ON comments(story_id);
CREATE INDEX idx_users_email ON users(email);
```

2. **Query Optimization**
```sql
-- Analyze and optimize slow queries
EXPLAIN SELECT * FROM stories WHERE user_id = 1;
```

## Monitoring Setup

### Application Monitoring

1. **Log Monitoring**
```bash
# Install log monitoring tool
apt install logwatch

# Configure log rotation
cat > /etc/logrotate.d/renaltales << EOF
/var/www/renaltales/storage/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
    create 0644 www-data www-data
}
EOF
```

2. **Health Check Endpoint**
```php
// Create health check endpoint
// public/health.php
<?php
header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'checks' => [
        'database' => checkDatabase(),
        'cache' => checkCache(),
        'storage' => checkStorage(),
    ]
];

echo json_encode($health);
```

### Server Monitoring

1. **System Monitoring**
```bash
# Install monitoring tools
apt install htop iotop nethogs

# Setup monitoring cron job
0 * * * * /usr/bin/php /var/www/renaltales/scripts/health-check.php
```

2. **Uptime Monitoring**
```bash
# Setup external uptime monitoring
# Use services like Pingdom, StatusCake, or UptimeRobot
```

## Backup and Recovery

### Database Backups

1. **Automated Backup Script**
```bash
#!/bin/bash
# backup-database.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/renaltales"
DB_NAME="renaltales_production"
DB_USER="production_user"
DB_PASS="very_secure_production_password"

mkdir -p $BACKUP_DIR

# Full backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/backup_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

2. **Backup Scheduling**
```bash
# Add to crontab
crontab -e

# Daily backup at 2 AM
0 2 * * * /path/to/backup-database.sh

# Weekly full backup
0 3 * * 0 /path/to/backup-full.sh
```

### File Backups

1. **Application Files**
```bash
#!/bin/bash
# backup-files.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/renaltales"
APP_DIR="/var/www/renaltales"

# Backup uploaded files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz $APP_DIR/storage/uploads/

# Backup configuration
tar -czf $BACKUP_DIR/config_$DATE.tar.gz $APP_DIR/.env $APP_DIR/config/

echo "File backup completed: files_$DATE.tar.gz"
```

### Recovery Procedures

1. **Database Recovery**
```bash
# Stop application
systemctl stop nginx

# Restore database
gunzip -c backup_20250101_020000.sql.gz | mysql -u production_user -p renaltales_production

# Start application
systemctl start nginx
```

2. **Application Recovery**
```bash
# Download backup
cd /var/www/
tar -xzf backup_files.tar.gz

# Set permissions
chown -R www-data:www-data renaltales/
chmod -R 755 renaltales/storage/

# Restart services
systemctl restart nginx
systemctl restart php8.2-fpm
```

## Troubleshooting

### Common Issues

1. **Permission Errors**
```bash
# Fix permissions
chown -R www-data:www-data /var/www/renaltales/storage/
chmod -R 755 /var/www/renaltales/storage/
```

2. **Database Connection Issues**
```bash
# Check database service
systemctl status mysql

# Test connection
mysql -u production_user -p renaltales_production
```

3. **Cache Issues**
```bash
# Clear cache
rm -rf /var/www/renaltales/storage/cache/*

# Restart Redis
systemctl restart redis
```

### Log Analysis

1. **Application Logs**
```bash
# Monitor application logs
tail -f /var/www/renaltales/storage/logs/application.log

# Check error logs
grep "ERROR" /var/www/renaltales/storage/logs/error.log
```

2. **Web Server Logs**
```bash
# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Apache logs
tail -f /var/log/apache2/access.log
tail -f /var/log/apache2/error.log
```

### Performance Issues

1. **Database Performance**
```sql
-- Check slow queries
SHOW PROCESSLIST;
SHOW FULL PROCESSLIST;

-- Analyze table performance
ANALYZE TABLE stories;
OPTIMIZE TABLE stories;
```

2. **Application Performance**
```bash
# Check PHP-FPM status
systemctl status php8.2-fpm

# Monitor resource usage
htop
```

## Deployment Checklist

### Pre-deployment
- [ ] Requirements verified
- [ ] Environment configuration prepared
- [ ] Database setup completed
- [ ] SSL certificate obtained
- [ ] Backup system configured

### Deployment
- [ ] Code deployed from repository
- [ ] Dependencies installed
- [ ] Environment file configured
- [ ] File permissions set
- [ ] Web server configured
- [ ] Database migrations run
- [ ] Cache cleared and warmed

### Post-deployment
- [ ] Health checks passing
- [ ] Monitoring configured
- [ ] Backup system tested
- [ ] Performance optimized
- [ ] Security hardened
- [ ] Documentation updated

### Rollback Plan
- [ ] Database backup created
- [ ] Application backup created
- [ ] Rollback procedure documented
- [ ] Rollback tested in staging

---

*Last updated: January 2025*
*Version: 1.0*
