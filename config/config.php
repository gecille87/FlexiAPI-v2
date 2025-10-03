 <?php
    return [
        'db' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'test',
            'username' => 'marieDB',
            'password' => 'Qwerty@0987654321',
            'charset' => 'utf8mb4',
            'whitelist_tables' => ['users', 'products', 'deleted_users'], // required
        ],
        'api' => [
            'key' => 'your-secret-token', // X-FlexiAPI-Key header check
            'max_limit' => 100,
            'default_limit' => 20,
        ]
    ];
