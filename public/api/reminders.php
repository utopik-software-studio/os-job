<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/config/config.php';require APP_ROOT.'/app/core/database.php';require APP_ROOT.'/app/modules/searches/searches.php';
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try{
 $uid=local_user_id();
 if($_SERVER['REQUEST_METHOD']==='POST'){
  $token=(string)($_POST['csrf']??'');if(empty($_SESSION['csrf'])||!hash_equals($_SESSION['csrf'],$token)){http_response_code(403);throw new RuntimeException('Session expirée.');}
  $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);$action=(string)($_POST['action']??'');if(!$id||!in_array($action,['done','snooze'],true)){http_response_code(400);throw new RuntimeException('Requête invalide.');}
  if($action==='done'){$s=db()->prepare('UPDATE application_reminders r JOIN applications a ON a.id=r.application_id SET r.done=1 WHERE r.id=? AND a.user_id=?');$s->execute([$id,$uid]);}
  else {$s=db()->prepare('UPDATE application_reminders r JOIN applications a ON a.id=r.application_id SET r.due_at=DATE_ADD(NOW(),INTERVAL 1 DAY) WHERE r.id=? AND a.user_id=? AND r.done=0');$s->execute([$id,$uid]);}
  echo json_encode(['ok'=>true]);exit;
 }
 if($_SERVER['REQUEST_METHOD']!=='GET'){http_response_code(405);throw new RuntimeException('Méthode interdite.');}
 $s=db()->prepare('SELECT r.id,r.title,r.due_at,a.id AS application_id,o.title AS job_title,o.company FROM application_reminders r JOIN applications a ON a.id=r.application_id JOIN job_offers o ON o.id=a.job_offer_id WHERE a.user_id=? AND r.done=0 AND r.due_at<=NOW() ORDER BY r.due_at ASC LIMIT 50');$s->execute([$uid]);
 echo json_encode(['ok'=>true,'reminders'=>$s->fetchAll()],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
}catch(Throwable $e){if(http_response_code()===200)http_response_code(500);echo json_encode(['ok'=>false,'error'=>$e instanceof PDOException?'Erreur base de données.':$e->getMessage()]);}
