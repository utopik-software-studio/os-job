<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/config/config.php';require APP_ROOT.'/app/core/database.php';require APP_ROOT.'/app/modules/searches/searches.php';
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);if(!$id){http_response_code(400);exit('Identifiant invalide.');}
try{
 $uid=local_user_id();$s=db()->prepare('SELECT d.* FROM application_documents d JOIN applications a ON a.id=d.application_id WHERE d.id=? AND a.user_id=?');$s->execute([$id,$uid]);$d=$s->fetch();if(!$d){http_response_code(404);exit('Document introuvable.');}
 if($d['mime_type']!=='application/pdf'){http_response_code(415);exit('Aperçu intégré disponible uniquement pour les PDF. Téléchargez le fichier Word.');}
 $path=APP_ROOT.'/storage/application-documents/'.$d['stored_name'];if(!is_file($path)){http_response_code(404);exit('Fichier absent.');}
 header('Content-Type: application/pdf');header('Content-Disposition: inline; filename="apercu.pdf"');header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');header('Content-Length: '.filesize($path));readfile($path);
}catch(Throwable $e){http_response_code(500);exit('Impossible de prévisualiser le document.');}
