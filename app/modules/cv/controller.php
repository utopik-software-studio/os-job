<?php
$cvError=null;$cvRows=[];$cvCurrent=null;$cvOffers=[];$cvData=cv_default();
try {
 $uid=cv_owner();
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)($_SESSION['csrf']??''),(string)($_POST['csrf']??'')))throw new InvalidArgumentException('Session expirée.');
  $action=(string)($_POST['action']??'save');
  if($action==='delete'){$id=(int)($_POST['id']??0);cv_owned($uid,$id);$s=db()->prepare('DELETE FROM cv_studio_documents WHERE id=? AND user_id=?');$s->execute([$id,$uid]);header('Location: '.url('cv-studio'),true,303);exit;}
  if($action==='save'){$id=cv_save($uid,$_POST);header('Location: '.url('cv-studio').'&id='.$id.'&saved=1',true,303);exit;}
  throw new InvalidArgumentException('Action inconnue.');
 }
 $cvRows=cv_list($uid);$cvOffers=docs_offers($uid);
 $id=(int)($_GET['id']??0);if($id){$cvCurrent=cv_owned($uid,$id);$cvData=$cvCurrent['content'];}
 elseif(($_GET['source']??'profile')!=='blank')$cvData=cv_profile_seed($uid);
 if(isset($_GET['duplicate'])&&(int)$_GET['duplicate']>0){$cvCurrent=cv_owned($uid,(int)$_GET['duplicate']);$cvData=$cvCurrent['content'];$cvCurrent['id']=0;$cvCurrent['title'].=' — Copie';}
}catch(Throwable $e){$cvError=$e instanceof PDOException?'Erreur de base : vérifiez la migration 015_cv_studio.sql.':$e->getMessage();}
