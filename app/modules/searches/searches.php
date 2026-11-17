<?php
declare(strict_types=1);
function local_user_id(): int { $s=db()->prepare('SELECT id FROM users WHERE email=?');$s->execute(['local@os-job.invalid']);return (int)$s->fetchColumn(); }
function search_spaces_all(int $uid):array {$s=db()->prepare('SELECT * FROM search_spaces WHERE user_id=? ORDER BY id DESC');$s->execute([$uid]);return $s->fetchAll();}
function offers_all(int $uid,?int $space=null):array {
 $sql='SELECT o.*,sc.score AS match_score,a0.id AS active_application_id FROM job_offers o LEFT JOIN offer_scores sc ON sc.job_offer_id=o.id AND sc.user_id=o.user_id LEFT JOIN applications a0 ON a0.job_offer_id=o.id AND a0.user_id=o.user_id';$params=[];
 if($space!==null){$sql.=' JOIN search_offer_links l ON l.job_offer_id=o.id AND l.search_space_id=?';$params[]=$space;}
 $params[]=$uid;
 $sql.=' WHERE o.user_id=? AND (o.status IN (\'to_review\',\'archived\') OR EXISTS (SELECT 1 FROM applications a WHERE a.job_offer_id=o.id AND a.user_id=o.user_id AND a.status=\'to_apply\')) AND NOT EXISTS (SELECT 1 FROM applications a WHERE a.job_offer_id=o.id AND a.user_id=o.user_id AND a.status NOT IN (\'to_review\',\'to_apply\')) ORDER BY o.updated_at DESC,o.id DESC';$s=db()->prepare($sql);$s->execute($params);return $s->fetchAll();
}
function search_action(int $uid,array $post):void {
 $act=(string)($post['action']??'');$id=filter_var($post['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
 if($act==='space_save'){
  $name=trim((string)($post['name']??''));if($name===''||mb_strlen($name)>160)throw new InvalidArgumentException('Nom de recherche invalide.');
  $loc=mb_substr(trim((string)($post['location_filter']??'')),0,255);$salary=nullable_salary($post['salary_min']??'');
  if($id){$s=db()->prepare('UPDATE search_spaces SET name=?,location_filter=?,salary_min=? WHERE id=? AND user_id=?');$s->execute([$name,$loc,$salary,$id,$uid]);}
  else{$s=db()->prepare('INSERT INTO search_spaces (user_id,name,location_filter,salary_min) VALUES (?,?,?,?)');$s->execute([$uid,$name,$loc,$salary]);}
 }elseif($act==='space_delete'){
  $s=db()->prepare('DELETE FROM search_spaces WHERE id=? AND user_id=?');$s->execute([$id,$uid]);
 }elseif($act==='offer_save'){
  $title=trim((string)($post['title']??''));if($title===''||mb_strlen($title)>200)throw new InvalidArgumentException('Intitulé de poste obligatoire (200 caractères max).');
  $company=mb_substr(trim((string)($post['company']??'')),0,190);$city=mb_substr(trim((string)($post['city']??'')),0,160);
  $low=nullable_salary($post['salary_min']??'');$high=nullable_salary($post['salary_max']??'');if($low!==null&&$high!==null&&$high<$low)throw new InvalidArgumentException('Fourchette salariale incohérente.');
  $link=trim((string)($post['source_url']??''));if($link!==''&&(!filter_var($link,FILTER_VALIDATE_URL)||!in_array(strtolower((string)parse_url($link,PHP_URL_SCHEME)),['http','https'],true)))throw new InvalidArgumentException('Lien de l’offre invalide.');
  $desc=trim((string)($post['description']??''));if(mb_strlen($desc)>50000)throw new InvalidArgumentException('Description trop longue.');
  $status=(string)($post['status']??'to_review');if(!array_key_exists($status,offer_statuses()))throw new InvalidArgumentException('Statut inconnu.');
  $vals=[$title,$company,$city,$low,$high,$link,$desc,$status];
  if($id){$s=db()->prepare('UPDATE job_offers SET title=?,company=?,city=?,salary_min=?,salary_max=?,source_url=?,description=?,status=? WHERE id=? AND user_id=?');$s->execute([...$vals,$id,$uid]);$offerId=$id;}
  else{$s=db()->prepare('INSERT INTO job_offers (title,company,city,salary_min,salary_max,source_url,description,status,user_id) VALUES (?,?,?,?,?,?,?,?,?)');$s->execute([...$vals,$uid]);$offerId=(int)db()->lastInsertId();}
  $space=filter_var($post['space_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
  if($space){$s=db()->prepare('SELECT id FROM search_spaces WHERE id=? AND user_id=?');$s->execute([$space,$uid]);if(!$s->fetchColumn())throw new InvalidArgumentException('Espace de recherche introuvable.');$s=db()->prepare('INSERT IGNORE INTO search_offer_links(search_space_id,job_offer_id) VALUES (?,?)');$s->execute([$space,$offerId]);}
  // Une offre « à étudier » n'est pas une candidature. Le statut « à postuler »
  // ouvre la préparation ; « envoyée » et les étapes suivantes ouvrent le suivi.
  $tracked=in_array($status,['to_apply','applied','interview','rejected','accepted'],true);
  $existing=db()->prepare('SELECT id,status,applied_at FROM applications WHERE user_id=? AND job_offer_id=?');
  $existing->execute([$uid,$offerId]);$existingApp=$existing->fetch(PDO::FETCH_ASSOC);
  if($existingApp && !in_array((string)$existingApp['status'],['to_review'],true)){
   $status=(string)$existingApp['status'];
   $sync=db()->prepare('UPDATE job_offers SET status=? WHERE id=? AND user_id=?');$sync->execute([in_array($status,['waiting','proposal'],true)?'applied':$status,$offerId,$uid]);
  }
  if($tracked){
   $appId=$existingApp?(int)$existingApp['id']:app_ensure($uid,$offerId);
   $previous=$existingApp?(string)$existingApp['status']:'to_review';
   // Une simple édition d'offre ne rétrograde jamais une candidature déjà envoyée.
   $advanced=in_array($previous,['applied','waiting','interview','proposal','rejected','accepted'],true);
   $next=($advanced && in_array($status,['to_apply','applied'],true))?$previous:$status;
   if($existingApp && $next!==$previous){
    $up=db()->prepare('UPDATE applications SET status=?,applied_at=CASE WHEN ? IN ("applied","interview","rejected","accepted") AND applied_at IS NULL THEN NOW() ELSE applied_at END WHERE id=? AND user_id=?');
    $up->execute([$next,$next,$appId,$uid]);
    $ev=db()->prepare('INSERT INTO application_events(application_id,event_type,details) VALUES (?,"status",?)');
    $ev->execute([$appId,(app_statuses()[$previous]??$previous).' → '.(app_statuses()[$next]??$next)]);
   }
   if($next!==$status){$fix=db()->prepare('UPDATE job_offers SET status=? WHERE id=? AND user_id=?');$fix->execute([$next,$offerId,$uid]);}
  }elseif($existingApp){
   // Les dossiers historiques sont conservés. Un retour à « à étudier »
   // masque la candidature du pipeline sans supprimer ses documents ni échanges.
   if(in_array((string)$existingApp['status'],['to_review','to_apply'],true)){
    $up=db()->prepare('UPDATE applications SET status=? WHERE id=? AND user_id=?');$up->execute([$status,(int)$existingApp['id'],$uid]);
   }else{
    // Ne pas effacer une candidature envoyée par une édition d'offre.
    $fix=db()->prepare('UPDATE job_offers SET status=? WHERE id=? AND user_id=?');$fix->execute([$existingApp['status'],$offerId,$uid]);
   }
  }
  $platform=trim((string)($post['discovery_platform']??''));if(mb_strlen($platform)>80)throw new InvalidArgumentException('Plateforme trop longue.');
  $s=db()->prepare('UPDATE job_offers SET discovery_platform=? WHERE id=? AND user_id=?');$s->execute([$platform,$offerId,$uid]);
  score_save($uid,$offerId);
 }elseif($act==='offer_stage'){
  $stage=(string)($post['stage']??'');
  if(!in_array($stage,['to_apply','applied'],true))throw new InvalidArgumentException('Étape de candidature invalide.');
  $own=db()->prepare('SELECT id,status FROM job_offers WHERE id=? AND user_id=?');$own->execute([$id,$uid]);$offer=$own->fetch(PDO::FETCH_ASSOC);
  if(!$offer)throw new InvalidArgumentException('Offre introuvable.');
  $existing=db()->prepare('SELECT id,status FROM applications WHERE user_id=? AND job_offer_id=?');$existing->execute([$uid,$id]);$app=$existing->fetch(PDO::FETCH_ASSOC);
  if($app){
   if(in_array((string)$app['status'],['applied','waiting','interview','proposal','rejected','accepted'],true)&&$stage==='to_apply')throw new InvalidArgumentException('Cette candidature est déjà envoyée ou avancée.');
   app_action($uid,['action'=>'status','id'=>(int)$app['id'],'status'=>$stage],[]);
  }else{
   $u=db()->prepare('UPDATE job_offers SET status=? WHERE id=? AND user_id=?');$u->execute([$stage,$id,$uid]);
   $appId=app_ensure($uid,$id);
   if($stage==='applied'){
    $u=db()->prepare('UPDATE applications SET applied_at=COALESCE(applied_at,NOW()) WHERE id=? AND user_id=?');$u->execute([$appId,$uid]);
   }
   $ev=db()->prepare('INSERT INTO application_events(application_id,event_type,details) VALUES (?,"status",?)');$ev->execute([$appId,'À étudier → '.(app_statuses()[$stage]??$stage)]);
  }
 }elseif($act==='offer_delete'){
  $s=db()->prepare('DELETE FROM job_offers WHERE id=? AND user_id=?');$s->execute([$id,$uid]);
 }elseif($act==='link_add'){
  $space=(int)($post['space_id']??0);$s=db()->prepare('INSERT IGNORE INTO search_offer_links (search_space_id,job_offer_id) SELECT s.id,o.id FROM search_spaces s JOIN job_offers o ON o.id=? AND o.user_id=s.user_id WHERE s.id=? AND s.user_id=?');$s->execute([$id,$space,$uid]);
 }else throw new InvalidArgumentException('Action inconnue.');
}
function nullable_salary($v):?int {$v=trim((string)$v);if($v==='')return null;if(!ctype_digit($v)||(int)$v>10000000)throw new InvalidArgumentException('Salaire invalide.');return (int)$v;}
function offer_statuses():array{return ['to_review'=>'À étudier','to_apply'=>'À postuler','applied'=>'Candidature envoyée','interview'=>'Entretien','rejected'=>'Refus','accepted'=>'Offre obtenue','archived'=>'Archivée'];}
