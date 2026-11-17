<?php
declare(strict_types=1);
/** Queue is database-backed. A CLI worker performs slow Ollama requests. */
function ai_job_latest(int $uid,int $offerId):?array {
 $s=db()->prepare('SELECT id,status,error_message,created_at,started_at,finished_at FROM offer_ai_jobs WHERE user_id=? AND job_offer_id=? ORDER BY id DESC LIMIT 1');
 $s->execute([$uid,$offerId]);return $s->fetch(PDO::FETCH_ASSOC)?:null;
}
function ai_job_enqueue(int $uid,int $offerId):int {
 $s=db()->prepare('SELECT id FROM job_offers WHERE id=? AND user_id=?');$s->execute([$offerId,$uid]);if(!$s->fetchColumn())throw new InvalidArgumentException('Offre introuvable.');
 $last=ai_job_latest($uid,$offerId);
 if($last && in_array($last['status'],['pending','running'],true)){
  if($last['status']==='running' && $last['started_at'] && strtotime($last['started_at'])<time()-600){
   db()->prepare("UPDATE offer_ai_jobs SET status='failed',finished_at=NOW(),error_message='Tâche interrompue (délai dépassé).' WHERE id=? AND status='running'")->execute([$last['id']]);
  }else return (int)$last['id'];
 }
 $s=db()->prepare("INSERT INTO offer_ai_jobs (user_id,job_offer_id,status) VALUES (?,?,'pending')");$s->execute([$uid,$offerId]);return (int)db()->lastInsertId();
}
function ai_job_launch(int $id):bool {
 if(PHP_OS_FAMILY!=='Windows' || !function_exists('proc_open'))return false;
 $php=dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'php.exe';
 if(!is_file($php))$php='C:\\xampp\\php\\php.exe';
 if(!is_file($php))return false;
 $worker=APP_ROOT.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'ai'.DIRECTORY_SEPARATOR.'worker.php';
 // Start-Process returns immediately; the worker runs independently from Apache.
 $quote=static fn(string $value):string => "'".str_replace("'","''",$value)."'";
 $ps='$p=Start-Process -FilePath '.$quote($php).' -ArgumentList @('.$quote($worker).','.$quote((string)$id).') -WindowStyle Hidden -PassThru; if($p){$p.Id}';
 $cmd=['powershell.exe','-NoProfile','-NonInteractive','-ExecutionPolicy','Bypass','-Command',$ps];
 $pipes=[];$process=@proc_open($cmd,[1=>['pipe','w'],2=>['pipe','w']],$pipes,APP_ROOT);
 if(!is_resource($process))return false;
 stream_set_timeout($pipes[1],4);$out=stream_get_contents($pipes[1]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);
 return $exit===0 && preg_match('/\d+/',(string)$out)===1;
}
function ai_job_run(int $id):void {
 $s=db()->prepare("UPDATE offer_ai_jobs SET status='running',started_at=NOW(),error_message=NULL WHERE id=? AND status='pending'");$s->execute([$id]);
 if($s->rowCount()!==1)return;
 $s=db()->prepare('SELECT user_id,job_offer_id FROM offer_ai_jobs WHERE id=?');$s->execute([$id]);$job=$s->fetch(PDO::FETCH_ASSOC);
 try{
  offer_ai_analyze((int)$job['user_id'],(int)$job['job_offer_id']);
  db()->prepare("UPDATE offer_ai_jobs SET status='completed',finished_at=NOW() WHERE id=?")->execute([$id]);
 }catch(Throwable $e){
  error_log('[OS::JOB worker] '.$e);
  db()->prepare("UPDATE offer_ai_jobs SET status='failed',finished_at=NOW(),error_message=? WHERE id=?")->execute([mb_substr($e->getMessage(),0,590),$id]);
 }
}
