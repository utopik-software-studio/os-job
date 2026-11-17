<?php
declare(strict_types=1);
/** Heuristic, explainable local matching. Unknown information is never treated as a negative. */
function score_offer(int $uid,int $offerId):array{
 $q=db()->prepare('SELECT * FROM job_offers WHERE id=? AND user_id=?');$q->execute([$offerId,$uid]);$offer=$q->fetch();if(!$offer)throw new InvalidArgumentException('Offre inconnue.');
 $q=db()->prepare('SELECT * FROM profiles WHERE user_id=?');$q->execute([$uid]);$p=$q->fetch();if(!$p)return ['score'=>null,'details'=>['Profil absent']];
 $q=db()->prepare('SELECT name FROM profile_skills WHERE profile_id=?');$q->execute([$p['id']]);$skills=array_column($q->fetchAll(),'name');
 $q=db()->prepare('SELECT title FROM career_targets WHERE profile_id=?');$q->execute([$p['id']]);$targets=array_column($q->fetchAll(),'title');
 $q=db()->prepare('SELECT title FROM experiences WHERE profile_id=?');$q->execute([$p['id']]);$experience=array_column($q->fetchAll(),'title');
 $norm=static function(string $s):string{$s=mb_strtolower($s,'UTF-8');$t=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);return preg_replace('/[^a-z0-9]+/',' ', $t!==false?$t:$s)??'';};
 $hay=$norm(($offer['title']??'').' '.($offer['description']??''));$title=$norm($offer['title']??'');
 $parts=[];$weights=[];
 $allTitles=array_merge($targets,$experience,[(string)($p['headline']??'')]);$matchedTitle=false;
 foreach($allTitles as $t){$t=trim($norm($t));if(mb_strlen($t)>=5&&str_contains($title,$t)){$matchedTitle=true;break;}}
 if($title!==''&&count(array_filter($allTitles))){$weights['position']=30;$parts['position']=$matchedTitle?30:0;}
 $matches=[];foreach($skills as $skill){$n=trim($norm($skill));if(mb_strlen($n)>=3&&str_contains($hay,$n))$matches[]=$skill;}
 if($skills&&trim((string)$offer['description'])!==''){$weights['skills']=40;$parts['skills']=(int)round(40*min(1,count($matches)/min(5,count($skills))));}
 $min=(int)($p['salary_min']??0);$max=(int)($offer['salary_max']??0);$offermin=(int)($offer['salary_min']??0);
 if($min>0&&($max>0||$offermin>0)){$weights['salary']=20;$parts['salary']=($max?:$offermin)>=$min?20:0;}
 $city=trim($norm((string)($offer['city']??'')));$preferred=trim($norm((string)($p['preferred_locations']??'')));
 if($city!==''&&$preferred!==''){$weights['location']=10;$parts['location']=str_contains($preferred,$city)?10:0;}
 $total=array_sum($weights);$score=$total?min(100,(int)round(100*array_sum($parts)/$total)):null;
 $details=['Méthode locale indicative, basée sur les informations disponibles.','Critères évalués : '.implode(', ',array_keys($weights))];
 if($matches)$details[]='Compétences repérées : '.implode(', ',array_slice($matches,0,12));
 if(!$matchedTitle&&isset($weights['position']))$details[]='Intitulé du poste non retrouvé à l’identique dans le profil.';
 if(!isset($weights['skills']))$details[]='Description ou compétences insuffisantes pour évaluer les compétences.';
 if(!isset($weights['salary']))$details[]='Salaire non renseigné : critère exclu du calcul.';
 return ['score'=>$score,'details'=>$details];
}
function score_save(int $uid,int $offerId):void{
 $result=score_offer($uid,$offerId);
 $s=db()->prepare('INSERT INTO offer_scores (user_id,job_offer_id,score,details_json,calculated_at) VALUES (?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE score=VALUES(score),details_json=VALUES(details_json),calculated_at=NOW()');
 $s->execute([$uid,$offerId,$result['score'],json_encode($result['details'],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
}
function score_refresh_all(int $uid):void{$s=db()->prepare('SELECT id FROM job_offers WHERE user_id=?');$s->execute([$uid]);foreach($s->fetchAll(PDO::FETCH_COLUMN) as $id)score_save($uid,(int)$id);}
