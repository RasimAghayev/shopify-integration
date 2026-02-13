# VPS Deployment Guide

Complete step-by-step guide for deploying the Shopify Integration system to a VPS server.

## Prerequisites

- VPS with Ubuntu 22.04 LTS (minimum 2GB RAM, 2 CPU cores)
- Domain name pointed to VPS IP
- SSH access to VPS
- Docker Hub account

## Step 1: Initial VPS Setup

### 1.1 Connect to VPS

```bash
ssh root@your-vps-ip
```

### 1.2 Update System

```bash
apt-get update && apt-get upgrade -y
```

### 1.3 Create Deploy User

```bash
adduser deploy
usermod -aG sudo deploy
```

### 1.4 Setup SSH Key Authentication

On your local machine:
```bash
ssh-keygen -t ed25519 -C "deploy@shopify-integration"
ssh-copy-id deploy@your-vps-ip
```

### 1.5 Disable Password Authentication

```bash
nano /etc/ssh/sshd_config
```

Set:
```
PasswordAuthentication no
PubkeyAuthentication yes
```

Restart SSH:
```bash
systemctl restart sshd
```

## Step 2: Install Docker

### 2.1 Install Docker Engine

```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
```

### 2.2 Add User to Docker Group

```bash
usermod -aG docker deploy
```

### 2.3 Install Docker Compose

```bash
curl -L "https://github.com/docker/compose/releases/download/v2.23.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
```

### 2.4 Verify Installation

```bash
docker --version
docker-compose --version
```

## Step 3: Install Nginx

### 3.1 Install Nginx

```bash
apt-get install -y nginx
```

### 3.2 Start and Enable

```bash
systemctl start nginx
systemctl enable nginx
```

## Step 4: Setup SSL with Let's Encrypt

### 4.1 Install Certbot

```bash
apt-get install -y certbot python3-certbot-nginx
```

### 4.2 Obtain SSL Certificates

```bash
certbot --nginx -d yourdomain.com -d api.yourdomain.com
```

### 4.3 Setup Auto-Renewal

```bash
certbot renew --dry-run
```

Add to crontab:
```bash
crontab -e
```

Add line:
```
0 0 1 * * certbot renew --quiet
```

## Step 5: Configure Firewall

### 5.1 Setup UFW

```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

### 5.2 Verify Status

```bash
ufw status
```

## Step 6: Clone Repository

### 6.1 Create Application Directory

```bash
mkdir -p /var/www/shopify-integration
cd /var/www/shopify-integration
```

### 6.2 Clone Repository

```bash
git clone https://github.com/your-username/shopify-integration.git .
```

### 6.3 Set Permissions

```bash
chown -R deploy:deploy /var/www/shopify-integration
```

## Step 7: Configure Environment

### 7.1 Backend Environment

```bash
cp backend/.env.example backend/.env
nano backend/.env
```

Set production values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_HOST=postgres
DB_DATABASE=shopify_integration
DB_USERNAME=shopify
DB_PASSWORD=your-secure-password

REDIS_HOST=redis

SHOPIFY_API_KEY=your-api-key
SHOPIFY_API_SECRET=your-api-secret
SHOPIFY_STORE_DOMAIN=your-store.myshopify.com
```

### 7.2 Frontend Environment

```bash
cp frontend/.env.example frontend/.env
nano frontend/.env
```

Set production values:
```env
NUXT_PUBLIC_API_BASE=https://api.yourdomain.com/api
```

## Step 8: Configure Nginx

### 8.1 Backend Configuration

Create file `/etc/nginx/sites-available/api.yourdomain.com`:

```nginx
upstream backend_servers {
    least_conn;
    server 127.0.0.1:8000 max_fails=3 fail_timeout=30s;
}

server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        proxy_pass http://backend_servers;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }

    location /health {
        access_log off;
        proxy_pass http://backend_servers;
    }
}
```

### 8.2 Frontend Configuration

Create file `/etc/nginx/sites-available/yourdomain.com`:

```nginx
upstream frontend_servers {
    least_conn;
    server 127.0.0.1:3000;
}

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    location / {
        proxy_pass http://frontend_servers;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 8.3 Enable Sites

```bash
ln -s /etc/nginx/sites-available/api.yourdomain.com /etc/nginx/sites-enabled/
ln -s /etc/nginx/sites-available/yourdomain.com /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

