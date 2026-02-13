#!/bin/bash

# Database Backup Script for Shopify Integration
# This script creates a compressed backup of the PostgreSQL database

set -e

BACKUP_DIR="/var/backups/shopify"
DATE=$(date +%Y%m%d_%H%M%S)
COMPOSE_FILE="/var/www/shopify-integration/docker-compose.prod.yml"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

echo "Starting database backup at $(date)"

# Create the backup
docker-compose -f "$COMPOSE_FILE" exec -T postgres \
    pg_dump -U shopify shopify_integration > "$BACKUP_DIR/db_$DATE.sql"

# Compress the backup
gzip "$BACKUP_DIR/db_$DATE.sql"

# Remove backups older than 7 days
find "$BACKUP_DIR" -name "*.gz" -mtime +7 -delete

echo "Backup completed: db_$DATE.sql.gz"
echo "Backup size: $(du -h "$BACKUP_DIR/db_$DATE.sql.gz" | cut -f1)"
