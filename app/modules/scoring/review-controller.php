<?php
declare(strict_types=1);
$reviewError=null;$reviewOffer=null;$reviewAnalysis=null;$reviewItems=[];$reviewPreview=null;
$reviewOfferId=filter_var($_GET['offer_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
try {
 $reviewUid=local_user_id();$reviewOffer=review_offer($reviewUid,$reviewOfferId);
 if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!hash_equals((string)$_SESSION['csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Session expirée, rechargez la page.');
  $action=(string)($_POST['action']??'');
  if($action==='review_create')review_create($reviewUid,$reviewOfferId,(int)($_POST['weight']??30));
  elseif($action==='review_accept'||$action==='review_reject')review_decide($reviewUid,$reviewOfferId,(int)($_POST['review_id']??0),$action==='review_accept'?'accepted':'rejected');
  else throw new InvalidArgumentException('Action inconnue.');
  header('Location: '.url('score-review').'&offer_id='.$reviewOfferId.'&saved=1',true,303);exit;
 }
 $reviewAnalysis=review_latest_analysis($reviewUid,$reviewOfferId);
 if($reviewAnalysis){try{$reviewPreview=review_calculate($reviewAnalysis['report']);}catch(Throwable $ignored){$reviewPreview=null;}}
 $reviewItems=review_history($reviewUid,$reviewOfferId);
}catch(Throwable $ex){$reviewError=$ex->getMessage();}
