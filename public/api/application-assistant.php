<?php
declare(strict_types=1);
ob_start();ini_set('display_errors','0');header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try{
 require dirname(__DIR__,2).'/app/config/config.php';
 require APP_ROOT.'/app/core/functions.php';
 require APP_ROOT.'/app/core/database.php';
 require APP_ROOT.'/app/modules/searches/searches.php';
 if(session_status()!==PHP_SESSION_ACTIVE)session_start();
 if(($_SERVER['REQUEST_METHOD']??'')!=='POST')throw new RuntimeException('Méthode invalide.');
 if(empty($_SESSION['csrf'])||!hash_equals((string)$_SESSION['csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Session expirée.');
 $uid=local_user_id();$id=(int)($_POST['id']??0);$mode=(string)($_POST['mode']??'');
 if(!in_array($mode,['analyze','reply','interview','save_draft'],true))throw new RuntimeException('Mode invalide.');
 $s=db()->prepare('SELECT o.title,o.company,o.description,a.status FROM applications a JOIN job_offers o ON o.id=a.job_offer_id WHERE a.id=? AND a.user_id=?');$s->execute([$id,$uid]);$offer=$s->fetch(PDO::FETCH_ASSOC);if(!$offer)throw new RuntimeException('Candidature introuvable.');
 if($mode==='save_draft'){
  $kind=(string)($_POST['kind']??'');if(!in_array($kind,['reply','interview'],true))throw new RuntimeException('Type de brouillon invalide.');
  $title=mb_substr(trim((string)($_POST['title']??'')),0,190);
  $draft=trim((string)($_POST['draft']??''));if($draft===''||mb_strlen($draft)>20000)throw new RuntimeException('Brouillon vide ou trop long.');
  $q=db()->prepare('INSERT INTO application_ai_drafts (user_id,application_id,kind,title,content) VALUES (?,?,?,?,?)');$q->execute([$uid,$id,$kind,$title,$draft]);
  ob_end_clean();echo json_encode(['ok'=>true,'data'=>['id'=>(int)db()->lastInsertId()]],JSON_UNESCAPED_UNICODE);exit;
 }
 $content=trim((string)($_POST['content']??''));if(mb_strlen($content)>12000)throw new RuntimeException('Texte trop long (12 000 caractères maximum).');
 if($mode!=='interview'&&mb_strlen($content)<10)throw new RuntimeException('Collez un email ou décrivez le message à préparer.');
 if(!function_exists('curl_init'))throw new RuntimeException('Extension PHP cURL nécessaire.');
 $context='Offre: '.$offer['title'].' chez '.$offer['company']."\nDescription (extrait): ".mb_substr((string)$offer['description'],0,2000)."\nStatut actuel: ".$offer['status'];
 $profileFacts=[];
 try {
  $q=db()->prepare('SELECT title,organization,actions_text,results_text FROM career_achievements WHERE user_id=? ORDER BY id DESC LIMIT 8');
  $q->execute([$uid]);
  foreach($q->fetchAll(PDO::FETCH_ASSOC) as $r){$profileFacts[]='Réalisation vérifiée : '.mb_substr(implode(' | ',array_filter([$r['title'],$r['organization'],$r['actions_text'],$r['results_text']])),0,450);}
 }catch(Throwable $ignored){}
 $context.="\nÉléments factuels du candidat (uniquement ceux-ci sont vérifiés) :\n".($profileFacts?implode("\n",$profileFacts):'Aucun élément de parcours vérifié disponible : ne pas inventer de réalisations ni de formation.');
 $context.="\nL'entreprise citée dans l'offre peut être un cabinet de recrutement : ne jamais affirmer qu'elle est l'employeur final sans preuve explicite.";
 $schema='';
 // Le dossier fait foi pour le suivi, mais le message collé peut concerner une autre offre.
 $mismatchWarning='';
 if($mode==='analyze'||$mode==='reply'){
  if(preg_match('/(?:poste de|candidature[^\n]{0,35}pour|candidature[^\n]{0,20}[–-])\s*([^\r\n.,;]+)/iu',$content,$matches)){
   $mentioned=trim($matches[1]);
   if(mb_strlen($mentioned)>5 && mb_stripos((string)$offer['title'],$mentioned)===false && mb_stripos($mentioned,(string)$offer['title'])===false){$mismatchWarning='Le poste mentionné dans l’email (« '.mb_substr($mentioned,0,100).' ») semble différent de celui du dossier (« '.$offer['title'].' »). Vérifiez la candidature avant tout enregistrement.';}
  }
 }

 if($mode==='analyze')$schema='Analyse cet email de recruteur en français. Retourne UNIQUEMENT un objet JSON avec les clés: summary (chaîne), intent (chaîne), proposed_status (UNE valeur parmi to_review,to_apply,applied,waiting,interview,proposal,rejected,accepted,unchanged), confidence (nombre 0 à 100), reason (chaîne), next_action (chaîne), sender (chaîne), date_mentioned (chaîne). Ne déduis pas un refus ou entretien sans preuve explicite. Un message demandant des disponibilités pour un entretien explicite implique interview, même sans date fixée. Une simple prise de contact sans entretien explicite implique waiting ou unchanged. Aucun changement automatique.';
 elseif($mode==='reply')$schema='MISSION : ÉCRIRE LA RÉPONSE DU CANDIDAT À L’EMAIL FOURNI, et non reformuler ou recopier l’email reçu. Commence par Bonjour, remercie le recruteur pour son invitation et exprime l’intérêt du candidat. Tu es le conseiller du CANDIDAT, jamais le recruteur. Rédige un email que le candidat envoie EN SON NOM à la personne qui lui a écrit. Écris à la première personne du singulier (je, mon, ma), jamais nous pour parler du candidat. Le recruteur est le destinataire et non le locuteur. Retourne UNIQUEMENT un JSON avec subject et body (chaînes). Ne propose AUCUN créneau daté ou horaire non fourni par le candidat : demande ses disponibilités ou mets [créneaux à compléter]. N’invente aucune qualification, réussite ou expérience. Ne prétends pas connaître l’employeur final si le contact est un cabinet. Ton naturel, bref, professionnel.';
 else $schema='Tu coaches exclusivement le CANDIDAT pour son entretien. Le candidat est la personne interrogée, jamais le recruteur. Le recruteur ou cabinet pose des questions ; le candidat y répond. Retourne UNIQUEMENT un JSON avec summary (chaîne), pitch (texte à la première personne je, 60 secondes, uniquement faits vérifiés), questions (tableau de 5 questions probables du recruteur), suggested_answers (tableau de 5 pistes de réponses dans le même ordre, sans inventer de faits), talking_points (tableau de 3 réalisations vérifiées ou points à documenter), questions_to_ask (tableau de 3 questions que le candidat peut poser AU recruteur, jamais l’inverse), watchouts (tableau de 2 points de vigilance). Si aucune expérience vérifiée n’est fournie, dis explicitement à compléter et ne prétends pas personnaliser. Ne confonds pas cabinet de recrutement et employeur final. Français professionnel, naturel, sans faute.';
 if($mode==='reply')$schema.=' Si l’email demande des disponibilités et que le candidat ne les a pas fournies, écris exactement une phrase du type : « Je vous transmettrai rapidement mes disponibilités pour la semaine prochaine. » Ne copie aucune phrase entière de l’email reçu. Le body est uniquement le message sortant, sans objet.';
 if($mode==='interview')$schema.=' Réponds de façon concise : pitch de 80 mots maximum, réponses de 30 mots maximum chacune, listes courtes. Chaque clé JSON doit être présente.';
 $payload=['model'=>'llama3.2:3b','stream'=>false,'format'=>'json','options'=>['temperature'=>0.15,'num_predict'=>($mode==='interview'?1800:900)],'prompt'=>$schema."\n\n".$context."\n\nMessage ou consigne:\n".$content];
 $ch=curl_init('http://127.0.0.1:11434/api/generate');curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_UNICODE),CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>4,CURLOPT_TIMEOUT=>210]);$raw=curl_exec($ch);$err=curl_error($ch);$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
 if($raw===false||$http<200||$http>=300)throw new RuntimeException('Service IA local indisponible : '.($err?:'HTTP '.$http));
 $envelope=json_decode($raw,true);
 if(!is_array($envelope))throw new RuntimeException('Réponse du service IA invalide ou incomplète. Réessayez.');
 if(!empty($envelope['error']))throw new RuntimeException('Service IA : '.mb_substr((string)$envelope['error'],0,250));
 $modelText=(string)($envelope['response']??'');
 $result=json_decode($modelText,true);
 if(!is_array($result))throw new RuntimeException('Le modèle IA n’a pas produit un JSON complet. Réessayez avec une consigne plus courte.');
 if($mode==='reply'){
  $body=(string)($result['body']??'');
  // Le modèle 3B peut recopier le courrier reçu malgré le prompt : bloquer ce résultat.
  $received=mb_strtolower($content);$outgoing=mb_strtolower($body);
  $echo=(mb_strlen($body)<35 || (mb_stripos($outgoing,'nous avons étudié votre candidature')!==false) || (mb_stripos($outgoing,'pouvez-vous nous transmettre vos disponibilités')!==false));
  if($echo){
   $result=['subject'=>'Re: Votre candidature – '.(string)$offer['title'], 'body'=>"Bonjour,\n\nJe vous remercie pour votre message et pour votre proposition de premier entretien. Je suis intéressé par cet échange en visioconférence.\n\nJe vous transmettrai rapidement mes disponibilités pour la semaine prochaine.\n\nBien cordialement,"];
   $result['quality_notice']='Le modèle avait reproduit le message reçu : une réponse prudente a été proposée à la place. À personnaliser avant envoi.';
  }
 }

 if($mode==='analyze'){
  $allowed=['to_review','to_apply','applied','waiting','interview','proposal','rejected','accepted','unchanged'];
  if(!in_array((string)($result['proposed_status']??''),$allowed,true))$result['proposed_status']='unchanged';
  $result['confidence']=max(0,min(100,(int)($result['confidence']??0)));
  $explicitInterview=(bool)preg_match('/(?:entretien|interview|visioconf[eé]rence)[^.!?\n]{0,140}(?:disponibilit[eé]s|rencontrer|invitation|semaine prochaine)|(?:souhaitons vous rencontrer|premier entretien|invitation à un entretien)/iu',$content);
  if($explicitInterview && !preg_match('/(?:annul[eé]|report[eé]|refus[eé])/iu',$content)){$result['proposed_status']='interview';$result['reason']=trim((string)($result['reason']??'').' Invitation explicite à un entretien détectée dans le message.');}
  elseif($result['confidence']<75)$result['proposed_status']='unchanged';
 }
 if($mismatchWarning!=='')$result['context_warning']=$mismatchWarning;
 ob_end_clean();echo json_encode(['ok'=>true,'data'=>$result],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
}catch(Throwable $e){error_log('[OSJOB application-assistant] '.$e->getMessage());while(ob_get_level())ob_end_clean();http_response_code(400);echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);}
