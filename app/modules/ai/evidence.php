<?php
declare(strict_types=1);
/** Evidence is a traceable textual match, never proof of professional proficiency. */
function offer_ai_excerpt(string $text,string $pattern):string {
 $text=preg_replace('/\*\*|__|[`#]/u','',strip_tags($text));
 $text=preg_replace('/\s+/u',' ',str_replace(['\\r','\\n'], ' ', $text));
 if(!preg_match($pattern,$text,$m,PREG_OFFSET_CAPTURE))return '';
 $offset=$m[0][1];$left=substr($text,0,$offset);$start=max((int)(strrpos($left,'. ')===false?0:strrpos($left,'. ')+2),(int)(strrpos($left,'; ')===false?0:strrpos($left,'; ')+2));
 if($offset-$start>155)$start=max(0,$offset-65);
 $chunk=substr($text,$start,260);$end=strpos($chunk,'. ');
 if($end!==false && $end>=$offset-$start)$chunk=substr($chunk,0,$end+1);
 $chunk=trim($chunk," \t\n\r\0\x0B,;:-*");
 if(mb_strlen($chunk)>200)$chunk=mb_substr($chunk,0,197).'…';
 return ($start>0 && !preg_match('/^[A-ZÀ-Ý]/u',$chunk)?'…':'').$chunk;
}
/** Evidence snippets must preserve complete words and indicate omissions. */
function offer_ai_short(string $text,int $limit=220):string {
 $text=trim((string)preg_replace('/\s+/u',' ',strip_tags($text)));
 if(mb_strlen($text)<=$limit)return $text;
 $cut=mb_substr($text,0,$limit);
 $last=mb_strrpos($cut,' ');
 if($last!==false && $last>$limit/2)$cut=mb_substr($cut,0,$last);
 return rtrim($cut," ,;:-").'…';
}
/** Contextual, user-authored activity: more informative than a keyword in a title. */
function offer_ai_component_from_achievement(array $a,string $name,string $pattern):?string {
 $actions=mb_strtolower((string)($a['actions_text']??''));
 $context=mb_strtolower((string)($a['context_text']??''));
 $detail=$actions.' '.$context;
 if(preg_match($pattern,$detail))return 'documented';
 if($name==='Recette fonctionnelle' && preg_match('/v[ée]rifi|valid|contr[oô]l|test|conformit[ée]/iu',$actions)
    && preg_match('/fonctionnalit[ée]|outil|crm|logiciel|application|scoring|syst[eè]me|d[ée]ploiement/iu',$detail))return 'partial';
 if($name==='Tests / cas de test' && preg_match('/test|sc[ée]nario|cas de test/iu',$actions))return 'partial';
 if($name==='Cycle de livraison documenté' && preg_match('/livraison|d[ée]ploiement|sprint|it[ée]ration/iu',$actions)
    && preg_match('/agil|scrum|kanban|sprint/iu',$detail))return 'partial';
 return null;
}
function offer_ai_evidence(int $uid,int $offerId):array {
 $q=db()->prepare('SELECT title,description,salary_min,salary_max FROM job_offers WHERE id=? AND user_id=?');$q->execute([$offerId,$uid]);$offer=$q->fetch(PDO::FETCH_ASSOC);if(!$offer)return [];
 $q=db()->prepare('SELECT * FROM profiles WHERE user_id=?');$q->execute([$uid]);$p=$q->fetch(PDO::FETCH_ASSOC);if(!$p)return [];
 $pid=(int)$p['id'];$facts=[];
 foreach(['experiences'=>['title','organization','description'],'profile_skills'=>['name','description'],'profile_languages'=>['name','level']] as $table=>$fields){$s=db()->prepare('SELECT '.implode(',',$fields).' FROM '.$table.' WHERE profile_id=?');$s->execute([$pid]);$facts[$table]=$s->fetchAll(PDO::FETCH_ASSOC);}
 // Professional achievements are user-authored evidence, independent of the experience list.
 $q=db()->prepare('SELECT title,organization,context_text,actions_text,results_text,evidence_text FROM career_achievements WHERE user_id=? ORDER BY id DESC LIMIT 100');
 $q->execute([$uid]);$facts['career_achievements']=$q->fetchAll(PDO::FETCH_ASSOC);
 $description=(string)$offer['description'];$offerText=mb_strtolower($description);
 $rules=[
  ['Gestion de projets et coordination métier/IT','/coordonn|équipes métiers|équipes tech|roadmap|pilotage/iu','/coordination|pilotage de projets|gestion de projets|responsable de projet|directeur informatique|\bdsi\b|\brsi\b/iu','essential','CV : présenter un projet précis de coordination métier/IT, avec rôle et résultat.'],
  ['Web et parcours numériques','/sites? web|parcours digitaux|parcours utilisateurs?|\bweb\b/iu','/parcours clients|expérience utilisateur|\bweb\b|sites? internet|e-commerce/iu','essential','CV : relier une réalisation web concrète à l’amélioration d’un parcours utilisateur.'],
  ['CRM et collaboration métier','/équipes métiers|marketing|call center|pricing|\bcrm\b/iu','/\bcrm\b|playiad|coordination.*métier|collaboration.*métier/iu','context','Entretien : préparer un exemple de collaboration entre métiers et équipes techniques.'],
  ['Backlog, user stories et critères d’acceptation','/backlog|user stor|critères d.acceptation|prioris/iu','/backlog|user stor|critères d.acceptation/iu','essential','Avant candidature : vérifier les projets où tu as priorisé des besoins, rédigé des spécifications ou validé des critères de recette.'],
  ['Agilité et méthodes de livraison','/agil|scrum|sprint/iu','/agil|scrum|sprint/iu','essential','Entretien : préparer un exemple réel de cycle de livraison agile et préciser ton rôle.'],
  ['Recette, tests et mise en production','/recette|\buat\b|tests?|mise en production/iu','/recette|\buat\b|tests?|mise en production/iu','essential','CV : citer une recette fonctionnelle ou une mise en production suivie, si elle est documentable.'],
  ['Outils spécifiques (Jira, Figma, Strapi…)','/jira|figma|strapi|contentsquare|pimcore/iu','/jira|figma|strapi|contentsquare|pimcore/iu','context','Avant entretien : distinguer les outils réellement utilisés de ceux simplement connus.'],
 ];
 $out=[];
 foreach($rules as [$label,$offerPattern,$profilePattern,$priority,$action]){
  $offerExcerpt=offer_ai_excerpt($description,$offerPattern);if($offerExcerpt==='')continue;
  $matches=[];
  foreach(['experiences','profile_skills'] as $table){foreach($facts[$table] as $item){
   $candidate=implode('. ',array_map('strval',$item));$excerpt=offer_ai_excerpt($candidate,$profilePattern);
   if($excerpt==='')continue;
   $source=$table==='experiences'?trim((string)($item['organization']??'').' — '.(string)($item['title']??'')):(string)$item['name'];
   $kind=$table==='experiences'?'experience':'declared_skill';
   $score=($kind==='experience'?40:12)+min(25,mb_strlen($excerpt)/12);
   // Prefer concrete, operational evidence over broad consulting summaries.
   if(preg_match('/iad españa|iad espana/iu',$source))$score+=12;
   if(preg_match('/pilot|livr|mise en production|coordonn|cadrage|conception|audit/iu',$excerpt))$score+=8;
   $matches[]=['source'=>$source,'excerpt'=>$excerpt,'type'=>$kind,'rank'=>$score];
  }}
  foreach($facts['career_achievements'] as $achievement){
   $content=implode('. ',[(string)$achievement['title'],(string)$achievement['context_text'],(string)$achievement['actions_text'],(string)$achievement['results_text']]);
   $excerpt=offer_ai_excerpt($content,$profilePattern);
   if($excerpt==='')continue;
   $source='Réalisation : '.trim((string)$achievement['title']).' — '.trim((string)$achievement['organization']);
   $details=trim((string)$achievement['actions_text']);
   $result=trim((string)$achievement['results_text']);
   $evidence=offer_ai_short($excerpt,210);
   if($details!=='')$evidence.=' | Action : '.offer_ai_short($details,190);
   if($result!=='')$evidence.=' | Résultat : '.offer_ai_short($result,155);
   $matches[]=['source'=>$source,'excerpt'=>$evidence,'type'=>'achievement','rank'=>90];
  }
  usort($matches,static fn($x,$y)=>$y['rank']<=>$x['rank']);
  $unique=[];$chosen=[];
  foreach($matches as $m){$key=mb_strtolower($m['source']);if(isset($unique[$key]))continue;$unique[$key]=true;$chosen[]=$m;if(count($chosen)===3)break;}
  if(!$chosen){$text=(string)($p['summary']??'').' '.(string)($p['headline']??'');$excerpt=offer_ai_excerpt($text,$profilePattern);if($excerpt!=='')$chosen[]=['source'=>'Présentation du profil','excerpt'=>$excerpt,'type'=>'profile_summary','rank'=>0];}
  $kind=$chosen[0]['type']??'none';
  $parts=[];foreach($chosen as $m)$parts[]=$m['source'].' : '.$m['excerpt'];
  $evidence=$parts?implode(' | ',$parts):'Aucune mention explicite retrouvée dans les données du profil.';
  $subskills=[];
  if(str_contains($label,'Recette, tests')){$subskills=['Recette fonctionnelle'=>'/recette|\buat\b/iu','Tests / cas de test'=>'/tests?|cas de test/iu','Mise en production'=>'/mise en production|déploiement/iu'];}
  elseif(str_contains($label,'Backlog')){$subskills=['Backlog'=>'/backlog/iu','User stories'=>'/user stor/iu','Critères d’acceptation'=>'/critères d.acceptation/iu'];}
  elseif(str_contains($label,'Agilité')){$subskills=['Méthode agile déclarée'=>'/agil|scrum|kanban/iu','Cycle de livraison documenté'=>'/sprint|livraison agile|cycle agile/iu'];}
  $components=[];
  foreach($subskills as $name=>$pattern){$found=false;$declared=false;$partial=false;
   foreach($facts['experiences'] as $x){if(preg_match($pattern,(string)$x['description'])){$found=true;break;}}
   if(!$found)foreach($facts['career_achievements'] as $x){
    // The action/context must support the activity; a title alone is not proof.
    $detail=(string)$x['actions_text'].' '.(string)$x['context_text'];
    $coverage=offer_ai_component_from_achievement($x,$name,$pattern);
    if($coverage==='documented'){$found=true;break;}
    if($coverage==='partial')$partial=true;
   }
   if(!$found)foreach($facts['profile_skills'] as $x){if(preg_match($pattern,(string)$x['name'].' '.(string)$x['description'])){$declared=true;break;}}
   $components[]=['label'=>$name,'coverage'=>$found?'documented':($partial?'partial':($declared?'declared':'unknown'))];}
  $covered=count(array_filter($components,static fn($c)=>$c['coverage']!=='unknown'));
  $status=$chosen?'confirmed':'verify';
  if($components){
   $documented=count(array_filter($components,static fn($c)=>$c['coverage']==='documented'));
   $partial=count(array_filter($components,static fn($c)=>$c['coverage']==='partial'));
   $declared=count(array_filter($components,static fn($c)=>$c['coverage']==='declared'));
   // A declared skill alone cannot fully confirm a multi-part professional requirement.
   $status=$documented===count($components)?'confirmed':(($documented+$partial+$declared)>0?'partial':'verify');
  }
  $out[]=['criterion'=>$label,'status'=>$status,'priority'=>$priority,'evidence_type'=>$kind,
   'offer_evidence'=>$offerExcerpt,'profile_evidence'=>$evidence,'proofs'=>$chosen,'components'=>$components,
   'coverage'=>$components?($covered===count($components)?'complete':($covered?'partial':'unverified')):($chosen?'indicative':'unverified'),
   'action'=>$action];
 }
 if(preg_match('/anglais|english/iu',$description)){
  $english='';foreach($facts['profile_languages'] as $l)if(preg_match('/anglais|english/iu',(string)$l['name']))$english=(string)$l['level'];
  $out[]=['criterion'=>'Anglais professionnel','status'=>'verify','priority'=>'essential','evidence_type'=>'declared_language','offer_evidence'=>offer_ai_excerpt($description,'/anglais|english/iu'),'profile_evidence'=>$english!==''?'Niveau déclaré : '.$english.' ; adéquation professionnelle à confirmer':'Niveau non documenté','action'=>'Entretien : clarifier le niveau d’anglais opérationnel réellement nécessaire.'];
 }
 $floor=is_numeric($p['salary_min']??null)?(float)$p['salary_min']:null;$ceiling=is_numeric($offer['salary_max']??null)?(float)$offer['salary_max']:null;
 if($floor!==null&&$floor>0&&$ceiling!==null&&$ceiling>0){$gap=$floor>$ceiling;$out[]=['criterion'=>'Rémunération','status'=>$gap?'gap':($floor===$ceiling?'verify':'confirmed'),'priority'=>'essential','evidence_type'=>'numeric_comparison','offer_evidence'=>'Maximum annoncé : '.number_format($ceiling,0,',',' ').' €','profile_evidence'=>'Minimum souhaité : '.number_format($floor,0,',',' ').' €'.($gap?' (supérieur au maximum annoncé)':($floor===$ceiling?' (au plafond de la fourchette)':'')),'action'=>$gap?'Avant candidature : clarifier si la rémunération peut dépasser la fourchette annoncée.':'Avant entretien : vérifier si le plafond salarial est accessible selon le profil.'];}
 return $out;
}
