<?php
declare(strict_types=1);
/** Lot 4: an explainable, deterministic evaluation of the stored evidence grid.
 * Ollama prose never changes the score. Only structured, traceable criteria count.
 */
function review_offer(int $uid,int $offerId): array {
 $s=db()->prepare('SELECT o.id,o.title,o.company,sc.score AS baseline FROM job_offers o LEFT JOIN offer_scores sc ON sc.job_offer_id=o.id AND sc.user_id=o.user_id WHERE o.id=? AND o.user_id=? LIMIT 1');
 $s->execute([$offerId,$uid]);$offer=$s->fetch(PDO::FETCH_ASSOC);
 if(!$offer)throw new InvalidArgumentException('Offre introuvable.');
 return $offer;
}
function review_latest_analysis(int $uid,int $offerId): ?array {
 $s=db()->prepare('SELECT id,report_json,created_at FROM offer_ai_analyses WHERE user_id=? AND job_offer_id=? ORDER BY id DESC LIMIT 1');
 $s->execute([$uid,$offerId]);$row=$s->fetch(PDO::FETCH_ASSOC);
 if(!$row)return null;
 $row['report']=json_decode((string)$row['report_json'],true);
 if(!is_array($row['report']))throw new RuntimeException('Analyse IA illisible : relancer l’analyse.');
 return $row;
}
function review_calculate(array $report): array {
 $criteria=$report['criteria']??null;
 if(!is_array($criteria)||count($criteria)===0)throw new RuntimeException('Cette analyse ne contient pas de grille de critères vérifiables. Lancez une nouvelle analyse IA.');
 $total=0.0;$earned=0.0;$details=[];
 foreach($criteria as $c){
  if(!is_array($c)||!isset($c['criterion'],$c['status']))continue;
  $priority=(string)($c['priority']??'context');$factor=$priority==='essential'?2.0:1.0;
  $status=(string)$c['status'];$profile=(string)($c['profile_evidence']??'');
  $proofs=$c['proofs']??[];
  $verified=false;
  if(is_array($proofs))foreach($proofs as $p){if(is_array($p)&&trim((string)($p['excerpt']??''))!==''&&trim((string)($p['source']??''))!==''){$verified=true;break;}}
  // A declared language/skill or a free-text model assertion alone cannot establish proof.
  $points=$status==='confirmed'&&$verified?1.0:($status==='partial'&&$verified?0.5:0.0);
  $total+=$factor;$earned+=$factor*$points;
  $details[]=['criterion'=>mb_substr((string)$c['criterion'],0,180),'priority'=>$priority,'status'=>$status,'evidence_verified'=>$verified,'points'=>$points,'weight'=>$factor,'source'=>$verified?'Source et extrait présents dans la grille':'Non démontré par une preuve structurée'];
 }
 if($total<=0)throw new RuntimeException('Aucun critère exploitable dans la grille IA.');
 return ['evidence_score'=>(int)round(100*$earned/$total),'criteria'=>$details,'rule'=>'Score conservateur calculé sur la grille de preuves (essentiel x2, confirmé avec source 100 %, partiel documenté 50 %, sinon 0 %). Une déclaration IA non vérifiée ne compte pas.'];
}
function review_history(int $uid,int $offerId): array {
 $s=db()->prepare('SELECT id,ai_analysis_id,baseline_score,evidence_score,weight_percent,proposed_score,rationale_json,decision,created_at,decided_at FROM offer_score_reviews WHERE user_id=? AND job_offer_id=? ORDER BY id DESC LIMIT 30');
 $s->execute([$uid,$offerId]);return $s->fetchAll(PDO::FETCH_ASSOC);
}
function review_create(int $uid,int $offerId,int $weight): void {
 if($weight<0||$weight>50||$weight%5!==0)throw new InvalidArgumentException('Pondération invalide (0 à 50 %, par pas de 5).');
 $offer=review_offer($uid,$offerId);
 if($offer['baseline']===null)throw new RuntimeException('Calculez d’abord le score initial de cette offre.');
 $base=(int)$offer['baseline'];$analysis=review_latest_analysis($uid,$offerId);
 if(!$analysis)throw new RuntimeException('Aucune analyse IA enregistrée pour cette offre.');
 $calc=review_calculate($analysis['report']);$evidence=$calc['evidence_score'];
 $proposed=(int)round(($base*(100-$weight)+$evidence*$weight)/100);
 $json=json_encode($calc,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
 if($json===false)throw new RuntimeException('Erreur de sérialisation de la grille.');
 $s=db()->prepare('INSERT INTO offer_score_reviews(user_id,job_offer_id,ai_analysis_id,baseline_score,evidence_score,weight_percent,proposed_score,rationale_json) VALUES (?,?,?,?,?,?,?,?)');
 $s->execute([$uid,$offerId,(int)$analysis['id'],$base,$evidence,$weight,$proposed,$json]);
}
function review_decide(int $uid,int $offerId,int $reviewId,string $decision): void {
 if(!in_array($decision,['accepted','rejected'],true))throw new InvalidArgumentException('Décision inconnue.');
 review_offer($uid,$offerId);
 $s=db()->prepare('UPDATE offer_score_reviews SET decision=?,decided_at=NOW() WHERE id=? AND user_id=? AND job_offer_id=? AND decision="proposed"');
 $s->execute([$decision,$reviewId,$uid,$offerId]);
 if($s->rowCount()!==1)throw new RuntimeException('Proposition introuvable ou déjà traitée.');
 // Never overwrite offer_scores: baseline remains the original scoring engine's result.
}

/** Last manually accepted review for each offer; baseline score remains unchanged. */
function review_accepted_by_offer(int $uid): array {
 $q=db()->prepare('SELECT r.* FROM offer_score_reviews r WHERE r.user_id=? AND r.decision="accepted" AND NOT EXISTS (SELECT 1 FROM offer_score_reviews newer WHERE newer.user_id=r.user_id AND newer.job_offer_id=r.job_offer_id AND newer.decision="accepted" AND newer.id>r.id)');
 $q->execute([$uid]);$result=[];foreach($q->fetchAll(PDO::FETCH_ASSOC) as $r)$result[(int)$r['job_offer_id']]=$r;return $result;
}
