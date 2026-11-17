<?php
declare(strict_types=1);
function cv_owner():int {return docs_owner();}
function cv_default():array{return ['name'=>'','headline'=>'','contact'=>'','summary'=>'','experiences'=>[],'achievements'=>[],'skills'=>[],'education'=>[],'languages'=>[]];}
function cv_profile_seed(int $uid):array {
 $p=docs_profile($uid);$pid=(int)($p['id']??0);$a=cv_default();
 $a['name']=trim(($p['first_name']??'').' '.($p['last_name']??''));$a['headline']=(string)($p['headline']??'');
 $a['contact']=implode(' | ',array_filter([(string)($p['city']??''),(string)($p['phone']??''),(string)($p['email']??''),(string)($p['linkedin_url']??'')]));
 $summary=(string)($p['summary']??'');$paragraphs=preg_split('/\R\s*\R/u',trim($summary));$a['summary']=implode("\n\n",array_slice($paragraphs?:[],0,2));
 foreach(docs_lines($pid,'experiences') as $r)$a['experiences'][]=['title'=>$r['title'],'organization'=>$r['organization'],'period'=>trim($r['start_date'].' – '.($r['end_date']?:'Aujourd’hui')),'description'=>(string)$r['description']];
 usort($a['experiences'],static fn($x,$y)=>strcmp($y['period'],$x['period']));$a['experiences']=array_slice($a['experiences'],0,6);foreach($a['experiences'] as &$exp){$parts=preg_split('/\R/u',trim($exp['description']));$bullets=array_values(array_filter($parts?:[],static fn($line)=>preg_match('/^\s*[-•]/u',$line)));$exp['description']=implode("\n",array_slice($bullets,0,3));}unset($exp);
 foreach(docs_lines($pid,'profile_education') as $r)$a['education'][]=['title'=>$r['title'],'organization'=>$r['organization'],'period'=>trim($r['start_date'].' – '.$r['end_date']),'description'=>(string)$r['description']];
 foreach(docs_lines($pid,'profile_skills') as $r)$a['skills'][]=trim($r['name'].($r['level']?' — '.$r['level']:''));
 $a['skills']=array_slice($a['skills'],0,12);
 foreach(docs_lines($pid,'profile_languages') as $r)$a['languages'][]=trim($r['name'].' — '.$r['level']);
 $s=db()->prepare('SELECT title,organization,actions_text,results_text FROM career_achievements WHERE user_id=? ORDER BY id DESC');$s->execute([$uid]);
 foreach($s->fetchAll() as $r)$a['achievements'][]=['title'=>$r['title'],'organization'=>$r['organization'],'period'=>'','description'=>trim($r['actions_text'].($r['results_text']?"\nRésultat : ".$r['results_text']:''))];
 return $a;
}
function cv_clean(array $raw):array {
 $out=cv_default();foreach(['name','headline','contact','summary'] as $key)$out[$key]=mb_substr(trim((string)($raw[$key]??'')),0,$key==='summary'?4000:700);
 foreach(['experiences','achievements','education'] as $key){$items=$raw[$key]??[];if(!is_array($items))continue;foreach(array_slice($items,0,35) as $item){if(!is_array($item))continue;$row=[];foreach(['title','organization','period','description'] as $field)$row[$field]=mb_substr(trim((string)($item[$field]??'')),0,$field==='description'?2500:350);if(implode('',$row)!=='')$out[$key][]=$row;}}
 foreach(['skills','languages'] as $key){$items=$raw[$key]??[];if(!is_array($items))continue;foreach(array_slice($items,0,80) as $item){$val=mb_substr(trim((string)$item),0,250);if($val!=='')$out[$key][]=$val;}}
 return $out;
}
function cv_owned(int $uid,int $id):array {$s=db()->prepare('SELECT * FROM cv_studio_documents WHERE id=? AND user_id=?');$s->execute([$id,$uid]);$d=$s->fetch();if(!$d)throw new InvalidArgumentException('CV introuvable.');$d['content']=cv_clean((array)(json_decode((string)$d['content_json'],true)?:[]));return $d;}
function cv_list(int $uid):array {$s=db()->prepare('SELECT id,title,template_key,updated_at FROM cv_studio_documents WHERE user_id=? ORDER BY updated_at DESC,id DESC');$s->execute([$uid]);return $s->fetchAll();}
function cv_save(int $uid,array $post):int {
 $id=(int)($post['id']??0);$title=mb_substr(trim((string)($post['title']??'')),0,220);if($title==='')throw new InvalidArgumentException('Indiquez un nom de document.');
 $template=(string)($post['template_key']??'executive');if(!in_array($template,['executive','ats'],true))throw new InvalidArgumentException('Modèle invalide.');
 $raw=json_decode((string)($post['content_json']??''),true);if(!is_array($raw))throw new InvalidArgumentException('Contenu CV invalide.');$data=cv_clean($raw);$json=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
 $offerId=(int)($post['job_offer_id']??0);if($offerId){$s=db()->prepare('SELECT id FROM job_offers WHERE id=? AND user_id=?');$s->execute([$offerId,$uid]);if(!$s->fetchColumn())throw new InvalidArgumentException('Offre non autorisée.');}
 $photo=isset($post['include_photo'])?1:0;
 if($id){cv_owned($uid,$id);$s=db()->prepare('UPDATE cv_studio_documents SET title=?,template_key=?,content_json=?,include_photo=?,job_offer_id=? WHERE id=? AND user_id=?');$s->execute([$title,$template,$json,$photo,$offerId?:null,$id,$uid]);return $id;}
 $s=db()->prepare('INSERT INTO cv_studio_documents(user_id,job_offer_id,title,template_key,content_json,include_photo) VALUES (?,?,?,?,?,?)');$s->execute([$uid,$offerId?:null,$title,$template,$json,$photo]);return (int)db()->lastInsertId();
}
