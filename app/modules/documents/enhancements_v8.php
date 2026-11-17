<?php
declare(strict_types=1);
function docs_background(int $docId):?array {
 $q=db()->prepare('SELECT * FROM document_backgrounds WHERE document_id=?');$q->execute([$docId]);return $q->fetch()?:null;
}
function docs_background_upload(int $uid,int $id,array $file):void {
 $d=docs_owned($uid,$id);if($d['document_type']!=='cv')throw new InvalidArgumentException('Les images de fond sont réservées aux CV.');
 if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new InvalidArgumentException('Téléversement impossible.');
 if((int)($file['size']??0)<1||(int)$file['size']>4*1024*1024)throw new InvalidArgumentException('Image limitée à 4 Mo.');
 $tmp=$file['tmp_name']??'';if(!is_uploaded_file($tmp))throw new InvalidArgumentException('Fichier non téléversé.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);$allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
 if(!isset($allowed[$mime])||@getimagesize($tmp)===false)throw new InvalidArgumentException('Image JPG, PNG ou WebP requise.');
 $folder=APP_ROOT.'/storage/document-backgrounds';if(!is_dir($folder)&&!mkdir($folder,0750,true))throw new RuntimeException('Dossier image indisponible.');
 $name=bin2hex(random_bytes(20)).'.'.$allowed[$mime];$path=$folder.'/'.$name;
 if(!move_uploaded_file($tmp,$path))throw new RuntimeException('Impossible de stocker le fichier.');
 $old=docs_background($id);
 try{$s=db()->prepare('INSERT INTO document_backgrounds(document_id,original_name,stored_name,mime_type) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE original_name=VALUES(original_name),stored_name=VALUES(stored_name),mime_type=VALUES(mime_type)');$s->execute([$id,mb_substr(basename((string)($file['name']??'image')),0,255),$name,$mime]);}
 catch(Throwable $e){@unlink($path);throw $e;}
 if($old)@unlink($folder.'/'.basename($old['stored_name']));
}
function docs_background_remove(int $uid,int $id):void {
 docs_owned($uid,$id);$old=docs_background($id);$s=db()->prepare('DELETE FROM document_backgrounds WHERE document_id=?');$s->execute([$id]);
 if($old)@unlink(APP_ROOT.'/storage/document-backgrounds/'.basename($old['stored_name']));
}
function docs_style_v8(array $raw,string $key):array {
 $out=docs_style_sanitize($raw,$key);
 $mode=(string)($raw['background_mode']??'none');$out['background_mode']=in_array($mode,['none','full','header','sidebar'],true)?$mode:'none';
 $out['background_opacity']=max(0,min(75,(int)($raw['background_opacity']??15)));
 $out['export_mode']=in_array(($raw['export_mode']??'premium'),['premium','ats'],true)?$raw['export_mode']:'premium';
 if(docs_template_type($key)!=='cv'){$out['background_mode']='none';$out['export_mode']='premium';}
 return $out;
}
function docs_analysis_keywords(string $text):array {
 $dictionary=['gestion de projet','project management','product owner','product manager','scrum','agile','kanban','jira','notion','make','automatisation','intelligence artificielle','ia','crm','erp','sql','mysql','php','javascript','html','css','seo','marketing digital','stratégie marketing','leadership','management','budget','transformation digitale','architecture si','cybersécurité','cloud','api','anglais','espagnol','français','communication','ux','data','reporting','kpi','roadmap','stakeholders','excel','power bi','salesforce','sap','wordpress'];
 $text=mb_strtolower($text,'UTF-8');$found=[];
 foreach($dictionary as $term){$pattern='/(?<![\pL\pN])'.preg_quote($term,'/').'(?![\pL\pN])/iu';if(preg_match($pattern,$text))$found[]=$term;}
 return $found;
}
function docs_analyse_offer(int $uid,int $docId):array {
 $d=docs_owned($uid,$docId);$offerId=(int)($d['job_offer_id']??0);if(!$offerId)throw new InvalidArgumentException('Associez une offre à ce document avant analyse.');
 $q=db()->prepare('SELECT title,company,description FROM job_offers WHERE id=? AND user_id=?');$q->execute([$offerId,$uid]);$offer=$q->fetch();if(!$offer)throw new InvalidArgumentException('Offre introuvable.');
 $terms=docs_analysis_keywords(($offer['title']??'').' '.($offer['description']??''));$profile=docs_profile($uid);$pid=(int)($profile['id']??0);
 $skills=$pid?docs_lines($pid,'profile_skills'):[];$experiences=$pid?docs_lines($pid,'experiences'):[];
 $known=mb_strtolower(($profile['headline']??'').' '.($profile['summary']??'').' '.implode(' ',array_map(fn($s)=>$s['name']??'',$skills)).' '.implode(' ',array_map(fn($x)=>($x['title']??'').' '.($x['description']??''),$experiences)),'UTF-8');
 $matched=[];$unverified=[];foreach($terms as $term){if(preg_match('/(?<![\pL\pN])'.preg_quote($term,'/').'(?![\pL\pN])/iu',$known))$matched[]=$term;else $unverified[]=$term;}
 $result=['offer_title'=>$offer['title'],'company'=>$offer['company'],'keywords'=>$terms,'matched'=>$matched,'unverified'=>$unverified,'coverage'=>count($terms)?round(100*count($matched)/count($terms)):null,'notice'=>'Correspondances lexicales indicatives. Non détecté ne signifie pas compétence absente. Vérifiez et adaptez manuellement le CV.','engine'=>'local_keywords_v1','ai_status'=>'Adaptateur IA facultatif prévu, aucune API appelée.'];
 $s=db()->prepare('INSERT INTO document_offer_analyses(document_id,offer_id,analysis_json) VALUES(?,?,?) ON DUPLICATE KEY UPDATE offer_id=VALUES(offer_id),analysis_json=VALUES(analysis_json)');$s->execute([$docId,$offerId,json_encode($result,JSON_UNESCAPED_UNICODE)]);
 return $result;
}
function docs_saved_analysis(int $uid,int $id):?array {
 $d=docs_owned($uid,$id);$s=db()->prepare('SELECT analysis_json,offer_id FROM document_offer_analyses WHERE document_id=?');$s->execute([$id]);$row=$s->fetch();if(!$row||(int)$row['offer_id']!==(int)$d['job_offer_id'])return null;$v=json_decode($row['analysis_json'],true);return is_array($v)?$v:null;
}
