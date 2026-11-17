<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/config/config.php';require APP_ROOT.'/app/core/database.php';require APP_ROOT.'/app/modules/searches/searches.php';require APP_ROOT.'/app/modules/applications/applications.php';
if(session_status()!==PHP_SESSION_ACTIVE)session_start();header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try{
 if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);throw new InvalidArgumentException('Méthode invalide.');}
 if(empty($_SESSION['csrf'])||!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??''))){http_response_code(403);throw new InvalidArgumentException('Session expirée.');}
 $uid=local_user_id();$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);$status=(string)($_POST['status']??'');if(!$id||!isset(app_statuses()[$status])){http_response_code(400);throw new InvalidArgumentException('Paramètres invalides.');}
 app_action($uid,['action'=>'status','id'=>$id,'status'=>$status],[]);echo json_encode(['ok'=>true,'status'=>$status]);
}catch(Throwable $e){if(http_response_code()===200)http_response_code(400);echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);}
