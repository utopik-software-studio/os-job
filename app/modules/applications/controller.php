<?php
$appError=null;$appRows=[];$appSelected=null;$appData=[];$appAvailable=[];
try{$uid=local_user_id();if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??'')))throw new InvalidArgumentException('Session expirée, rechargez la page.');
 if(($_POST['action']??'')==='decision_save'){
  $applicationId=(int)($_POST['id']??0);app_owned($uid,$applicationId);
  $decision=(string)($_POST['decision']??'');if(!in_array($decision,['apply','investigate','discard'],true))throw new InvalidArgumentException('Décision invalide.');
  $reason=trim((string)($_POST['reason']??''));if($reason===''||mb_strlen($reason)>2000)throw new InvalidArgumentException('Indiquez un motif (2000 caractères maximum).');
  $q=db()->prepare('INSERT INTO application_decisions(user_id,application_id,decision,reason) VALUES(?,?,?,?)');$q->execute([$uid,$applicationId,$decision,$reason]);
 }elseif(($_POST['action']??'')==='platform_save'){
  $applicationId=(int)($_POST['id']??0);app_owned($uid,$applicationId);
  $platform=trim((string)($_POST['application_platform']??''));if(mb_strlen($platform)>80)throw new InvalidArgumentException('Plateforme trop longue.');
  $q=db()->prepare('UPDATE applications SET application_platform=? WHERE id=? AND user_id=?');$q->execute([$platform,$applicationId,$uid]);
 }elseif(($_POST['action']??'')==='achievement_add'){
  $applicationId=(int)($_POST['id']??0);app_owned($uid,$applicationId);
  $title=trim((string)($_POST['achievement_title']??''));$actions=trim((string)($_POST['achievement_actions']??''));
  if($title===''||$actions===''||mb_strlen($title)>190||mb_strlen($actions)>5000)throw new InvalidArgumentException('Titre et actions obligatoires.');
  $q=db()->prepare('INSERT INTO career_achievements(user_id,title,organization,context_text,actions_text,results_text,evidence_text) VALUES(?,?,?,?,?,?,?)');
  $q->execute([$uid,$title,mb_substr(trim((string)($_POST['achievement_org']??'')),0,190),mb_substr(trim((string)($_POST['achievement_context']??'')),0,5000),$actions,mb_substr(trim((string)($_POST['achievement_results']??'')),0,5000),mb_substr(trim((string)($_POST['achievement_evidence']??'')),0,5000)]);
 }elseif(($_POST['action']??'')==='offer_ai_analyze'){
  $applicationId=(int)($_POST['id']??0);app_owned($uid,$applicationId);
  $q=db()->prepare('SELECT job_offer_id FROM applications WHERE id=? AND user_id=?');$q->execute([$applicationId,$uid]);
  $jobId=ai_job_enqueue($uid,(int)$q->fetchColumn());
  if(!ai_job_launch($jobId))$_SESSION['ai_worker_notice']='Tâche en attente : lancez le worker PHP CLI (voir README v0.9.6).';
 }elseif(($_POST['action']??'')==='score_recalculate'){app_owned($uid,(int)($_POST['id']??0));$s=db()->prepare('SELECT job_offer_id FROM applications WHERE id=? AND user_id=?');$s->execute([(int)$_POST['id'],$uid]);score_save($uid,(int)$s->fetchColumn());}elseif(in_array((string)($_POST['action']??''),['doc_link','doc_unlink','doc_send'],true)){adoc_action($uid,(int)($_POST['id']??0),$_POST);}else {if(($_POST['action']??'')==='create'){$createdId=app_ensure($uid,(int)($_POST['offer_id']??0));}else app_action($uid,$_POST,$_FILES);} $to=$createdId??(int)($_POST['id']??0);header('Location: '.url('applications').($to?'&id='.$to:'').'&saved=1',true,303);exit;
 }
 $appRows=app_fetch($uid);$s=db()->prepare('SELECT o.id,o.title,o.company FROM job_offers o LEFT JOIN applications a ON a.job_offer_id=o.id AND a.user_id=o.user_id WHERE o.user_id=? AND a.id IS NULL AND o.status IN (\'to_apply\',\'applied\',\'interview\',\'rejected\',\'accepted\') ORDER BY o.id DESC');$s->execute([$uid]);$appAvailable=$s->fetchAll();$selected=(int)($_GET['id']??0);foreach($appRows as $row)if((int)$row['id']===$selected)$appSelected=$row;
 if($appSelected){$aiReports=offer_ai_reports($uid);$appData['offer_ai_report']=$aiReports[(int)$appSelected['job_offer_id']]??null;$appData['offer_ai_job']=ai_job_latest($uid,(int)$appSelected['job_offer_id']);}
 if($appSelected){
  $q=db()->prepare('SELECT decision,reason,decided_at FROM application_decisions WHERE user_id=? AND application_id=? ORDER BY id DESC LIMIT 10');$q->execute([$uid,$selected]);$appData['decisions']=$q->fetchAll(PDO::FETCH_ASSOC);
  $q=db()->prepare('SELECT * FROM career_achievements WHERE user_id=? ORDER BY id DESC LIMIT 30');$q->execute([$uid]);$appData['achievements']=$q->fetchAll(PDO::FETCH_ASSOC);
 }
 if($appSelected){$appData['generated_links']=adoc_list($uid,$selected);$appData['generated_candidates']=adoc_candidates($uid,$selected);$appData['dispatches']=adoc_history($uid,$selected);}
 if($appSelected){$q=db()->prepare('SELECT kind,title,content,created_at FROM application_ai_drafts WHERE user_id=? AND application_id=? ORDER BY id DESC LIMIT 30');$q->execute([$uid,$selected]);$appData['ai_drafts']=$q->fetchAll(PDO::FETCH_ASSOC);}
 if($appSelected)foreach(['application_events','application_contacts','application_interviews','application_reminders','application_documents'] as $table)$appData[$table]=app_children($table,$selected);
}catch(Throwable $e){if($_SERVER['REQUEST_METHOD']==='POST' && (int)($_POST['id']??0)>0 && !isset($_GET['id']))$_GET['id']=(int)$_POST['id'];try{$appRows=app_fetch($uid);$selected=(int)($_GET['id']??0);foreach($appRows as $row)if((int)$row['id']===$selected)$appSelected=$row;if($appSelected){$aiReports=offer_ai_reports($uid);$appData['offer_ai_report']=$aiReports[(int)$appSelected['job_offer_id']]??null;$appData['offer_ai_job']=ai_job_latest($uid,(int)$appSelected['job_offer_id']);}if($appSelected){
    $appData['generated_links']=adoc_list($uid,$selected);
    $appData['generated_candidates']=adoc_candidates($uid,$selected);
    $appData['dispatches']=adoc_history($uid,$selected);
    try{$q=db()->prepare('SELECT kind,title,content,created_at FROM application_ai_drafts WHERE user_id=? AND application_id=? ORDER BY id DESC LIMIT 30');$q->execute([$uid,$selected]);$appData['ai_drafts']=$q->fetchAll(PDO::FETCH_ASSOC);}catch(Throwable $ignored){}
    foreach(['application_events','application_contacts','application_interviews','application_reminders','application_documents'] as $table){$appData[$table]=app_children($table,$selected);}
   }}catch(Throwable $ignored){} $appError=$e instanceof PDOException?'Erreur MySQL : '.$e->getMessage():$e->getMessage(); error_log('[OS::JOB applications] '.$e);}

foreach(['decisions','achievements'] as $k)if(!isset($appData[$k]))$appData[$k]=[];

$appAcceptedScores=[];$appHistory=[];
try {$appAcceptedScores=review_accepted_by_offer(local_user_id());}catch(Throwable $ex){error_log('[OS::JOB score overview] '.$ex->getMessage());}
if($appSelected){try{$appHistory=app_history_all(local_user_id(),(int)$appSelected['id'],(int)$appSelected['job_offer_id']);}catch(Throwable $ex){error_log('[OS::JOB history] '.$ex->getMessage());}}
