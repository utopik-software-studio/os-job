<?php
declare(strict_types=1);
/** v0.13.2 — read-only, parameterized and user-scoped analytics. */
function analytics_load(int $uid, string $period): array {
 $days=['30'=>30,'90'=>90,'180'=>180,'365'=>365,'all'=>null][$period]??90;
 $from=$days===null?null:(new DateTimeImmutable('today'))->modify('-'.($days-1).' days')->format('Y-m-d 00:00:00');
 $pdo=db();$warnings=[];
 $run=static function(string $key,string $sql,array $args=[])use($pdo,&$warnings):array{
  try{$stmt=$pdo->prepare($sql);$stmt->execute($args);return $stmt->fetchAll(PDO::FETCH_ASSOC);}catch(Throwable $e){error_log('[OS::JOB analytics '.$key.'] '.$e->getMessage());$warnings[]=$key;return [];}
 };
 $periodSql=$from!==null?' AND a.applied_at >= ?':'';
 $args=[$uid];if($from!==null)$args[]=$from;
 // Sent applications have an actual send date; this is necessary for period-based rates.
 $sent=$run('sent','SELECT a.id,a.status,a.applied_at,a.application_platform,o.company,o.title FROM applications a JOIN job_offers o ON o.id=a.job_offer_id WHERE a.user_id=? AND a.applied_at IS NOT NULL'.$periodSql.' ORDER BY a.applied_at DESC',$args);
 $ids=array_map(static fn($r)=>(int)$r['id'],$sent);$n=count($sent);
 $interviewed=[];$responses=[];$interviewEvents=[];
 if($ids){
  $marks=implode(',',array_fill(0,count($ids),'?'));
  foreach($run('interviews','SELECT DISTINCT application_id FROM application_interviews WHERE application_id IN ('.$marks.')',$ids) as $r)$interviewed[(int)$r['application_id']]=true;
  foreach($run('events','SELECT application_id,event_type,details,occurred_at FROM application_events WHERE application_id IN ('.$marks.') ORDER BY occurred_at',$ids) as $r){
   $id=(int)$r['application_id'];$type=strtolower((string)$r['event_type']);
   if(in_array($type,['response','reply','email_received'],true)||($type==='email'&&preg_match('/^(email reçu|mail reçu|courriel reçu|received email|inbound email)\s*:/iu',trim((string)$r['details']))))$responses[$id]=true;
   if(in_array($type,['interview','meeting'],true)||($type==='status'&&preg_match('/(?:^|→)\s*Entretien\s*$/iu',trim((string)$r['details']))))$interviewEvents[$id]=true;
  }
 }
 $statuses=[];$platforms=[];$months=[];$replyCount=0;$positive=0;$rejected=0;$waiting=0;
 foreach($sent as $a){$status=(string)$a['status'];$statuses[$status]=($statuses[$status]??0)+1;
  $platform=trim((string)($a['application_platform']??''));$platform=$platform!==''?$platform:'Non renseignée';$platforms[$platform]=($platforms[$platform]??0)+1;
  $month=substr((string)$a['applied_at'],0,7);$months[$month]=($months[$month]??0)+1;
  if(in_array($status,['interview','proposal','accepted'],true)||isset($interviewed[(int)$a['id']])||isset($interviewEvents[(int)$a['id']]))$interviewed[(int)$a['id']]=true;
  if(in_array($status,['interview','proposal','accepted','rejected'],true)||isset($interviewed[(int)$a['id']])||isset($responses[(int)$a['id']]))$replyCount++;
  if(in_array($status,['proposal','accepted'],true))$positive++;
  if($status==='rejected')$rejected++;
  if(in_array($status,['applied','waiting'],true))$waiting++;
 }
 ksort($months);arsort($platforms);
 $scoreSql='SELECT o.id,o.title,o.company,s.score AS baseline,(SELECT r.proposed_score FROM offer_score_reviews r WHERE r.job_offer_id=o.id AND r.user_id=o.user_id AND r.decision=\'accepted\' ORDER BY r.id DESC LIMIT 1) AS validated FROM job_offers o JOIN offer_scores s ON s.job_offer_id=o.id AND s.user_id=o.user_id WHERE o.user_id=?';
 $scoreArgs=[$uid];if($from!==null){$scoreSql.=' AND o.created_at >= ?';$scoreArgs[]=$from;}
 $scores=$run('scores',$scoreSql.' ORDER BY o.created_at DESC LIMIT 1000',$scoreArgs);
 $base=[];$effective=[];$changed=0;foreach($scores as $r){if($r['baseline']===null)continue;$base[]=(int)$r['baseline'];$effective[]=(int)($r['validated']??$r['baseline']);if($r['validated']!==null)$changed++;}
 return ['period'=>$period,'from'=>$from,'sent'=>$n,'interviewed'=>count($interviewed),'responded'=>$replyCount,'positive'=>$positive,'rejected'=>$rejected,'waiting'=>$waiting,'statuses'=>$statuses,'platforms'=>$platforms,'months'=>$months,'scores_count'=>count($base),'baseline_avg'=>$base?round(array_sum($base)/count($base),1):null,'effective_avg'=>$effective?round(array_sum($effective)/count($effective),1):null,'validated_scores'=>$changed,'warnings'=>$warnings];
}
function analytics_pct(int $num,int $den):string{return $den>0?number_format($num*100/$den,1,',',' ').' %':'—';}
