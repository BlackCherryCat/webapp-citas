<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Dotenv\Dotenv;

require __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$capsule = new Capsule();

// Leer configuración desde variables de entorno con valores por defecto.
$dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'mariadb');
$dbDatabase = getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? 'webapp_citas');
$dbUsername = getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'user');
$dbPassword = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? 'password');

$capsule->addConnection([
    "driver" => "mysql",
    "host" => $dbHost,
    "database" => $dbDatabase,
    "username" => $dbUsername,
    "password" => $dbPassword
]);

$capsule->setEventDispatcher(new Dispatcher(new Container()));
$capsule->setAsGlobal();
$capsule->bootEloquent();
