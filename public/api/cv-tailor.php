<?php
declare(strict_types=1);
// Always respond with JSON, even if bootstrap or PHP raises a warning.
// Buffer output so PHP warnings cannot corrupt the API response.
ob_start();
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
set_error_handler(static function (int $severity, string $message): bool {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity);
});
try {
 require dirname(__DIR__,2).'/app/config/config.php';
 require APP_ROOT.'/app/core/database.php';
 require APP_ROOT.'/app/modules/documents/generator.php';
 require APP_ROOT.'/app/modules/cv/studio.php';
 if(session_status()!==PHP_SESSION_ACTIVE)session_start();
 if(($_SERVER['REQUEST_METHOD']??'')!=='POST')throw new InvalidArgumentException('Méthode non autorisée.');
 if(empty($_SESSION['csrf'])||!hash_equals((string)$_SESSION['csrf'],(string)($_POST['csrf']??'')))throw new InvalidArgumentException('Session expirée.');
 $uid=cv_owner();$id=(int)($_POST['offer_id']??0);if($id<1)throw new InvalidArgumentException('Choisissez une offre.');
 $q=db()->prepare('SELECT title,company,description FROM job_offers WHERE id=? AND user_id=?');$q->execute([$id,$uid]);$offer=$q->fetch(PDO::FETCH_ASSOC);if(!$offer)throw new InvalidArgumentException('Offre introuvable.');
 $raw=json_decode((string)($_POST['content_json']??''),true);if(!is_array($raw))throw new InvalidArgumentException('CV invalide.');$cv=cv_clean($raw);
 if(!function_exists('curl_init'))throw new RuntimeException('Extension PHP cURL requise.');
 $profile=cv_profile_seed($uid);
 $facts=['summary'=>$profile['summary'],'experiences'=>array_slice($profile['experiences'],0,6),'skills'=>array_slice($profile['skills'],0,24),'achievements'=>array_slice($profile['achievements'],0,8)];
 // Queue a local CLI worker. The HTTP request returns immediately; the browser polls for completion.
 $dir=APP_ROOT.'/storage/cv-tailor-jobs';if(!is_dir($dir)&&!mkdir($dir,0700,true)&&!is_dir($dir))throw new RuntimeException('Impossible de créer le dossier des tâches IA.');
 $token=bin2hex(random_bytes(16));$job=$dir.'/'.$token.'.json';
 $input=['state'=>'queued','created'=>time(),'user_id'=>$uid,'offer'=>['title'=>$offer['title'],'company'=>$offer['company'],'description'=>mb_substr((string)$offer['description'],0,2600)],'facts'=>$facts,'cv'=>$cv];
 if(file_put_contents($job,json_encode($input,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE),LOCK_EX)===false)throw new RuntimeException('Impossible d’enregistrer la tâche IA.');
 $_SESSION['cv_tailor_jobs'][$token]=$uid;
 session_write_close();
 $php=PHP_BINARY;
 if(PHP_OS_FAMILY==='Windows'){
   $candidate=dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'php.exe';
   if(is_file($candidate))$php=$candidate;
 }
 $worker=__DIR__.'/cv-tailor-worker.php';
 $log=$dir.'/'.$token.'.stderr.log';
 if(PHP_OS_FAMILY==='Windows'){
   // PowerShell Start-Process is reliable on Windows; cmd/start + Unix shell quoting is not.
   $launcher=__DIR__.'/cv-tailor-launch.ps1';
   if(!is_file($launcher))throw new RuntimeException('Script de lancement Windows manquant.');
   $cmd=['powershell.exe','-NoProfile','-NonInteractive','-ExecutionPolicy','Bypass','-File',$launcher,$php,$worker,$job,$log];
   $spec=[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']];
   $proc=@proc_open($cmd,$spec,$pipes,null,null,['bypass_shell'=>true]);
   if(!is_resource($proc))throw new RuntimeException('Impossible de démarrer PowerShell depuis PHP.');
   fclose($pipes[0]);$stdout=stream_get_contents($pipes[1]);fclose($pipes[1]);
   $stderr=stream_get_contents($pipes[2]);fclose($pipes[2]);$code=proc_close($proc);
   if($code!==0){
     $detail=trim((string)$stderr.' '.(string)$stdout);
     @file_put_contents($log,'Échec du lancement : '.$detail."\n",FILE_APPEND);
     throw new RuntimeException('Lancement Windows impossible : '.mb_substr($detail?:'PowerShell a renvoyé le code '.$code,0,350));
   }
 }else{
   $command=escapeshellarg($php).' '.escapeshellarg($worker).' '.escapeshellarg($job).' >>'.escapeshellarg($log).' 2>&1 &';
   @exec($command,$unused,$code);if($code!==0)throw new RuntimeException('Démarrage du traitement IA impossible.');
 }
 $result=['ok'=>true,'state'=>'queued','job_id'=>$token];
} catch (Throwable $e) {
 error_log('[OSJOB cv-tailor] '.get_class($e).': '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine());
 $message = $e instanceof InvalidArgumentException ? $e->getMessage() :
     (($e instanceof RuntimeException) ? $e->getMessage() : 'Erreur serveur pendant la personnalisation. Consultez le journal PHP/Apache.');
 http_response_code($e instanceof InvalidArgumentException ? 400 : 500);
 $result = ['ok'=>false, 'error'=>$message];
} finally {
 restore_error_handler();
 // Discard any accidental output (including HTML warnings) before JSON.
 while (ob_get_level() > 0) ob_end_clean();
 header('Content-Type: application/json; charset=utf-8');
 if (isset($result)) {
   echo json_encode($result, JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
 }
}

