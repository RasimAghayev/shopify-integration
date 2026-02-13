# Shopify Integration - Production-Ready E-commerce System

> A production-grade Shopify product synchronization system built with Clean Architecture, SOLID principles, and TDD approach. Demonstrates best practices in modern PHP and Vue.js development.

[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3-4FC08D?logo=vue.js&logoColor=white)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?logo=typescript&logoColor=white)](https://www.typescriptlang.org)

---

## 📋 Table of Contents

- [Project Overview](#-project-overview)
- [Architecture](#-architecture)
- [Tech Stack](#-tech-stack)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
  - [Docker Setup (Recommended)](#docker-setup-recommended)
  - [Manual Setup](#manual-setup)
- [Configuration](#-configuration)
- [Database Setup](#-database-setup)
- [Running the Application](#-running-the-application)
- [Testing](#-testing)
- [API Documentation](#-api-documentation)
- [Code Quality](#-code-quality)
- [CI/CD Pipeline](#-cicd-pipeline)
- [Development Workflow](#-development-workflow)
- [Troubleshooting](#-troubleshooting)
- [Production Deployment](#-production-deployment)
- [Performance Optimization](#-performance-optimization)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Project Overview

This project is a comprehensive **Shopify Integration System** designed to demonstrate:

- ✅ **Clean Architecture** - Domain-driven design with clear layer separation
- ✅ **SOLID Principles** - Maintainable, testable, and extensible code
- ✅ **TDD Approach** - Test-first development methodology
- ✅ **Modern PHP 8.4** - Typed properties, readonly classes, enums
- ✅ **Vue 3 + TypeScript** - Type-safe reactive frontend
- ✅ **RESTful API** - Well-designed HTTP interface
- ✅ **Production-Ready** - Docker, CI/CD, monitoring

### Key Features

- 🔄 **Product Synchronization** - Sync products from Shopify to local database
- 📦 **Bulk Import** - Queue-based bulk product synchronization
- 📊 **Inventory Management** - Real-time inventory updates
- 🎨 **Admin Dashboard** - Vue 3 frontend for product management
- 🔍 **Search & Filter** - Fast product search with caching
- 📱 **Responsive Design** - Mobile-first UI

---

## 🏛️ Architecture

### Clean Architecture Layers

```
┌─────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                   │
│  (HTTP Controllers, Requests, Resources, Routes)        │
│  • ProductController • SyncController                   │
│  • API Resources • Form Request Validation              │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│                   APPLICATION LAYER                     │
│  (Use Cases, DTOs, Services, Contracts)                 │
│  • SyncProductFromShopify • BulkImportProducts          │
│  • UpdateInventory • GetProductDetails                  │
└─────────────────────────────────────────────────────────┘
                           ↓
┌──────────────────────────────────────────────────────────┐
│                      DOMAIN LAYER                        │
│  (Entities, Value Objects, Events, Repository Interfaces)│
│  • Product Entity • ProductVariant Entity                │
│  • Sku, Price Value Objects • ProductStatus Enum         │
│  • ProductSynced Event • ProductRepositoryInterface      │
└──────────────────────────────────────────────────────────┘
                           ↑
┌──────────────────────────────────────────────────────────┐
│                  INFRASTRUCTURE LAYER                    │
│  (Persistence, External Services, Queue, Cache, Logging) │
│  • EloquentProductRepository • ShopifyClient             │
│  • RedisCache • SyncProductJob • MockShopifyClient       │
└──────────────────────────────────────────────────────────┘
```

### Dependency Flow

```
Presentation → Application → Domain ←←←←←← Infrastructure
    ↓              ↓            ↑               ↑
  Routes        Use Cases    Entities       Repositories
  Controllers   DTOs         Value Objects  External APIs
  Resources     Contracts    Events         Cache/Queue
```

### Directory Structure

```
shopify-integration/
├── backend/                        # Laravel 12 API
│   ├── src/
│   │   ├── Domain/                 # Business Logic Core
│   │   │   └── Product/
│   │   │       ├── Entities/       # Product, ProductVariant
│   │   │       ├── ValueObjects/   # Sku, Price, ProductStatus, Currency
│   │   │       ├── Events/         # ProductSynced, InventoryUpdated
│   │   │       ├── Exceptions/     # Domain-specific exceptions
│   │   │       └── Repositories/   # Repository interfaces
│   │   ├── Application/            # Use Cases & Services
│   │   │   ├── UseCases/           # Business operations
│   │   │   ├── Contracts/          # Interface definitions
│   │   │   └── Services/           # Application services
│   │   ├── Infrastructure/         # External Dependencies
│   │   │   ├── Persistence/        # Eloquent models & repositories
│   │   │   ├── External/           # Shopify client
│   │   │   ├── Queue/              # Background jobs
│   │   │   ├── Cache/              # Cache implementation
│   │   │   ├── Events/             # Event dispatcher
│   │   │   └── Logger/             # Logging
│   │   └── Presentation/           # API Layer
│   │       └── Http/
│   │           ├── Controllers/    # HTTP controllers
│   │           ├── Requests/       # Form request validation
│   │           └── Resources/      # API response formatting
│   ├── tests/                      # PHPUnit tests
│   │   ├── Unit/                   # Domain & Application tests
│   │   └── Feature/                # HTTP endpoint tests
│   ├── database/
│   │   └── migrations/             # Database schema
│   ├── routes/
│   │   └── api.php                 # API routes
│   ├── docker/                     # Docker configuration
│   ├── composer.json               # PHP dependencies
│   ├── phpunit.xml                 # PHPUnit configuration
│   └── pint.json                   # Laravel Pint (code style)
│
├── frontend/                       # Nuxt 3 + Vue 3 App
│   ├── src/
│   │   ├── components/             # Reusable Vue components
│   │   │   ├── products/           # Product-related components
│   │   │   ├── sync/               # Sync status components
│   │   │   └── ui/                 # UI components (Button, Input, Modal)
│   │   ├── composables/            # Vue composables
│   │   │   ├── useProducts.ts      # Product management logic
│   │   │   └── useSync.ts          # Sync logic
│   │   ├── stores/                 # Pinia state management
│   │   │   ├── product.ts          # Product store
│   │   │   └── sync.ts             # Sync store
│   │   ├── pages/                  # Nuxt pages (routes)
│   │   │   ├── index.vue           # Home page
│   │   │   ├── products/           # Product pages
│   │   │   └── sync/               # Sync page
│   │   ├── layouts/                # Page layouts
│   │   ├── types/                  # TypeScript type definitions
│   │   └── utils/                  # Utility functions
│   ├── tests/                      # Vitest tests
│   ├── package.json                # Node dependencies
│   ├── nuxt.config.ts              # Nuxt configuration
│   └── tsconfig.json               # TypeScript configuration
│
├── infrastructure/                 # Infrastructure as Code
│   └── docker/
│       └── nginx/                  # Nginx configuration
│
├── .github/
│   └── workflows/                  # CI/CD pipelines
│       ├── ci.yml                  # Continuous Integration
│       └── build-deploy.yml        # Build & Deploy
│
├── docs/                           # Documentation
│   ├── prompt/                     # Implementation guides
│   ├── best-practices/             # Coding standards
│   └── MASTER_IMPLEMENTATION_PROMPT.md
│
├── docker-compose.yml              # Docker services
├── Makefile                        # Common commands
└── README.md                       # This file
```

---

## 🛠️ Tech Stack

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.4+    | Programming language |
| **Laravel** | 12      | Web framework |
| **PostgreSQL** | 16      | Primary database |
| **Redis** | 7       | Cache & queue |
| **Guzzle** | 7       | HTTP client |
| **PHPUnit** | 11      | Testing framework |
| **Laravel Pint** | Latest  | Code style |
| **PHPStan** | Level 8 | Static analysis |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| **Vue.js** | 3 | UI framework |
| **Nuxt** | 3 | Meta-framework |
| **TypeScript** | 5 | Type safety |
| **Pinia** | Latest | State management |
| **Tailwind CSS** | 3 | Styling |
| **Vitest** | Latest | Unit testing |
| **Playwright** | Latest | E2E testing |

### Infrastructure

| Technology | Version | Purpose |
|------------|---------|---------|
| **Docker** | 24+ | Containerization |
| **Docker Compose** | 2+ | Multi-container orchestration |
| **Nginx** | 1.25 | Reverse proxy |
| **GitHub Actions** | - | CI/CD |

---

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

### Required

- **Docker** (24.0+) & **Docker Compose** (2.0+)
  ```bash
  docker --version
  docker compose version
  ```

### Optional (for manual setup)

- **PHP** 8.4+
- **Composer** 2.7+
- **Node.js** 20+ & **npm** 10+
- **PostgreSQL** 16+
- **Redis** 7+

---

## 🚀 Installation

### Docker Setup (Recommended)

This is the easiest way to get started. All services run in containers.

#### 1. Clone the Repository

```bash
git clone <repository-url>
cd shopify-integration
```

#### 2. Copy Environment Files

```bash
# Backend environment
cp backend/.env.example backend/.env

# Frontend environment
cp frontend/.env.example frontend/.env
```

#### 3. Configure Environment Variables

Edit `backend/.env`:

```env
# Application
APP_NAME="Shopify Integration"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=shopify_integration
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Shopify (Mock by default)
SHOPIFY_USE_MOCK=true
SHOPIFY_STORE_DOMAIN=your-store.myshopify.com
SHOPIFY_ACCESS_TOKEN=your-access-token
SHOPIFY_API_KEY=your-api-key
SHOPIFY_API_SECRET=your-api-secret
```

Edit `frontend/.env`:

```env
# API Base URL
NUXT_PUBLIC_API_BASE=http://localhost/api
```

#### 4. Start All Services

```bash
# Using Docker Compose
docker compose up -d

# Or using Makefile
make up
```

This will start:
- **Backend API** (Laravel) - `http://localhost/api`
- **Frontend App** (Nuxt) - `http://localhost:3000`
- **PostgreSQL** - `localhost:5432`
- **Redis** - `localhost:6379`
- **Nginx** - `localhost:80`

#### 5. Install Dependencies

```bash
# Backend dependencies
docker compose exec backend composer install

# Frontend dependencies
docker compose exec frontend npm install
```

#### 6. Run Database Migrations

```bash
# Using Docker Compose
docker compose exec backend php artisan migrate

# Or using Makefile
make migrate
```

#### 7. Seed Database (Optional)

```bash
# Sync some mock products
docker compose exec backend php artisan tinker
>>> $useCase = app(\Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductFromShopifyUseCase::class);
>>> $useCase->execute(new \Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductDTO('632910392'));
>>> $useCase->execute(new \Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductDTO('921728736'));
>>> exit
```

#### 8. Access the Application

- **Frontend:** http://localhost:3000
- **Backend API:** http://localhost/api/v1/products
- **Health Check:** http://localhost/api/v1/health

---

### Manual Setup

If you prefer to run services locally without Docker:

#### Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Copy environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# DB_HOST=127.0.0.1
# DB_DATABASE=shopify_integration
# DB_USERNAME=your_user
# DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Start development server
php artisan serve
# API available at http://localhost:8000/api
```

#### Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Copy environment
cp .env.example .env

# Configure API URL in .env
# NUXT_PUBLIC_API_BASE=http://localhost:8000/api

# Start development server
npm run dev
# App available at http://localhost:3000
```

---

## ⚙️ Configuration

### Backend Configuration

#### Shopify Settings

**Using Mock Client (Development):**
```env
SHOPIFY_USE_MOCK=true
```

**Using Real Shopify API:**
```env
SHOPIFY_USE_MOCK=false
SHOPIFY_STORE_DOMAIN=your-store.myshopify.com
SHOPIFY_ACCESS_TOKEN=shpat_xxxxx
SHOPIFY_API_KEY=xxxxx
SHOPIFY_API_SECRET=xxxxx
```

#### Cache Configuration

```env
CACHE_DRIVER=redis      # or 'file' for file-based cache
CACHE_PREFIX=shopify_
```

#### Queue Configuration

```env
QUEUE_CONNECTION=redis   # or 'database', 'sync'
```

### Frontend Configuration

```env
# API Base URL (without trailing slash)
NUXT_PUBLIC_API_BASE=http://localhost/api

# WebSocket URL (if using real-time features)
NUXT_PUBLIC_WS_URL=ws://localhost:6001
```

---

## 🗄️ Database Setup

### Database Schema

#### Products Table

```sql
CREATE TABLE products (
    id BIGSERIAL PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    shopify_id VARCHAR(255) UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'draft',
    price INTEGER DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'USD',
    inventory_quantity INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_created_at ON products(created_at);
```

#### Product Variants Table

```sql
CREATE TABLE product_variants (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT REFERENCES products(id) ON DELETE CASCADE,
    sku VARCHAR(50) UNIQUE NOT NULL,
    shopify_variant_id VARCHAR(255),
    price INTEGER DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'USD',
    inventory_quantity INTEGER DEFAULT 0,
    weight DECIMAL(10, 2),
    weight_unit VARCHAR(10),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX idx_variants_product_id ON product_variants(product_id);
CREATE INDEX idx_variants_sku ON product_variants(sku);
```

### Running Migrations

```bash
# Fresh migration (drops all tables and recreates)
php artisan migrate:fresh

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset

# Check migration status
php artisan migrate:status
```

---

## 🏃 Running the Application

### Using Docker (Recommended)

```bash
# Start all services
docker compose up -d

# View logs
docker compose logs -f

# Stop services
docker compose down

# Restart specific service
docker compose restart backend
```

### Using Makefile

```bash
# View available commands
make help

# Common commands
make up           # Start all services
make down         # Stop all services
make restart      # Restart all services
make migrate      # Run migrations
make test         # Run tests
make lint         # Run code linters
make logs         # View logs
```

### Development Servers

#### Backend

```bash
# Using Docker
docker compose exec backend php artisan serve --host=0.0.0.0 --port=8000

# Using local PHP
php artisan serve
```

#### Frontend

```bash
# Using Docker
docker compose exec frontend npm run dev

# Using local Node.js
npm run dev
```

#### Queue Worker

```bash
# Start queue worker
docker compose exec backend php artisan queue:work

# Start queue worker with auto-restart
docker compose exec backend php artisan queue:work --tries=3 --timeout=60
```

---

## 🧪 Testing

### Backend Tests

#### Run All Tests

```bash
# Using Docker
docker compose exec backend php artisan test

# Using local PHP
php artisan test
```

#### Run Specific Test Suites

```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature

# Specific test file
php artisan test tests/Unit/Domain/ValueObjects/SkuTest.php

# Specific test method
php artisan test --filter=it_creates_valid_sku
```

#### Test Coverage

```bash
# Generate coverage report (HTML)
php artisan test --coverage-html=coverage

# Coverage with minimum threshold
php artisan test --coverage --min=80

# Coverage summary in terminal
php artisan test --coverage
```

#### Parallel Testing

```bash
# Run tests in parallel (faster)
php artisan test --parallel
```

### Frontend Tests

#### Unit Tests

```bash
# Using Docker
docker compose exec frontend npm run test:unit

# Using local Node.js
npm run test:unit

# Watch mode
npm run test:unit -- --watch

# Coverage
npm run test:unit -- --coverage
```

#### E2E Tests

```bash
# Run E2E tests
npm run test:e2e

# Run in headed mode (see browser)
npm run test:e2e -- --headed

# Run specific test
npm run test:e2e tests/e2e/products.spec.ts
```

### Test Structure

```
tests/
├── Unit/                           # Fast, isolated tests
│   ├── Domain/
│   │   ├── Entities/
│   │   │   ├── ProductTest.php
│   │   │   └── ProductVariantTest.php
│   │   └── ValueObjects/
│   │       ├── SkuTest.php
│   │       ├── PriceTest.php
│   │       └── ProductStatusTest.php
│   └── Application/
│       └── UseCases/
│           └── SyncProductFromShopifyUseCaseTest.php
│
└── Feature/                        # HTTP endpoint tests
    └── Api/
        ├── ProductControllerTest.php
        ├── SyncControllerTest.php
        └── InventoryControllerTest.php
```

---

## 📚 API Documentation

### Base URL

- **Development:** `http://localhost/api/v1`
- **Production:** `https://your-domain.com/api/v1`

### Authentication

Currently no authentication required. Add JWT/Sanctum for production.

### Endpoints

#### Products

##### List Products

```http
GET /api/v1/products
```

**Query Parameters:**
- `page` (integer, optional) - Page number (default: 1)
- `per_page` (integer, optional) - Items per page (default: 10)

**Response:**
```json
{
  "data": [
    {
      "id": "632910392",
      "sku": "IPOD2008PINK",
      "title": "IPod Nano - 8GB",
      "description": "<p>It's the small iPod...</p>",
      "status": "active",
      "price": {
        "amount": 19900,
        "formatted": "$199.00",
        "currency": "USD"
      },
      "inventory": 10,
      "created_at": "2024-02-13 10:00:00",
      "updated_at": "2024-02-13 10:00:00"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 25
  }
}
```

##### Get Product by SKU

```http
GET /api/v1/products/{sku}
```

**Response:**
```json
{
  "data": {
    "id": "632910392",
    "sku": "IPOD2008PINK",
    "title": "IPod Nano - 8GB",
    "status": "active",
    "price": {
      "amount": 19900,
      "formatted": "$199.00",
      "currency": "USD"
    },
    "inventory": 10
  }
}
```

##### Delete Product

```http
DELETE /api/v1/products/{sku}
```

**Response:**
```json
{
  "message": "Product deleted successfully"
}
```

#### Sync

##### Sync Single Product

```http
POST /api/v1/sync/product
```

**Request Body:**
```json
{
  "shopify_id": "632910392",
  "force_update": true
}
```

**Response:**
```json
{
  "data": {
    "sku": "IPOD2008PINK",
    "title": "IPod Nano - 8GB",
    "status": "active"
  }
}
```

##### Bulk Sync (Queue)

```http
POST /api/v1/sync/bulk
```

**Request Body:**
```json
{
  "shopify_ids": ["632910392", "921728736", "789456123"]
}
```

**Response:**
```json
{
  "message": "Sync jobs queued",
  "count": 3
}
```

##### Bulk Sync (Immediate)

```http
POST /api/v1/sync/bulk/immediate
```

**Request Body:**
```json
{
  "shopify_ids": ["632910392", "921728736"],
  "skip_duplicates": true
}
```

**Response:**
```json
{
  "success_count": 2,
  "failed_count": 0,
  "errors": []
}
```

#### Inventory

##### Update Inventory

```http
PUT /api/v1/inventory
```

**Request Body:**
```json
{
  "sku": "IPOD2008PINK",
  "quantity": 50
}
```

**Response:**
```json
{
  "message": "Inventory updated successfully",
  "data": {
    "sku": "IPOD2008PINK",
    "previous_quantity": 10,
    "new_quantity": 50
  }
}
```

#### Health Check

```http
GET /api/v1/health
```

**Response:**
```json
{
  "status": "ok"
}
```

### Error Responses

#### 404 Not Found

```json
{
  "error": "Product not found",
  "message": "Product with SKU 'INVALID' does not exist"
}
```

#### 422 Validation Error

```json
{
  "message": "The shopify id field is required.",
  "errors": {
    "shopify_id": [
      "The shopify id field is required."
    ]
  }
}
```

#### 400 Bad Request

```json
{
  "error": "Sync failed",
  "message": "Failed to sync product 123456: Product not found in Shopify"
}
```

---

## 🎨 Code Quality

### Linting & Formatting

#### Backend (PHP)

```bash
# Laravel Pint (code style)
docker compose exec backend composer lint

# Check without fixing
docker compose exec backend composer lint:check

# PHPStan (static analysis)
docker compose exec backend composer analyse

# PHPStan strict mode
docker compose exec backend composer analyse:strict
```

#### Frontend (TypeScript/Vue)

```bash
# ESLint
docker compose exec frontend npm run lint

# Fix automatically
docker compose exec frontend npm run lint:fix

# Type check
docker compose exec frontend npm run type-check
```

### Code Style Configuration

#### PHP (Pint)

```json
{
  "preset": "laravel",
  "rules": {
    "declare_strict_types": true,
    "final_class": true,
    "no_unused_imports": true
  }
}
```

#### TypeScript (ESLint)

```js
{
  "extends": [
    "@nuxtjs/eslint-config-typescript",
    "plugin:vue/vue3-recommended"
  ],
  "rules": {
    "@typescript-eslint/no-unused-vars": "error",
    "vue/multi-word-component-names": "off"
  }
}
```

---

## 🔄 CI/CD Pipeline

### GitHub Actions Workflows

#### CI Workflow (`ci.yml`)

Runs on every push and pull request:

1. **Backend Tests**
   - PHP 8.4 setup
   - Composer install
   - PostgreSQL service
   - Redis service
   - Run PHPUnit tests
   - Upload coverage

2. **Backend Code Quality**
   - Laravel Pint check
   - PHPStan Level 8

3. **Frontend Tests**
   - Node.js 20 setup
   - npm install
   - Vitest unit tests
   - ESLint check

#### Build & Deploy Workflow (`build-deploy.yml`)

Runs on push to `main` or `develop`:

1. **Build Docker Images**
   - Backend image
   - Frontend image
   - Push to GitHub Container Registry (GHCR)

2. **Deploy to Staging**
   - Triggered on `develop` branch
   - Deploy to staging server

3. **Deploy to Production**
   - Triggered on `main` branch
   - Requires manual approval
   - Deploy to production server

### Required Secrets

Configure these in GitHub repository settings:

| Secret | Description |
|--------|-------------|
| `STAGING_HOST` | Staging server IP address |
| `STAGING_USER` | SSH username for staging |
| `PRODUCTION_HOST` | Production server IP address |
| `PRODUCTION_USER` | SSH username for production |
| `SSH_PRIVATE_KEY` | SSH private key for deployment |

---

## 💻 Development Workflow

### 1. Create Feature Branch

```bash
git checkout -b feature/product-export
```

### 2. Write Tests First (TDD)

```bash
# Create test file
touch tests/Unit/Domain/Services/ProductExportServiceTest.php

# Write failing test
php artisan test --filter=ProductExportServiceTest
```

### 3. Implement Feature

```bash
# Create service
touch src/Domain/Product/Services/ProductExportService.php

# Implement logic
# Run tests until green
php artisan test --filter=ProductExportServiceTest
```

### 4. Run Quality Checks

```bash
# Code style
composer lint

# Static analysis
composer analyse

# All tests
php artisan test
```

### 5. Commit Changes

```bash
git add .
git commit -m "feat: add product export service"
```

### 6. Push and Create PR

```bash
git push origin feature/product-export
# Create pull request on GitHub
```

---

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Failed

**Problem:** `SQLSTATE[08006] Connection refused`

**Solution:**
```bash
# Check if PostgreSQL is running
docker compose ps postgres

# Restart PostgreSQL
docker compose restart postgres

# Check environment variables
cat backend/.env | grep DB_
```

#### Port Already in Use

**Problem:** `Error: bind: address already in use`

**Solution:**
```bash
# Find process using port 80
sudo lsof -i :80

# Kill process
sudo kill -9 <PID>

# Or change port in docker-compose.yml
```

#### Composer Dependencies Not Found

**Problem:** `Class not found` errors

**Solution:**
```bash
# Rebuild autoload
docker compose exec backend composer dump-autoload

# Clear cache
docker compose exec backend php artisan cache:clear
docker compose exec backend php artisan config:clear
```

#### Frontend Build Errors

**Problem:** `Module not found` or TypeScript errors

**Solution:**
```bash
# Clear node_modules and reinstall
docker compose exec frontend rm -rf node_modules
docker compose exec frontend npm install

# Clear Nuxt cache
docker compose exec frontend rm -rf .nuxt
docker compose exec frontend npm run dev
```

#### Queue Not Processing

**Problem:** Jobs stuck in queue

**Solution:**
```bash
# Check queue worker
docker compose exec backend php artisan queue:work --once

# Clear failed jobs
docker compose exec backend php artisan queue:flush

# Restart queue worker
docker compose restart backend
```

### Debug Mode

Enable debug mode in `.env`:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

View logs:

```bash
# Application logs
docker compose logs -f backend

# Laravel logs
tail -f backend/storage/logs/laravel.log

# Nginx logs
docker compose logs -f nginx
```

---

## 🚀 Production Deployment

### Prerequisites

- VPS/Server with Docker & Docker Compose
- Domain name with DNS configured
- SSL certificate (Let's Encrypt recommended)

### Deployment Steps

#### 1. Server Setup

```bash
# Install Docker
curl -fsSL https://get.docker.com | sh

# Install Docker Compose
sudo apt-get install docker-compose-plugin

# Clone repository
git clone <repository-url> /var/www/shopify-integration
cd /var/www/shopify-integration
```

#### 2. Production Configuration

```bash
# Copy production env
cp backend/.env.prod.example backend/.env
cp frontend/.env.prod.example frontend/.env

# Edit backend/.env
nano backend/.env
```

Set production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=postgres
DB_DATABASE=shopify_production
DB_USERNAME=shopify_user
DB_PASSWORD=strong_password_here

REDIS_PASSWORD=redis_password_here

SHOPIFY_USE_MOCK=false
SHOPIFY_STORE_DOMAIN=your-store.myshopify.com
SHOPIFY_ACCESS_TOKEN=shpat_xxxxx
```

#### 3. SSL Setup (Let's Encrypt)

```bash
# Install certbot
sudo apt-get install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal
sudo certbot renew --dry-run
```

#### 4. Build & Deploy

```bash
# Build production images
docker compose -f docker-compose.prod.yml build

# Start services
docker compose -f docker-compose.prod.yml up -d

# Run migrations
docker compose -f docker-compose.prod.yml exec backend php artisan migrate --force

# Optimize Laravel
docker compose -f docker-compose.prod.yml exec backend php artisan config:cache
docker compose -f docker-compose.prod.yml exec backend php artisan route:cache
docker compose -f docker-compose.prod.yml exec backend php artisan view:cache
```

#### 5. Monitoring

```bash
# Check services
docker compose -f docker-compose.prod.yml ps

# View logs
docker compose -f docker-compose.prod.yml logs -f
```

### Zero-Downtime Deployment

```bash
# Pull latest changes
git pull origin main

# Build new images
docker compose -f docker-compose.prod.yml build

# Rolling update
docker compose -f docker-compose.prod.yml up -d --no-deps --build backend
docker compose -f docker-compose.prod.yml up -d --no-deps --build frontend
```

---

## ⚡ Performance Optimization

### Backend Optimization

#### 1. Enable OpCache

Add to `docker/php-fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

#### 2. Database Indexing

```sql
-- Already included in migrations
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_created_at ON products(created_at);
CREATE INDEX idx_variants_product_id ON product_variants(product_id);
```

#### 3. Query Optimization

```php
// Always eager load relationships
$products = ProductModel::with('variants')->get();

// Use select to limit columns
$products = ProductModel::select(['id', 'sku', 'title'])->get();
```

#### 4. Redis Caching

```php
// Cache expensive queries
$products = Cache::remember('products_page_1', 300, function () {
    return ProductModel::with('variants')->paginate(10);
});

// Use cache tags for easy invalidation
Cache::tags(['products'])->put('product_123', $product, 3600);
Cache::tags(['products'])->flush();
```

### Frontend Optimization

#### 1. Code Splitting

```typescript
// Use dynamic imports for large components
const ProductEditor = defineAsyncComponent(() => 
  import('~/components/products/ProductEditor.vue')
);
```

#### 2. Image Optimization

```bash
# Use Nuxt Image module
npm install @nuxt/image

# nuxt.config.ts
modules: ['@nuxt/image']
```

#### 3. API Caching

```typescript
// Cache API responses in Pinia store
const productStore = useProductStore();
await productStore.fetchProducts(page); // Caches internally
```

---

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

### Branching Strategy

- `main` - Production-ready code
- `develop` - Development branch
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Urgent production fixes

### Commit Message Format

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add product export feature
fix: resolve SKU validation issue
docs: update API documentation
test: add tests for ProductService
refactor: simplify cache invalidation
chore: update dependencies
```

### Pull Request Process

1. Create feature branch from `develop`
2. Write tests for new functionality
3. Ensure all tests pass
4. Update documentation
5. Submit PR to `develop` branch
6. Wait for code review
7. Address feedback
8. Merge after approval

---

## 📄 License

This project is licensed under the MIT License.

```
MIT License

Copyright (c) 2024

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 📞 Support

For questions and support:

- **Documentation:** See `/docs` folder
- **Issues:** GitHub Issues
- **Discussions:** GitHub Discussions

---

## 🙏 Acknowledgments

- **Laravel Community** - For the amazing framework
- **Vue.js Team** - For the reactive framework
- **Shopify** - For the comprehensive API

---

Made with ❤️ using Clean Architecture & SOLID Principles
