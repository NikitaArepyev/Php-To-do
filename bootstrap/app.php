<?php

declare(strict_types=1);

use TodoApi\App;
use TodoApi\Controller\TaskController;
use TodoApi\Infrastructure\Database;
use TodoApi\Repository\PdoTaskRepository;
use TodoApi\Service\TaskService;
use TodoApi\Support\FileLogger;

$pdo = Database::createPdoFromEnv();
Database::ensureSchema($pdo);

$repository = new PdoTaskRepository($pdo);
$service = new TaskService($repository);
$controller = new TaskController($service);
$logger = new FileLogger(__DIR__ . '/../storage/logs/app.log');

return new App($controller, $logger);
