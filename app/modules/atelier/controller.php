<?php
$atelierTab=(string)($_GET['tab']??'cv');
if(!in_array($atelierTab,['cv','lettres','candidatures','bibliotheque'],true))$atelierTab='cv';
$atelierCvRows=[];$atelierDocs=[];$atelierApplications=[];
try{
 $uid=docs_owner();
 $atelierCvRows=cv_list($uid);
 $q=db()->prepare('SELECT id,title,document_type,updated_at FROM generated_documents WHERE user_id=? ORDER BY updated_at DESC');$q->execute([$uid]);$atelierDocs=$q->fetchAll();
 $q=db()->prepare('SELECT a.id,a.status,j.title AS offer_title,j.company FROM applications a JOIN job_offers j ON j.id=a.job_offer_id WHERE a.user_id=? ORDER BY a.id DESC');$q->execute([$uid]);$atelierApplications=$q->fetchAll();
}catch(Throwable $e){$atelierError='Impossible de charger la bibliothèque. Vérifiez la base de données.';}
