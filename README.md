
# FlexiAPI 

Current Version : `v2.0.0`

Versions:  
[Raw php - 1.0](https://github.com/gecille87/FlexiAPI)  
[Library Composer -  2.0.0]()


**FlexiAPI** is a plug-and-play **pure PHP library** that generates a safe and dynamic JSON API layer over your database.  
It handles repetitive CRUD operations (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) automatically, while supporting pagination, filtering, sorting, and Postman collection generation — so you can focus on building apps, not writing boilerplate SQL.  

---

## Features

- ✅ Pure PHP — no frameworks required  
- ✅ Safe SQL handling via PDO prepared statements  
- ✅ Dynamic CRUD operations (`get`, `create`, `update`, `delete`)  
- ✅ Pagination, filtering, sorting out-of-the-box  
- ✅ Input validation & table whitelisting for security  
- ✅ Bulk insert and safe delete with limit  
- ✅ Automatic Postman collection generator (`bin/generate_postman.php`)  
- ✅ JSON standard responses (consistent across all endpoints)  
- ✅ Easy installation via Composer  

---

## Project Structure

```

flexiapi/
├─ bin/                        # CLI scripts
│  └─ generate_postman.php     # Generate Postman collection JSON
├─ config/
│  └─ config.php               # DB credentials, API key, whitelist
├─ public/
│  └─ index.php                # Single API entrypoint
├─ src/
│  ├─ Core/
│  │  ├─ FlexiAPI.php          # Bootstrap & request handling
│  │  └─ Router.php            # (Optional) route extension
│  ├─ Controllers/
│  │  ├─ BaseController.php    # Common controller logic
│  │  └─ TableController.php   # CRUD controller
│  ├─ DB/
│  │  ├─ DBAdapterInterface.php
│  │  └─ MySQLAdapter.php      # PDO MySQL implementation
│  ├─ Services/
│  │  └─ QueryBuilder.php      # Safe query builder
│  ├─ Validators/
│  │  └─ InputValidator.php    # Centralized input validation
│  └─ Utils/
│     └─ Response.php          # JSON response helper
├─ tests/                      # PHPUnit tests (optional)
├─ composer.json
└─ README.md

````

---

## Installation

Require via Composer:

```bash
composer require your-vendor/flexiapi
````

---

## Configuration

Edit `config/config.php` with your DB credentials and security settings:

```php
<?php
return [
  'db' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'my_database',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'whitelist_tables' => ['users', 'products'] // Only allow API access to these tables
  ],
  'api' => [
    'key' => 'your-secret-token',   // API Key required in requests
    'max_limit' => 100,             // Prevent heavy queries
    'default_limit' => 20
  ]
];
```

---

## Running Locally

Start a PHP development server:

```bash
php -S 127.0.0.1:8080 -t public
```

Your API is now available at:

```
http://127.0.0.1:8080/index.php
```

Use the header:

```
X-FlexiAPI-Key: your-secret-token
```

---

## API Endpoints

FlexiAPI supports four main operations: `get`, `create`, `update`, `delete`.
All requests return **standard JSON**:

```json
{
  "status": true,
  "message": "Data retrieved successfully",
  "data": [],
  "error": null,
  "pagination": {
    "current_page": 1,
    "limit": 20,
    "total_rows": 0,
    "total_pages": 0
  }
}
```

---

### 1. GET — Fetch rows

**Request (GET):**

```bash
curl -G "http://127.0.0.1:8080/index.php" \
 -H "X-FlexiAPI-Key: your-secret-token" \
 --data-urlencode "table=users" \
 --data-urlencode "columns=id,name,email" \
 --data-urlencode 'condition=[{"field":"status","operator":"=","value":"active"}]' \
 --data-urlencode "page=1" \
 --data-urlencode "limit=5"
```

**Response:**

```json
{
  "status": true,
  "message": "Data retrieved successfully",
  "data": [
    { "id": 1, "name": "Alice", "email": "alice@example.com", "status": "active" }
  ],
  "pagination": {
    "current_page": 1,
    "limit": 5,
    "total_rows": 1,
    "total_pages": 1
  },
  "error": null
}
```

---

### 2. CREATE — Insert rows

**Request (POST):**

```bash
curl -X POST "http://127.0.0.1:8080/index.php" \
 -H "Content-Type: application/json" \
 -H "X-FlexiAPI-Key: your-secret-token" \
 -d '{
   "table": "users",
   "data": [
     {"name": "Jane", "email": "jane@example.com", "age": 22},
     {"name": "Paul", "email": "paul@example.com", "age": 29}
   ]
 }'
```

**Response:**

```json
{
  "status": true,
  "message": "Rows inserted successfully",
  "data": { "inserted": 2 },
  "error": null
}
```

---

### 3. UPDATE — Update rows

**Request (PUT):**

```bash
curl -X PUT "http://127.0.0.1:8080/index.php" \
 -H "Content-Type: application/json" \
 -H "X-FlexiAPI-Key: your-secret-token" \
 -d '{
   "table": "users",
   "where": { "field": "id", "operator": "=", "value": 1 },
   "data": { "status": "inactive" }
 }'
```

**Response:**

```json
{
  "status": true,
  "message": "Rows updated successfully",
  "data": { "affected": 1 },
  "error": null
}
```

---

### 4. DELETE — Delete rows

**Request (DELETE):**

```bash
curl -X DELETE "http://127.0.0.1:8080/index.php" \
 -H "Content-Type: application/json" \
 -H "X-FlexiAPI-Key: your-secret-token" \
 -d '{
   "table": "users",
   "column": "id",
   "values": [2],
   "limit": 1
 }'
```

**Response:**

```json
{
  "status": true,
  "message": "Rows deleted successfully",
  "data": { "affected": 1 },
  "error": null
}
```

---

## Testing with Postman

FlexiAPI includes a CLI generator to create a ready-to-import Postman collection.

```bash
php bin/generate_postman.php > postman_collection.json
```

Import `postman_collection.json` into Postman.
Set variables in Postman:

* `{{baseUrl}}` → `http://127.0.0.1:8080`
* `{{apiKey}}` → your API key

You now have GET/POST/PUT/DELETE requests pre-configured!

---

## Security Features

* All queries use **prepared statements** (no raw SQL injection).
* **Table whitelist** prevents exposing unwanted tables.
* **Column validation** allows only safe names (`a-z0-9_`).
* **API key authentication** via `X-FlexiAPI-Key` header.
* **Limit & pagination enforcement** prevents heavy queries.
* Transactions for insert/delete to keep DB consistent.

---

## Deployment

1. Copy your project to server (`public/` is the web root).
2. Update `config/config.php` with production DB credentials.
3. Configure Apache/Nginx to point `/api` → `public/index.php`.
4. Use HTTPS + rotate API keys for security.

---

## Roadmap

* [ ] PostgreSQL & SQLite adapters
* [ ] Role-based access control
* [ ] Schema migrations
* [ ] Custom route support (`/api/custom/...`)
* [ ] Query caching & performance tuning

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you’d like to change.

---

## License

[MIT](LICENSE)