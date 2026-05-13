# StockCraft — Complete Setup Guide

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL or SQLite

---

## Step 1 — Create the Laravel Project

```bash
composer create-project laravel/laravel stockcraft
cd stockcraft
```

---

## Step 2 — Install Breeze (auth scaffolding)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

When prompted: choose **Blade with Alpine**, dark mode: **No**, testing: **PHPUnit**

---

## Step 3 — Copy All Project Files

Copy every file from this repository into your Laravel project root, maintaining the exact folder structure:

```
app/
  Http/Controllers/
    CategoryController.php
    DashboardController.php
    GeneratorController.php
    ProductController.php
    ReportController.php
    StockMovementController.php
  Models/
    Category.php
    Product.php
    StockMovement.php
  Policies/
    ProductPolicy.php
  Providers/
    AppServiceProvider.php        ← replaces the default one
  Services/
    InventoryGeneratorService.php
database/
  migrations/
    2024_01_01_000001_create_inventory_tables.php
resources/
  css/app.css
  js/app.js
  js/bootstrap.js
  views/
    layouts/app.blade.php
    dashboard.blade.php
    generator/index.blade.php
    products/index.blade.php
    products/create.blade.php
    products/edit.blade.php
    products/show.blade.php
    movements/index.blade.php
    categories/index.blade.php
    reports/index.blade.php
    reports/low-stock.blade.php
    reports/expiry.blade.php
    reports/stock-value.blade.php
    reports/movements.blade.php
    vendor/pagination/tailwind.blade.php
routes/
  web.php
tailwind.config.js
vite.config.js
package.json
```

---

## Step 4 — Configure .env

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_NAME=StockCraft
APP_URL=http://localhost:8000

# Option A: MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stockcraft
DB_USERNAME=root
DB_PASSWORD=your_password

# Option B: SQLite (easier for local dev)
DB_CONNECTION=sqlite
# Then create the file:
# touch database/database.sqlite
```

---

## Step 5 — Database Setup

```bash
# If using MySQL, create the database first:
mysql -u root -p -e "CREATE DATABASE stockcraft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations (creates all tables including auth tables from Breeze)
php artisan migrate

# Create storage symlink for product images
php artisan storage:link
```

---

## Step 6 — Install JS Dependencies & Build Assets

```bash
npm install
npm run build
```

For development with hot reload:

```bash
npm run dev
# (keep this running in a separate terminal)
```

---

## Step 7 — Run the App

```bash
php artisan serve
```

Visit: **http://localhost:8000**

Register a new account → you'll land on the Dashboard.

---

## Step 8 — First Steps Inside the App

1. Go to **Generate Inventory** in the sidebar
2. Select a business type (e.g. Pharmacy)
3. Set count to 50, stock level to Medium
4. Enable Categories and Expiry Dates
5. Click **Generate** — products will be created instantly
6. Explore **Products**, **Reports**, and **Stock Movements**

---

## Troubleshooting

| Problem                                        | Fix                                                                  |
| ---------------------------------------------- | -------------------------------------------------------------------- |
| `Class 'App\Policies\ProductPolicy' not found` | Run `php artisan optimize:clear`                                     |
| Styles not loading                             | Run `npm run build`                                                  |
| Images not showing                             | Run `php artisan storage:link`                                       |
| `SQLSTATE` errors                              | Check `.env` DB credentials and run `php artisan migrate`            |
| AlpineJS not working                           | Check `resources/js/app.js` imports Alpine correctly                 |
| Pagination looks unstyled                      | Ensure `resources/views/vendor/pagination/tailwind.blade.php` exists |

---

## Artisan Commands Reference

```bash
php artisan migrate:fresh          # Drop all tables and re-migrate (DEV only)
php artisan migrate:fresh --seed   # Fresh + seed with dummy data
php artisan optimize:clear         # Clear all caches
php artisan route:list             # See all registered routes
php artisan storage:link           # Create public storage symlink
```

---

## Project Structure Summary

```
Feature              Route                    Controller Method
─────────────────────────────────────────────────────────────
Dashboard            /dashboard               DashboardController@index
Generate Inventory   /generate                GeneratorController@index/generate
Products List        /products                ProductController@index
Add Product          /products/create         ProductController@create/store
Edit Product         /products/{id}/edit      ProductController@edit/update
Product Detail       /products/{id}           ProductController@show
Export CSV           /products/export/csv     ProductController@exportCsv
Import CSV           /products/import/csv     ProductController@importCsv
Stock Movements      /movements               StockMovementController@index/store
Categories           /categories              CategoryController@index/store
Report Hub           /reports                 ReportController@index
Low Stock Report     /reports/low-stock       ReportController@lowStock
Expiry Report        /reports/expiry          ReportController@expiry
Stock Value Report   /reports/stock-value     ReportController@stockValue
Movement Report      /reports/movements       ReportController@movements
```
