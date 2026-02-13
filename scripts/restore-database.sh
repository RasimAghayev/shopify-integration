#!/bin/bash

# Database Restore Script for Shopify Integration
# This script restores a PostgreSQL database from a backup file

set -e

COMPOSE_FILE="/var/www/shopify-integration/docker-compose.prod.yml"

if [ -z "$1" ]; then
    echo "Usage: $0 <backup_file.sql.gz>"
    echo "Example: $0 /var/backups/shopify/db_20240101_120000.sql.gz"
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "Error: Backup file not found: $BACKUP_FILE"
    exit 1
fi

echo "WARNING: This will overwrite the current database!"
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Restore cancelled."
    exit 0
fi

echo "Starting database restore at $(date)"

# Decompress if necessary
if [[ "$BACKUP_FILE" == *.gz ]]; then
    echo "Decompressing backup file..."
    TEMP_FILE=$(mktemp)
    gunzip -c "$BACKUP_FILE" > "$TEMP_FILE"
    SQL_FILE="$TEMP_FILE"
else
    SQL_FILE="$BACKUP_FILE"
fi

# Drop and recreate database
echo "Recreating database..."
docker-compose -f "$COMPOSE_FILE" exec -T postgres \
    psql -U shopify -d postgres -c "DROP DATABASE IF EXISTS shopify_integration;"
docker-compose -f "$COMPOSE_FILE" exec -T postgres \
    psql -U shopify -d postgres -c "CREATE DATABASE shopify_integration;"

# Restore the backup
echo "Restoring data..."
docker-compose -f "$COMPOSE_FILE" exec -T postgres \
    psql -U shopify -d shopify_integration < "$SQL_FILE"

# Clean up temp file if created
if [ -n "$TEMP_FILE" ]; then
    rm -f "$TEMP_FILE"
fi

echo "Database restore completed at $(date)"
