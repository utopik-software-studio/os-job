<?php
declare(strict_types=1);
// Association distincte de l'envoi. Les preuves d'envoi sont append-only.
function adoc_list(int $uid,int $aid):array {
 app_owned($uid,$aid);
 $s=db()->prepare('SELECT l.id AS link_id,d.id,d.title,d.document_type,d.template_key,d.updated_at FROM application_document_links l JOIN generated_documents d ON d.id=l.generated_document_id JOIN applications a ON a.id=l.application_id WHERE l.application_id=? AND a.user_id=? AND d.user_id=? ORDER BY l.id DESC');$s->execute([$aid,$uid,$uid]);return $s->fetchAll();
}
function adoc_candidates(int $uid,int $aid):array {
 app_owned($uid,$aid);$s=db()->prepare('SELECT d.id,d.title,d.document_type,d.job_offer_id FROM generated_documents d JOIN applications a ON a.job_offer_id=d.job_offer_id AND a.user_id=d.user_id WHERE a.id=? AND a.user_id=? AND d.user_id=? AND NOT EXISTS(SELECT 1 FROM application_document_links l WHERE l.application_id=a.id AND l.generated_document_id=d.id) ORDER BY d.updated_at DESC');$s->execute([$aid,$uid,$uid]);return $s->fetchAll();
}
function adoc_history(int $uid,int $aid):array {
 app_owned($uid,$aid);$s=db()->prepare('SELECT x.*,g.title AS generated_title,u.original_name AS uploaded_title FROM application_document_dispatches x LEFT JOIN generated_documents g ON g.id=x.generated_document_id LEFT JOIN application_documents u ON u.id=x.application_document_id WHERE x.application_id=? ORDER BY x.sent_at DESC,x.id DESC');$s->execute([$aid]);return $s->fetchAll();
}
function adoc_uploaded(int $uid,int $aid):array {app_owned($uid,$aid);$s=db()->prepare('SELECT * FROM application_documents WHERE application_id=? ORDER BY id DESC');$s->execute([$aid]);return $s->fetchAll();}
function adoc_action(int $uid,int $aid,array $p):void {
 app_owned($uid,$aid);$action=(string)($p['action']??'');$docid=(int)($p['document_id']??0);
 if($action==='doc_link') {
  $d=docs_owned($uid,$docid);$s=db()->prepare('SELECT job_offer_id FROM applications WHERE id=?');$s->execute([$aid]);if((int)$d['job_offer_id']!==(int)$s->fetchColumn())throw new InvalidArgumentException('Le document doit être associé à cette offre dans CV & Lettres.');
  $s=db()->prepare('INSERT IGNORE INTO application_document_links(application_id,generated_document_id) VALUES (?,?)');$s->execute([$aid,$docid]);return;
 }
 if($action==='doc_unlink') {
  $s=db()->prepare('SELECT id FROM application_document_dispatches WHERE application_id=? AND generated_document_id=? LIMIT 1');$s->execute([$aid,$docid]);if($s->fetchColumn())throw new InvalidArgumentException('Ce document a déjà été envoyé : son association historique est protégée.');
  $s=db()->prepare('DELETE FROM application_document_links WHERE application_id=? AND generated_document_id=?');$s->execute([$aid,$docid]);return;
 }
 if($action!=='doc_send')throw new InvalidArgumentException('Action documentaire inconnue.');
 $source=(string)($p['source']??'generated');$sent=(string)($p['sent_at']??'');$date=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$sent);if(!$date||$date->format('Y-m-d\TH:i')!==$sent||$date>new DateTimeImmutable('+5 minutes'))throw new InvalidArgumentException('Date d’envoi invalide.');
 $channel=(string)($p['channel']??'email');if(!in_array($channel,['email','platform','post','hand','other'],true))throw new InvalidArgumentException('Canal invalide.');
 $recipient=mb_substr(trim((string)($p['recipient']??'')),0,190);$notes=mb_substr(trim((string)($p['notes']??'')),0,3000);
 $generated=null;$uploaded=null;$mime='text/html';$original='';$bytes='';
 if($source==='generated') {
  $s=db()->prepare('SELECT d.* FROM generated_documents d JOIN application_document_links l ON l.generated_document_id=d.id WHERE l.application_id=? AND d.id=? AND d.user_id=?');$s->execute([$aid,$docid,$uid]);$d=$s->fetch();if(!$d)throw new InvalidArgumentException('Associez d’abord ce document.');
  $generated=$docid;$original='OSJOB_document_'.$docid.'_version_envoyee.html';
  // Snapshot figé du rendu, et non du PDF transmis. Pour preuve exacte, joindre le fichier transmis.
  $profile=docs_profile($uid);$bytes=doc_render_html($d,$profile,true);
  $bytes=str_replace(['src="photo.php"','url("document-background.php?id='.$docid.'")'],['src=""','none'],$bytes);
 } elseif($source==='uploaded') {
  $s=db()->prepare('SELECT * FROM application_documents WHERE id=? AND application_id=?');$s->execute([$docid,$aid]);$d=$s->fetch();if(!$d)throw new InvalidArgumentException('Fichier joint introuvable.');
  $uploaded=$docid;$original=$d['original_name'];$mime=$d['mime_type'];$path=APP_ROOT.'/storage/application-documents/'.basename($d['stored_name']);if(!is_file($path))throw new RuntimeException('Fichier source manquant.');$bytes=file_get_contents($path);if($bytes===false)throw new RuntimeException('Lecture du fichier impossible.');
 } else throw new InvalidArgumentException('Source inconnue.');
 if(strlen($bytes)>10*1024*1024)throw new InvalidArgumentException('Fichier trop volumineux.');
 $dir=APP_ROOT.'/storage/document-dispatches';if(!is_dir($dir)&&!mkdir($dir,0700,true))throw new RuntimeException('Stockage indisponible.');
 $ext=['application/pdf'=>'pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx','application/msword'=>'doc','text/html'=>'html'][$mime]??'bin';$file=bin2hex(random_bytes(20)).'.'.$ext;$path=$dir.'/'.$file;
 if(file_put_contents($path,$bytes,LOCK_EX)===false)throw new RuntimeException('Impossible de conserver la copie.');
 try {
  $s=db()->prepare('INSERT INTO application_document_dispatches(application_id,generated_document_id,application_document_id,sent_at,channel,recipient,notes,snapshot_filename,snapshot_original_name,snapshot_mime,snapshot_sha256) VALUES (?,?,?,?,?,?,?,?,?,?,?)');$s->execute([$aid,$generated,$uploaded,$date->format('Y-m-d H:i:s'),$channel,$recipient,$notes,$file,$original,$mime,hash('sha256',$bytes)]);
  $s=db()->prepare('INSERT INTO application_events(application_id,event_type,details) VALUES (?,?,?)');$s->execute([$aid,'email','Envoi documentaire déclaré : '.$original.' ('.$channel.')']);
 }catch(Throwable $e){@unlink($path);throw $e;}
}
