<?php
declare(strict_types=1);
ob_start();ini_set('display_errors','0');header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try {
 require dirname(__DIR__,2).'/app/config/config.php';
 if(session_status()!==PHP_SESSION_ACTIVE)session_start();
 $token=(string)($_GET['job_id']??'');
 if(!preg_match('/^[a-f0-9]{32}$/',$token)||!isset($_SESSION['cv_tailor_jobs'][$token]))throw new InvalidArgumentException('Tâche inconnue ou session expirée.');
 $file=APP_ROOT.'/storage/cv-tailor-jobs/'.$token.'.json';if(!is_file($file))throw new RuntimeException('Tâche introuvable.');
 $job=json_decode((string)file_get_contents($file),true);if(!is_array($job))throw new RuntimeException('Lecture de la tâche impossible.');
 if(($job['user_id']??null)!==$_SESSION['cv_tailor_jobs'][$token])throw new InvalidArgumentException('Accès refusé.');
 $state=$job['state']??'error';if(in_array($state,['queued','running'],true)&&time()-(int)($job['created']??0)>600) $state='error';
 $out=['ok'=>$state!=='error','state'=>$state];if($state==='done'){$out['content']=$job['result']??null;unset($_SESSION['cv_tailor_jobs'][$token]);@unlink($file);}if($state==='error'){$out['error']=$job['error']??'Le traitement a dépassé 10 minutes ou le processus ne s’est pas lancé. Vérifiez les journaux .stderr.log dans storage/cv-tailor-jobs.';unset($_SESSION['cv_tailor_jobs'][$token]);/* Fichier conservé pour diagnostic. */}
} catch(Throwable $e){http_response_code(400);$out=['ok'=>false,'error'=>$e->getMessage()];}
while(ob_get_level()>0)ob_end_clean();echo json_encode($out,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
