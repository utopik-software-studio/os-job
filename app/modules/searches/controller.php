<?php
$searchError=null;$searchSpaces=[];$offers=[];$currentSpace=0;$editOffer=null;$editSpace=null;$offerAiReports=[];$aiNotice=null;$offerApplicationIds=[];
try{
 $uid=local_user_id();
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??'')))throw new InvalidArgumentException('Session expirée. Actualisez la page.');
  if(($_POST['action']??'')==='offer_ai_analyze'){
   $offerId=(int)($_POST['offer_id']??0);
   $jobId=ai_job_enqueue($uid,$offerId);
   if(!ai_job_launch($jobId))$_SESSION['ai_worker_notice']='Tâche en attente : worker PHP CLI non démarré.';
   header('Location: '.url('searches').'&space='.(int)($_POST['space_id']??0).'&ai_done='.$offerId,true,303);exit;
  }
  search_action($uid,$_POST);
  $redirect=(int)($_POST['space_id']??$_GET['space']??0);
  header('Location: '.url('searches').'&space='.$redirect.'&saved=1',true,303);exit;
 }
 $searchSpaces=search_spaces_all($uid);
 $currentSpace=filter_var($_GET['space']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
 if($currentSpace && !array_filter($searchSpaces,fn($s)=>(int)$s['id']===$currentSpace))$currentSpace=0;
 $offers=offers_all($uid,$currentSpace?:null);
 $offerAiReports=offer_ai_reports($uid);
 $q=db()->prepare('SELECT job_offer_id,id FROM applications WHERE user_id=?');$q->execute([$uid]);$offerApplicationIds=$q->fetchAll(PDO::FETCH_KEY_PAIR);
 $editId=filter_var($_GET['edit_offer']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
 if($editId){$s=db()->prepare('SELECT * FROM job_offers WHERE id=? AND user_id=?');$s->execute([$editId,$uid]);$editOffer=$s->fetch()?:null;}
 $editId=filter_var($_GET['edit_space']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
 if($editId)foreach($searchSpaces as $s)if((int)$s['id']===$editId)$editSpace=$s;
}catch(Throwable $ex){$searchError=$ex->getMessage();}
