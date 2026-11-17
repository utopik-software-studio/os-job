<?php
declare(strict_types=1);
/** OS::JOB 0.13.1 — dashboard read-only. The applications table is authoritative for application stages. */
function dashboard_load(int $uid): array {
    $pdo=db();
    $one=static function(string $sql,array $params=[])use($pdo):int{$q=$pdo->prepare($sql);$q->execute($params);return (int)$q->fetchColumn();};
    $all=static function(string $sql,array $params=[])use($pdo):array{$q=$pdo->prepare($sql);$q->execute($params);return $q->fetchAll(PDO::FETCH_ASSOC);};
    $out=['spaces'=>0,'offers'=>0,'preparing'=>0,'sent'=>0,'interviews'=>0,'positive'=>0,'rejected'=>0,'waiting'=>0,'overdue'=>0,'upcoming'=>0,'pending_scores'=>0,'offers_recent'=>[],'applications_recent'=>[],'reminders'=>[],'interviews_next'=>[],'activity'=>[],'weeks'=>[],'warnings'=>[]];
    $queries=[
        'spaces'=>['SELECT COUNT(*) FROM search_spaces WHERE user_id=?',[$uid]],
        'offers'=>['SELECT COUNT(*) FROM job_offers WHERE user_id=? AND status<>\'archived\' AND NOT EXISTS (SELECT 1 FROM applications a WHERE a.job_offer_id=job_offers.id AND a.user_id=? AND a.status NOT IN (\'to_review\',\'to_apply\'))',[$uid,$uid]],
        'preparing'=>['SELECT COUNT(*) FROM applications WHERE user_id=? AND status=\'to_apply\'',[$uid]],
        'sent'=>['SELECT COUNT(*) FROM applications WHERE user_id=? AND (applied_at IS NOT NULL OR status IN (\'applied\',\'waiting\',\'interview\',\'proposal\',\'rejected\',\'accepted\'))',[$uid]],
        'interviews'=>['SELECT COUNT(*) FROM application_interviews i JOIN applications a ON a.id=i.application_id WHERE a.user_id=?',[$uid]],
        'positive'=>['SELECT COUNT(*) FROM applications WHERE user_id=? AND status IN (\'proposal\',\'accepted\')',[$uid]],
        'rejected'=>['SELECT COUNT(*) FROM applications WHERE user_id=? AND status=\'rejected\'',[$uid]],
        'waiting'=>['SELECT COUNT(*) FROM applications WHERE user_id=? AND status IN (\'applied\',\'waiting\')',[$uid]],
        'overdue'=>['SELECT COUNT(*) FROM application_reminders r JOIN applications a ON a.id=r.application_id WHERE a.user_id=? AND r.done=0 AND r.due_at < NOW()',[$uid]],
        'upcoming'=>['SELECT COUNT(*) FROM application_interviews i JOIN applications a ON a.id=i.application_id WHERE a.user_id=? AND i.starts_at>=NOW() AND i.starts_at<DATE_ADD(NOW(),INTERVAL 7 DAY)',[$uid]],
        'pending_scores'=>['SELECT COUNT(*) FROM offer_score_reviews r WHERE r.user_id=? AND r.decision=\'proposed\' AND NOT EXISTS (SELECT 1 FROM offer_score_reviews newer WHERE newer.user_id=r.user_id AND newer.job_offer_id=r.job_offer_id AND newer.id>r.id)',[$uid]],
    ];
    foreach($queries as $key=>[$sql,$params]){try{$out[$key]=$one($sql,$params);}catch(Throwable $ex){error_log('[OS::JOB dashboard '.$key.'] '.$ex->getMessage());$out['warnings'][]=$key;}}
    $lists=[
        'offers_recent'=>['SELECT o.id,o.title,o.company,o.city,o.status,o.updated_at,a.id AS application_id,a.status AS application_status,sc.score AS initial_score,ar.proposed_score AS accepted_score FROM job_offers o LEFT JOIN applications a ON a.job_offer_id=o.id AND a.user_id=o.user_id LEFT JOIN offer_scores sc ON sc.job_offer_id=o.id AND sc.user_id=o.user_id LEFT JOIN offer_score_reviews ar ON ar.id=(SELECT MAX(r.id) FROM offer_score_reviews r WHERE r.job_offer_id=o.id AND r.user_id=o.user_id AND r.decision=\'accepted\') WHERE o.user_id=? AND (a.id IS NULL OR a.status IN (\'to_review\',\'to_apply\')) AND o.status<>\'archived\' ORDER BY o.updated_at DESC,o.id DESC LIMIT 5',[$uid]],
        'applications_recent'=>['SELECT a.id,a.status,a.applied_at,o.title,o.company,o.city FROM applications a JOIN job_offers o ON o.id=a.job_offer_id WHERE a.user_id=? AND a.status NOT IN (\'to_review\',\'to_apply\') ORDER BY COALESCE(a.applied_at,a.created_at) DESC,a.id DESC LIMIT 5',[$uid]],
        'reminders'=>['SELECT r.id,r.title,r.due_at,a.id AS application_id,o.title AS offer_title,o.company FROM application_reminders r JOIN applications a ON a.id=r.application_id JOIN job_offers o ON o.id=a.job_offer_id WHERE a.user_id=? AND r.done=0 ORDER BY r.due_at ASC LIMIT 6',[$uid]],
        'interviews_next'=>['SELECT i.id,i.starts_at,i.interview_mode,a.id AS application_id,o.title,o.company FROM application_interviews i JOIN applications a ON a.id=i.application_id JOIN job_offers o ON o.id=a.job_offer_id WHERE a.user_id=? AND i.starts_at>=NOW() ORDER BY i.starts_at ASC LIMIT 4',[$uid]],
        'activity'=>['SELECT ev.event_type,ev.details,ev.occurred_at,a.id AS application_id,o.title,o.company FROM application_events ev JOIN applications a ON a.id=ev.application_id JOIN job_offers o ON o.id=a.job_offer_id WHERE a.user_id=? ORDER BY ev.occurred_at DESC,ev.id DESC LIMIT 5',[$uid]],
        'weeks'=>['SELECT DATE_FORMAT(applied_at,\'%x-%v\') AS wk,COUNT(*) AS qty FROM applications WHERE user_id=? AND applied_at IS NOT NULL AND applied_at>=DATE_SUB(CURDATE(),INTERVAL 42 DAY) GROUP BY DATE_FORMAT(applied_at,\'%x-%v\') ORDER BY wk',[$uid]],
    ];
    foreach($lists as $key=>[$sql,$params]){try{$out[$key]=$all($sql,$params);}catch(Throwable $ex){error_log('[OS::JOB dashboard '.$key.'] '.$ex->getMessage());$out['warnings'][]=$key;}}
    return $out;
}
function dashboard_date(?string $value,bool $withTime=false):string {
    if(!$value)return '—';
    try{return (new DateTimeImmutable($value))->format($withTime?'d/m/Y · H:i':'d/m/Y');}catch(Throwable $e){return '—';}
}
