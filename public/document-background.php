<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/config/config.php';require APP_ROOT.'/app/core/database.php';require APP_ROOT.'/app/modules/documents/generator.php';require APP_ROOT.'/app/modules/documents/enhancements_v8.php';
try{$uid=docs_owner();$id=(int)($_GET['id']??0);docs_owned($uid,$id);$img=docs_background($id);if(!$img)throw new RuntimeException();
 $file=APP_ROOT.'/storage/document-backgrounds/'.basename($img['stored_name']);if(!is_file($file))throw new RuntimeException();
 header('Content-Type: '.$img['mime_type']);header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');header('Content-Length: '.filesize($file));readfile($file);
}catch(Throwable $e){http_response_code(404);header('Content-Type: text/plain; charset=utf-8');echo 'Image indisponible.';}
