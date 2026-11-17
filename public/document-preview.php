<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/config/config.php';require APP_ROOT.'/app/core/database.php';require APP_ROOT.'/app/modules/documents/generator.php';require APP_ROOT.'/app/modules/documents/enhancements_v8.php';require APP_ROOT.'/app/modules/documents/render.php';
try{$uid=docs_owner();$d=docs_owned($uid,(int)($_GET['id']??0));$p=docs_profile($uid);header('Content-Type: text/html; charset=utf-8');header('Cache-Control: private, no-store');header('X-Content-Type-Options: nosniff');echo doc_render_html($d,$p);}catch(Throwable $e){http_response_code(404);echo 'Document introuvable.';}
