<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/config/config.php';require APP_ROOT.'/app/core/database.php';
// Accès uniquement en environnement local ; pas d'exposition réseau sans authentification.
try{$s=db()->prepare('SELECT photo_path FROM profiles p JOIN users u ON u.id=p.user_id WHERE u.email=?');$s->execute(['local@os-job.invalid']);$name=$s->fetchColumn();if(!$name)throw new RuntimeException();$path=APP_ROOT.'/storage/profile-photos/'.basename((string)$name);if(!is_file($path))throw new RuntimeException();$mime=(new finfo(FILEINFO_MIME_TYPE))->file($path);if(!in_array($mime,['image/jpeg','image/png','image/webp'],true))throw new RuntimeException();header('Content-Type: '.$mime);header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');readfile($path);}catch(Throwable $e){http_response_code(404);}
