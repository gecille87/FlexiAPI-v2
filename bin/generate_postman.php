<?php

/**
 * FlexiAPI Postman Collection Generator
 *
 * Usage:
 *   php bin/generate_postman.php > postman_collection.json
 */

require __DIR__ . '/../vendor/autoload.php';

use FlexiAPI\Controllers\CustomController;
use FlexiAPI\DB\MySQLAdapter;

// 🔹 Global Config
$config   = require __DIR__ . '/../config/config.php';
$baseUrl  = '{{baseUrl}}';   // Postman variable for API host
$apiKey   = '{{apiKey}}';    // Postman variable for API key

// 🔹 Default Headers
$headers  = [
    ['key' => 'X-FlexiAPI-Key', 'value' => $apiKey]
];

// 🔹 Base Postman Collection Skeleton
$collection = [
    'info' => [
        'name'   => 'FlexiAPI Collection',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
    ],
    'item' => []
];

// 🔹 Example CRUD requests (TableController)
$examples = [
    'Create (Insert Rows)' => [
        'method' => 'POST',
        'url'    => $baseUrl . '/index.php',
        'body'   => [
            "action" => "create",
            "table"  => "users",
            "data"   => [
                ["name" => "Jane", "email" => "jane@example.com", "age" => 22],
                ["name" => "Paul", "email" => "paul@example.com", "age" => 29]
            ]
        ]
    ],
    'Get (Select Rows)' => [
        'method' => 'GET',
        'url'    => $baseUrl . '/index.php',
        'query'  => [
            ["key" => "action", "value" => "get"],
            ["key" => "table", "value" => "users"],
            ["key" => "columns", "value" => "id,name,email"],
            ["key" => "condition", "value" => '[{"field":"status","operator":"=","value":"active"}]'],
            ["key" => "page", "value" => "1"],
            ["key" => "limit", "value" => "5"]
        ]
    ],
    'Update Rows' => [
        'method' => 'PUT',
        'url'    => $baseUrl . '/index.php',
        'body'   => [
            "action" => "update",
            "table"  => "users",
            "where"  => ["field" => "id", "operator" => "=", "value" => 1],
            "data"   => ["status" => "inactive"]
        ]
    ],
    'Delete Rows' => [
        'method' => 'DELETE',
        'url'    => $baseUrl . '/index.php',
        'body'   => [
            "action" => "delete",
            "table"  => "users",
            "column" => "id",
            "values" => [2],
            "limit"  => 1
        ]
    ]
];

// 🔹 Try DB connection for custom methods
try {
    $db = new MySQLAdapter($config['db']);
} catch (\Throwable $e) {
    $db = null; // still allow generating base CRUD collection
}

// === Build CRUD Requests ===
foreach ($examples as $name => $ex) {
    $item = [
        'name' => $name,
        'request' => [
            'method' => $ex['method'],
            'header' => $headers,
            'url'    => [
                'raw'  => $ex['url'],
                'host' => [$baseUrl],
                'path' => ['index.php']
            ]
        ]
    ];

    if (isset($ex['query'])) {
        $item['request']['url']['query'] = $ex['query'];
    }

    if (isset($ex['body'])) {
        $item['request']['body'] = [
            'mode' => 'raw',
            'raw'  => json_encode($ex['body'], JSON_PRETTY_PRINT)
        ];
        $item['request']['header'][] = [
            'key'   => 'Content-Type',
            'value' => 'application/json'
        ];
    }

    $collection['item'][] = $item;
}

// === Add Custom Methods if available ===
if ($db) {
    $customController = new CustomController($db, $config);
    $customMethods    = $customController->listMethods();

    foreach ($customMethods as $method) {
        $collection['item'][] = [
            'name' => "Custom: {$method}",
            'request' => [
                'method' => 'POST',
                'header' => array_merge($headers, [
                    ['key' => 'Content-Type', 'value' => 'application/json']
                ]),
                'url' => [
                    'raw'  => $baseUrl . '/index.php',
                    'host' => [$baseUrl],
                    'path' => ['index.php']
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw'  => json_encode([
                        "action" => "custom",
                        "method" => $method,
                        "params" => new \stdClass()
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
}

// === Output ===
echo json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
