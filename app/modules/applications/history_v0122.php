<?php
declare(strict_types=1);
/** Read-only unified timeline, sourced from existing records; never invent events. */
function app_history_all(int $uid,int $applicationId,int $offerId): array {
 $events=[];
 $add=static function(string $kind,string $title,string $date,string $detail,string $key)use(&$events):void {
  if($date==='')return;
  $events[]=['kind'=>$kind,'title'=>$title,'date'=>$date,'detail'=>$detail,'key'=>$key];
 };
 $queries=[
  ['SELECT id,event_type,details,occurred_at FROM application_events WHERE application_id=? ORDER BY id DESC',[$applicationId],'event'],
  ['SELECT id,kind,title,content,created_at FROM application_ai_drafts WHERE user_id=? AND application_id=? ORDER BY id DESC',[$uid,$applicationId],'draft'],
  ['SELECT id,starts_at,interview_mode,location,notes FROM application_interviews WHERE application_id=? ORDER BY id DESC',[$applicationId],'interview'],
  ['SELECT id,title,due_at,done FROM application_reminders WHERE application_id=? ORDER BY id DESC',[$applicationId],'reminder'],
  ['SELECT id,decision,reason,decided_at FROM application_decisions WHERE user_id=? AND application_id=? ORDER BY id DESC',[$uid,$applicationId],'decision'],
  ['SELECT id,baseline_score,evidence_score,weight_percent,proposed_score,decision,created_at,decided_at,rationale_json FROM offer_score_reviews WHERE user_id=? AND job_offer_id=? ORDER BY id DESC',[$uid,$offerId],'score'],
  ['SELECT id,created_at,model,report_json FROM offer_ai_analyses WHERE user_id=? AND job_offer_id=? ORDER BY id DESC LIMIT 30',[$uid,$offerId],'analysis'],
  ['SELECT id,snapshot_original_name,sent_at,channel,recipient,notes FROM application_document_dispatches WHERE application_id=? ORDER BY id DESC',[$applicationId],'dispatch'],
  ['SELECT id,original_name,document_type,uploaded_at FROM application_documents WHERE application_id=? ORDER BY id DESC',[$applicationId],'document'],
  ['SELECT id,created_at,applied_at FROM applications WHERE id=? AND user_id=?',[$applicationId,$uid],'application'],
 ];
 foreach($queries as [$sql,$args,$type]){
  try{$stmt=db()->prepare($sql);$stmt->execute($args);$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);}catch(Throwable $e){error_log('[OS::JOB timeline '.$type.'] '.$e->getMessage());continue;}
  foreach($rows as $r){$id=(string)$r['id'];$key=$type.'-'.$id;
   if($type==='application'){$add('status','Dossier de candidature créé',(string)$r['created_at'],'Candidature créée à partir de l’offre.',$key);if(!empty($r['applied_at']))$add('status','Date d’envoi déclarée',(string)$r['applied_at'],'Date d’envoi enregistrée dans le dossier.',$key.'-sent');}
   elseif($type==='event'){$kind=in_array($r['event_type'],['email','email_received','email_sent'],true)?'email':(in_array($r['event_type'],['status','call','meeting','note'],true)?$r['event_type']:'other');$add($kind,ucfirst(str_replace('_',' ',(string)$r['event_type'])),(string)$r['occurred_at'],(string)$r['details'],$key);}
   elseif($type==='draft')$add('ia','Brouillon IA : '.(string)($r['title']?:$r['kind']),(string)$r['created_at'],(string)$r['content'],$key);
   elseif($type==='interview')$add('interview','Entretien · '.(string)$r['interview_mode'],(string)$r['starts_at'],trim((string)$r['location'].'
'.(string)$r['notes']),$key);
   elseif($type==='reminder')$add('reminder',($r['done']?'Rappel terminé : ':'Rappel prévu : ').(string)$r['title'],(string)$r['due_at'],'Échéance du rappel (pas nécessairement sa date de création).',$key);
   elseif($type==='decision')$add('status','Décision : '.(string)$r['decision'],(string)$r['decided_at'],(string)$r['reason'],$key);
   elseif($type==='score'){$detail='Initial : '.$r['baseline_score'].' % | Preuves : '.$r['evidence_score'].' % | Pondération : '.$r['weight_percent'].' % | Proposition : '.$r['proposed_score'].' % | Décision : '.$r['decision'];$rationale=json_decode((string)$r['rationale_json'],true);if(is_array($rationale))$detail.='
'.(string)($rationale['rule']??'');$add('score','Réévaluation du score · '.$r['decision'],(string)($r['decided_at']?:$r['created_at']),$detail,$key);}
   elseif($type==='analysis'){$report=json_decode((string)$r['report_json'],true);$add('ia','Analyse IA de l’offre', (string)$r['created_at'],'Modèle : '.(string)$r['model'].'
'.(string)($report['summary']??'Analyse structurée enregistrée.'),$key);}
   elseif($type==='dispatch')$add('document','Document envoyé : '.(string)$r['snapshot_original_name'],(string)$r['sent_at'],'Canal : '.$r['channel'].' · Destinataire : '.(string)$r['recipient'].'
'.(string)$r['notes'],$key);
   elseif($type==='document')$add('document','Document joint : '.(string)$r['original_name'],(string)($r['uploaded_at']??''),'Type : '.(string)$r['document_type'],$key);
  }
 }
 usort($events,static fn($a,$b)=>strcmp($b['date'],$a['date'])?:strcmp($b['key'],$a['key']));
 return $events;
}
