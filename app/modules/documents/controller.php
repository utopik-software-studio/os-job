<?php
$docsAiReview=null;$docsBackground=null;$docsAnalysis=null;$docsError=null;$docsRows=[];$docsOffers=[];$docsCurrent=null;$docsProfile=[];$docsVariants=[];$docsStyle=[];
try {
 $uid=docs_owner();$docsProfile=docs_profile($uid);
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)($_SESSION['csrf']??''),(string)($_POST['csrf']??'')))throw new InvalidArgumentException('Session expirée. Actualisez la page.');
  $action=(string)($_POST['action']??'');
  if($action==='upload_background'){docs_background_upload($uid,(int)($_POST['id']??0),$_FILES['background_image']??[]);$id=(int)$_POST['id'];}
  elseif($action==='remove_background'){docs_background_remove($uid,(int)($_POST['id']??0));$id=(int)$_POST['id'];}
  elseif($action==='ai_review'){$id=(int)($_POST['id']??0);ai_review_save($uid,$id,ai_language((string)($_POST['language']??'fr')),(string)($_POST['engine']??'local'));}
  elseif($action==='analyse_offer'){$id=(int)($_POST['id']??0);docs_analyse_offer($uid,$id);}
  else {$post=$_POST;if(in_array($action,['create','save','save_variant'],true)){$post['style']=docs_style_v8((array)($_POST['style']??[]),(string)($_POST['template_key']??'classic'));}$id=docs_action_v7($uid,$post);}header('Location: '.url('documents').($id?'&id='.$id:'').'&saved=1',true,303);exit;
 }
 $docsRows=docs_list($uid);$docsOffers=docs_offers($uid);$docsVariants=docs_variants($uid);
 if((int)($_GET['id']??0)>0){$docsCurrent=docs_owned($uid,(int)$_GET['id']);$docsStyle=docs_presentation((int)$docsCurrent['id']);$docsBackground=docs_background((int)$docsCurrent['id']);$docsAnalysis=docs_saved_analysis($uid,(int)$docsCurrent['id']);$docsAiReview=ai_review_latest($uid,(int)$docsCurrent['id']);}
}catch(Throwable $ex){$docsError=$ex instanceof PDOException?'Erreur MySQL : vérifiez que les migrations 007 à 010 sont importées.':$ex->getMessage();}
