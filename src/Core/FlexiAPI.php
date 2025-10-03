<?php

namespace FlexiAPI\Core;

use FlexiAPI\DB\MySQLAdapter;
use FlexiAPI\Controllers\TableController;
use FlexiAPI\Utils\Response;

class FlexiAPI
{
    private array $config;
    private MySQLAdapter $db;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->db = new MySQLAdapter($config['db']);
    }

    public function handle(): void
    {
        // 🔑 API key check
        $apiKey = $this->config['api']['key'] ?? null;
        $clientKey = $_SERVER['HTTP_X_FLEXIAPI_KEY'] ?? null;
        if ($apiKey && $clientKey !== $apiKey) {
            Response::json(false, 'Unauthorized: invalid API key', null, 401);
        }

        // detect HTTP verb
        $method = $_SERVER['REQUEST_METHOD'];
        $input = $this->getInput();

        // default controller
        $controller = new TableController($this->db, $this->config);

        switch ($method) {
            case 'GET':
                $controller->get($input);
                break;
            case 'POST':
                $controller->create($input);
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
    }

    private function getInput(): array
    {
        $data = [];
        // GET → query params
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $_GET;
        } else {
            // POST, PUT, DELETE → JSON body
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $data = $json;
            }
        }
        return $data;
    }
}
