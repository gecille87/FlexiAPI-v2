
# FlexiAPI 

Current Version : `v2.0.2`

Versions:  
[Raw php - 1.0](https://github.com/gecille87/FlexiAPI)  
[Library Composer -  2.0.2](https://packagist.org/packages/gecille-uptura/flexiapi)


Sample Usage - [Project AbsiDee](https://github.com/gecille87/AbSiDee)

**FlexiAPI** is a plug-and-play **pure PHP library** that generates a safe and dynamic JSON API layer over your database.  
It handles repetitive CRUD operations (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) automatically, while supporting pagination, filtering, sorting, and Postman collection generation — so you can focus on building apps, not writing boilerplate SQL.

---

## Table of Contents
- [Introduction](#flexiapi)
- [Features](#features)
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running Locally](#running-locally)
- [API Endpoints](#api-endpoints)
  - [1. GET — Fetch rows](#1-get--fetch-rows)
  - [2. CREATE — Insert rows](#2-create--insert-rows)
  - [3. UPDATE — Update rows](#3-update--update-rows)
  - [4. DELETE — Delete rows](#4-delete--delete-rows)
- [Guide to Adding Custom SELECT Methods](#guide-to-adding-custom-select-methods)
  - [What Are Custom Methods?](#what-are-custom-methods)
  - [File Structure (Relevant Parts)](#file-structure-relevant-parts)
  - [Step 1 – Define Your Custom Method](#step-1--define-your-custom-method)
  - [Step 2 – Call Your Custom Method via API](#step-2--call-your-custom-method-via-api)
  - [Step 3 – Example of Aggregation Query](#step-3--example-of-aggregation-query)
  - [Step 4 – Error Handling](#step-4--error-handling)
  - [Step 5 – Include in Postman Collection](#step-5--include-in-postman-collection)
  - [Summary](#summary)
- [Testing with Postman](#testing-with-postman)
- [Security Features](#security-features)
- [Deployment](#deployment)
- [Roadmap](#roadmap)
- [Contributing](#contributing)
- [License](#license)

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
composer require gecille-uptura/flexiapi
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


# Guide to Adding Custom SELECT Methods

This guide explains how to extend **FlexiAPI** with your own **complex SELECT queries** (or other database operations) using the **Custom Methods** feature.

---

## What Are Custom Methods?

FlexiAPI provides a `CustomController` that lets you define **user-specific SQL queries** (beyond simple `get`, `create`, `update`, `delete`).
These methods are stored in `src/Custom/UserMethods.php` and are automatically accessible through the API.

This makes it easy to expose **complex reporting queries, joins, aggregations, or stored procedure calls** as simple API endpoints.

---

## File Structure (Relevant Parts)

```
├─ src/
│  ├─ Controllers/
│  │  ├─ CustomController.php   # Handles custom methods
│  ├─ Custom/
│  │  └─ UserMethods.php        # Place your custom SQL logic here
│  ├─ DB/
│  │  └─ MySQLAdapter.php       # Executes queries (returns arrays)
```

---

## Step 1 – Define Your Custom Method

Open `src/Custom/UserMethods.php`.
This file **returns an associative array** where the key is the method name, and the value is a function that takes two parameters:

```php
<?php

return [
    'getTopUsers' => function($db, $params) {
        $limit = (int)($params['limit'] ?? 5);
        $sql   = "SELECT id, username, email 
                  FROM users 
                  WHERE status = 'active'
                  ORDER BY created_at DESC 
                  LIMIT {$limit}";
        return $db->query($sql); //  $db->query() returns an array
    },

    'userStats' => function($db, $params) {
        $sql = "SELECT status, COUNT(*) as count 
                FROM users 
                GROUP BY status";
        return $db->query($sql);
    }
];
```

* `$db` is your **database adapter** (e.g., `MySQLAdapter`).
* `$params` is an array of values passed from the API request body.
* Always return an **array** (the adapter already fetches results).

---

## Step 2 – Call Your Custom Method via API

Send a JSON request with `action=custom` and `method=<your_method_name>`.

### Example: `getTopUsers`

```http
POST /index.php
Content-Type: application/json
X-FlexiAPI-Key: secret123

{
  "action": "custom",
  "method": "getTopUsers",
  "params": { "limit": 3 }
}
```

### Example Response

```json
{
  "status": true,
  "message": "Custom method 'getTopUsers' executed successfully",
  "data": [
    { "id": 10, "username": "alice", "email": "alice@example.com" },
    { "id": 9, "username": "bob", "email": "bob@example.com" },
    { "id": 7, "username": "charlie", "email": "charlie@example.com" }
  ],
  "error": null
}
```

---

##  Step 3 – Example of Aggregation Query

Add a new method to `UserMethods.php`:

```php
'activeUserCount' => function($db) {
    $sql  = "SELECT COUNT(*) AS total FROM users WHERE status = 'active'";
    $rows = $db->query($sql);
    return $rows[0] ?? ['total' => 0];
}
```

Call it with:

```json
{
  "action": "custom",
  "method": "activeUserCount"
}
```

**Response:**

```json
{
  "status": true,
  "message": "Custom method 'activeUserCount' executed successfully",
  "data": { "total": 42 },
  "error": null
}
```

---

## Step 4 – Error Handling

If a custom method is missing or fails:

```json
{
  "status": false,
  "message": "Custom method 'getUnknownStuff' not found",
  "data": null,
  "error": "Custom method 'getUnknownStuff' not found"
}
```

---

##  Step 5 – Include in Postman Collection

Your `bin/generate_postman.php` can be updated to **auto-load `UserMethods.php`** so every custom method appears in the collection. Example snippet:

```php
$customFile = __DIR__ . '/../src/Custom/UserMethods.php';
if (file_exists($customFile)) {
    $methods = include $customFile;
    foreach (array_keys($methods) as $name) {
        $collection['item'][] = [
            'name' => "Custom: $name",
            'request' => [
                'method' => 'POST',
                'header' => $headers,
                'url'    => [ 'raw' => $baseUrl . '/index.php', 'host' => [$baseUrl], 'path' => ['index.php'] ],
                'body'   => [
                    'mode' => 'raw',
                    'raw'  => json_encode([
                        'action' => 'custom',
                        'method' => $name,
                        'params' => new \stdClass() // empty object for Postman
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
}
```

---

## Summary

1. Add your SQL logic inside `src/Custom/UserMethods.php`.
2. Call it with `{ "action": "custom", "method": "yourMethodName", "params": {...} }`.
3. Response will always be in FlexiAPI JSON format.
4. Postman generator can automatically pick them up.


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

* [x] Dynamic JSON API layer
* [x] Custom SELECT Methods
* [ ] Custom route support (`/api/custom/...`)
* [ ] PostgreSQL & SQLite adapters
* [ ] Role-based access control
* [ ] Schema migrations
* [ ] Query caching & performance tuning

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you’d like to change.

---

## License

[MIT](LICENSE)