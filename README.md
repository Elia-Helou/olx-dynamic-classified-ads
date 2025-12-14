# OLX Classified Ads API

Laravel RESTful API for classified ads with dynamic, category-specific fields.

## Setup

```bash
composer install
cp .env.example .env
```

Configure your database credentials in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Then run:
```bash
php artisan key:generate
php artisan migrate
```

## Seeding Data

The seeders fetch categories and fields from OLX API:

```bash
php artisan db:seed
```

Or run individually:
```bash
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=CategoryFieldSeeder
```

Seeders are idempotent - safe to run multiple times.

## API Endpoints

All endpoints require Sanctum authentication.

### Create Ad
```
POST /api/v1/ads
```

Body:
```json
{
  "category_id": 2,
  "title": "Ad Title",
  "description": "Ad description",
  "price": 10000,
  "field_external_id": "value"
}
```

Dynamic fields use their `external_id` as keys. Required fields are validated based on category.

### List My Ads
```
GET /api/v1/my-ads?per_page=15
```

Returns paginated list of authenticated user's ads.

### View Ad
```
GET /api/v1/ads/{id}
```

Returns single ad with all dynamic field values.

## Authentication

Get token from seeded user:
```bash
php artisan tinker
```
```php
User::first()->createToken('api-token')->plainTextToken
```

Use in requests:
```
Authorization: Bearer {token}
```

## Syncing Data

Sync categories and fields from OLX API:

```bash
php artisan olx:sync-categories
php artisan olx:sync-category-fields
```

Force refresh (clears cache):
```bash
php artisan olx:sync-categories --force
php artisan olx:sync-category-fields --force
```

Clear cache manually:
```bash
php artisan olx:clear-cache
```

## Caching

API responses are cached for 24 hours. Cache is automatically cleared daily at 1:55 AM before syncing.

## Testing

```bash
php artisan test
```

Feature tests cover all API endpoints with success and validation scenarios.
