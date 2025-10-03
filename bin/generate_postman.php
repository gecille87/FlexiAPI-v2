  <?php
    /**
     * FlexiAPI Postman Collection Generator
     *
     * Usage:
     *   php bin/generate_postman.php > postman_collection.json
     */

    $collection = [
        'info' => [
            'name' => 'FlexiAPI Collection',
            'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
        ],
        'item' => []
    ];

    // Base URL (adjust if deployed)
    $baseUrl = '{{baseUrl}}'; // Postman variable

    // API key variable
    $headers = [
        [
            'key' => 'X-FlexiAPI-Key',
            'value' => '{{apiKey}}'
        ]
    ];

    // Example requests for TableController
    $examples = [
        'Create (Insert Rows)' => [
            'method' => 'POST',
            'url' => $baseUrl . '/index.php',
            'body' => [
                "table" => "users",
                "data" => [
                    ["name" => "Jane", "email" => "jane@example.com", "age" => 22],
                    ["name" => "Paul", "email" => "paul@example.com", "age" => 29]
                ]
            ]
        ],
        'Get (Select Rows)' => [
            'method' => 'GET',
            'url' => $baseUrl . '/index.php',
            'query' => [
                ["key" => "table", "value" => "users"],
                ["key" => "columns", "value" => "id,name,email"],
                ["key" => "condition", "value" => '[{"field":"status","operator":"=","value":"active"}]'],
                ["key" => "page", "value" => "1"],
                ["key" => "limit", "value" => "5"]
            ]
        ],
        'Update Rows' => [
            'method' => 'PUT',
            'url' => $baseUrl . '/index.php',
            'body' => [
                "table" => "users",
                "where" => ["field" => "id", "operator" => "=", "value" => 1],
                "data" => ["status" => "inactive"]
            ]
        ],
        'Delete Rows' => [
            'method' => 'DELETE',
            'url' => $baseUrl . '/index.php',
            'body' => [
                "table" => "users",
                "column" => "id",
                "values" => [2],
                "limit" => 1
            ]
        ]
    ];

    // Build items
    foreach ($examples as $name => $ex) {
        $item = [
            'name' => $name,
            'request' => [
                'method' => $ex['method'],
                'header' => $headers,
                'url' => [
                    'raw' => $ex['url'],
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
                'raw' => json_encode($ex['body'], JSON_PRETTY_PRINT)
            ];
            $item['request']['header'][] = [
                'key' => 'Content-Type',
                'value' => 'application/json'
            ];
        }

        $collection['item'][] = $item;
    }

    echo json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
