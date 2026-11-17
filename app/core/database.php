<?php
declare(strict_types=1);
function db(): PDO {
 static $pdo = null;
 if ($pdo instanceof PDO) return $pdo;
 $path = APP_ROOT . '/app/config/database.php';
 if (!is_file($path)) throw new RuntimeException('Configuration MySQL absente. Copiez database.example.php vers database.php.');
 $c = require $path;
 $dsn = 'mysql:host='.$c['host'].';port='.(int)$c['port'].';dbname='.$c['database'].';charset=utf8mb4';
 return $pdo = new PDO($dsn, $c['username'], $c['password'], [
   PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
   PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
   PDO::ATTR_EMULATE_PREPARES=>false,
 ]);
}