## Step 9: Start Application

### 9.1 Build and Start Containers

```bash
cd /var/www/shopify-integration
docker-compose -f docker-compose.prod.yml up -d --build
```

### 9.2 Run Migrations

```bash
docker-compose -f docker-compose.prod.yml exec backend php artisan migrate --force
```

### 9.3 Generate App Key

```bash
docker-compose -f docker-compose.prod.yml exec backend php artisan key:generate
```

### 9.4 Cache Configuration

```bash
docker-compose -f docker-compose.prod.yml exec backend php artisan config:cache
docker-compose -f docker-compose.prod.yml exec backend php artisan route:cache
docker-compose -f docker-compose.prod.yml exec backend php artisan view:cache
```

## Step 10: Setup GitHub Actions Secrets

In your GitHub repository, add these secrets:

| Secret Name | Value |
|------------|-------|
| DOCKER_USERNAME | Your Docker Hub username |
| DOCKER_PASSWORD | Your Docker Hub password/token |
| VPS_HOST | Your VPS IP address |
| VPS_USER | deploy |
| VPS_SSH_KEY | Contents of ~/.ssh/id_ed25519 |
| DOMAIN | Your domain (e.g., yourdomain.com) |

## Step 11: Verify Deployment

### 11.1 Check Container Status

```bash
docker-compose -f docker-compose.prod.yml ps
```

All containers should show "Up" status.

### 11.2 Check Application Health

```bash
curl https://api.yourdomain.com/health
curl https://yourdomain.com
```

### 11.3 Check Logs

```bash
docker-compose -f docker-compose.prod.yml logs -f backend
docker-compose -f docker-compose.prod.yml logs -f frontend
```

## Step 12: Setup Monitoring

### 12.1 Install Basic Monitoring

```bash
apt-get install -y htop iotop
```

### 12.2 Setup Log Rotation

Create `/etc/logrotate.d/docker-containers`:

```
/var/lib/docker/containers/*/*.log {
    rotate 7
    daily
    compress
    missingok
    delaycompress
    copytruncate
}
```

## Step 13: Backup Configuration

### 13.1 Database Backup Script

Create `/var/www/shopify-integration/scripts/backup-database.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/shopify"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

docker-compose -f /var/www/shopify-integration/docker-compose.prod.yml exec -T postgres \
    pg_dump -U shopify shopify_integration > $BACKUP_DIR/db_$DATE.sql

gzip $BACKUP_DIR/db_$DATE.sql

find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: db_$DATE.sql.gz"
```

### 13.2 Schedule Backups

```bash
chmod +x /var/www/shopify-integration/scripts/backup-database.sh
crontab -e
```

Add:
```
0 2 * * * /var/www/shopify-integration/scripts/backup-database.sh
```

## Troubleshooting

### Container Not Starting

```bash
docker-compose -f docker-compose.prod.yml logs backend
```

### Database Connection Issues

```bash
docker-compose -f docker-compose.prod.yml exec backend php artisan tinker
>>> DB::connection()->getPdo();
```

### Permission Issues

```bash
docker-compose -f docker-compose.prod.yml exec backend chown -R www-data:www-data storage
docker-compose -f docker-compose.prod.yml exec backend chmod -R 775 storage
```

### Clear All Caches

```bash
docker-compose -f docker-compose.prod.yml exec backend php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec backend php artisan config:clear
docker-compose -f docker-compose.prod.yml exec backend php artisan route:clear
```

## Security Checklist

- [ ] SSH key authentication enabled
- [ ] Password authentication disabled
- [ ] Firewall configured
- [ ] SSL certificates installed
- [ ] APP_DEBUG set to false
- [ ] Strong database passwords
- [ ] Regular backups configured
- [ ] Log rotation enabled

## Rollback Procedure

If deployment fails:

```bash
cd /var/www/shopify-integration
git log --oneline -5  # Find previous commit
git checkout <previous-commit-hash>
docker-compose -f docker-compose.prod.yml up -d --build
```
