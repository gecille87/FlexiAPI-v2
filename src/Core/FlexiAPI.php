<?php

namespace FlexiAPI\Core;

use FlexiAPI\DB\MySQLAdapter;
use FlexiAPI\Controllers\TableController;
use FlexiAPI\Controllers\CustomController;
use FlexiAPI\Utils\Response;

class FlexiAPI
{
    private array $config;
    private MySQLAdapter $db;

    public function __construct(array $config)
    {
        $this->config = $config;

        try {
            $this->db = new MySQLAdapter($config['db']);
        } catch (\Throwable $e) {
            Response::json(false, 'Database connection failed', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function handle(): void
    {
        // 🔑 API key check
        $apiKey = $this->config['api']['key'] ?? null;
        $clientKey = $_SERVER['HTTP_X_FLEXIAPI_KEY'] ?? null;
        if ($apiKey && $clientKey !== $apiKey) {
            Response::json(false, 'Unauthorized: invalid API key', null, 401);
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $input  = $this->getInput();

        // Default controller
        $controller = new TableController($this->db, $this->config);

        try {
            switch ($method) {
                case 'GET':
                    $controller->get($input);
                    break;

                case 'POST':
                    // 🔹 Support for custom method calls
                    $action = strtolower($input['action'] ?? '');
                    if ($action === 'custom') {
                        $custom = new CustomController($this->db, $this->config);
                        $customMethod = $input['method'] ?? null;
                        if (!$customMethod) {
                            Response::json(false, "Custom method not specified", null, 400);
                        }
                        $custom->run($customMethod, $input['params'] ?? []);
                    } else {
                        $controller->create($input);
                    }
                    break;

                case 'PUT':
                    $controller->update($input);
                    break;

                case 'DELETE':
                    $controller->delete($input);
                    break;

                default:
                    Response::json(false, 'Unsupported HTTP method', null, 405);
            }
        } catch (\Throwable $e) {
            Response::json(false, 'Unhandled server error', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function getInput(): array
    {
        $data = [];
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $_GET;
        } else {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $data = $json;
            }
        }
        return $data;
    }
}
