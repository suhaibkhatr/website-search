# Website-Wide Search (Laravel + Scout + Meilisearch)

Unified, backend-only search across **BlogPosts, Products, Pages, and FAQs**.  
Built with **Laravel**, **Laravel Scout**, and **Meilisearch** — with **queued indexing** and clean JSON APIs.

---

## Features
- Unified search endpoint: `/api/search?q=...`
- Typeahead suggestions: `/api/search/suggestions?q=...`
- (Optional) Search logs + top terms: `/api/search/logs`
- Partial/fuzzy matching via Meilisearch
- Combined pagination + basic recency ordering
- Auto index sync on create/update/delete (queued with Redis)

---

## Tech Stack
- Laravel (Sail)
- Docker services: **MySQL**, **Redis**, **Meilisearch**
- Laravel Scout (+ Meilisearch PHP SDK)

---

## Quick Start

### Prerequisites
- **Docker Desktop** (recommended)
- Alternatively: PHP 8.2+ & Composer if you prefer running Composer locally

### 1) Clone & install
```bash
git clone <your-repo-url>.git
cd website-wide-search
composer install
```

> If you don’t have PHP/Composer, you can still continue after Step 2 using Sail (Docker).

### 2) Enable Sail services
```bash
composer require laravel/sail --dev
php artisan sail:install
# Choose: mysql, redis, meilisearch
```

### 3) Boot the stack
```bash
./vendor/bin/sail up -d
```

### 4) Configure environment
Open `.env` and confirm:
```
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
QUEUE_CONNECTION=redis
SCOUT_QUEUE=true
```

### 5) App key, migrate, seed, import indexes
```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed

# First full import (bootstrap the indexes)
./vendor/bin/sail artisan scout:import "App\Models\BlogPost"
./vendor/bin/sail artisan scout:import "App\Models\Product"
./vendor/bin/sail artisan scout:import "App\Models\Page"
./vendor/bin/sail artisan scout:import "App\Models\FAQ"
```

### 6) Run the queue worker (for async indexing)
```bash
./vendor/bin/sail artisan queue:work
```

Now hit the APIs at `http://localhost`.

---

## API

### `GET /api/search`
Unified search across all types.

**Query Params**
- `q` *(required)* — query string
- `page` *(default: 1)*
- `per_page` *(default: 10)*

**Example**
```
GET http://localhost/api/search?q=iphone&per_page=10&page=1
```

**Response (shape)**
```json
{
  "data": [
    {
      "id": "42",
      "type": "product",
      "title": "Wireless Headphones Z",
      "snippet": "Lightweight over-ear ...",
      "link": "http://localhost/products/42",
      "recency": 1728579812
    }
  ],
  "meta": {
    "query": "iphone",
    "page": 1,
    "per_page": 10,
    "total": 76,
    "total_by_type": { "blog": 12, "product": 50, "page": 3, "faq": 11 }
  }
}
```

### `GET /api/search/suggestions`
Typeahead strings from titles/names/questions.
```
GET http://localhost/api/search/suggestions?q=iph
```

### `GET /api/search/logs` *(optional/demo)*
Top search terms (optionally filter by date).
```
GET http://localhost/api/search/logs?limit=20&since=2025-01-01
```

---

## Data Model
- **BlogPosts**: `title, body, tags, published_at`
- **Products**: `name, description, category, price`
- **Pages**: `title, content`
- **FAQs**: `question, answer`

All models:
- `use Laravel\Scout\Searchable;`
- `$afterCommit = true;` (index updates after DB commit)
- `toSearchableArray()` trims/strips HTML for clean indexing

---

## Index Reliability
- **Queued indexing**: `SCOUT_QUEUE=true` + `queue:work`
- **After commit**: `$afterCommit = true` on models
- **Soft deletes** *(optional)*:
  - Set `'soft_delete' => true` in `config/scout.php`
  - Add `SoftDeletes` to models
- **Bulk re-import** (safety net):
  ```bash
  ./vendor/bin/sail artisan search:rebuild
  ```

---

## Health & Troubleshooting
- Meilisearch health:
  ```bash
  curl http://localhost:7700/health
  # {"status":"available"}
  ```
- If `/api/search` returns HTML (error page), test with JSON header:
  ```bash
  curl -H "Accept: application/json" "http://localhost/api/search?q=test"
  ```
- Empty results? Ensure:
  - `migrate --seed` ran
  - `scout:import` ran for each model
  - `queue:work` is running (for subsequent changes)
- Restart services:
  ```bash
  ./vendor/bin/sail up -d
  ./vendor/bin/sail restart
  ```

---

## Development Notes
- Unified search logic: `app/Http/Controllers/SearchController.php`
- Suggestions: `SearchController@suggestions`
- Search logging middleware: `app/Http/Middleware/LogSearchQuery.php`
- Top terms endpoint: `app/Http/Controllers/SearchLogController.php`
- Rebuild indexes: `app/Console/Commands/RebuildSearchIndex.php`

---
