<?php
declare(strict_types=1);
function docs_owner(): int { $s=db()->prepare('SELECT id FROM users WHERE email=? LIMIT 1');$s->execute(['local@os-job.invalid']);return (int)$s->fetchColumn(); }
function docs_profile(int $uid):array { $s=db()->prepare('SELECT * FROM profiles WHERE user_id=?');$s->execute([$uid]);return $s->fetch()?:[]; }
function docs_list(int $uid):array { $s=db()->prepare('SELECT d.*,o.title AS offer_title,o.company FROM generated_documents d LEFT JOIN job_offers o ON o.id=d.job_offer_id WHERE d.user_id=? ORDER BY d.updated_at DESC,d.id DESC');$s->execute([$uid]);return $s->fetchAll(); }
function docs_offers(int $uid):array { $s=db()->prepare('SELECT id,title,company,description FROM job_offers WHERE user_id=? ORDER BY updated_at DESC,id DESC');$s->execute([$uid]);return $s->fetchAll(); }
function docs_owned(int $uid,int $id):array { $s=db()->prepare('SELECT * FROM generated_documents WHERE id=? AND user_id=?');$s->execute([$id,$uid]);$d=$s->fetch();if(!$d)throw new InvalidArgumentException('Document introuvable.');return $d; }
function docs_lines(int $pid,string $table):array { if(!in_array($table,['experiences','profile_education','profile_skills','profile_languages'],true))throw new InvalidArgumentException('Table inconnue');$s=db()->prepare("SELECT * FROM `$table` WHERE profile_id=? ORDER BY id DESC");$s->execute([$pid]);return $s->fetchAll(); }
function docs_suggest(int $uid,string $type,int $offerId):string {
 $p=docs_profile($uid);$pid=(int)($p['id']??0);$offers=docs_offers($uid);$offer=null;foreach($offers as $o)if((int)$o['id']===$offerId)$offer=$o;
 $name=trim(($p['first_name']??'').' '.($p['last_name']??''));$headline=trim((string)($p['headline']??''));$email=(string)($p['email']??'');$phone=(string)($p['phone']??'');$city=(string)($p['city']??'');
 if($type==='letter') {
  $position=$offer['title']??'[intitulé du poste]';$company=$offer['company']??'[nom de l’entreprise]';
  return "Objet : Candidature — $position\n\nMadame, Monsieur,\n\nJe souhaite vous proposer ma candidature au poste de $position chez $company.\n\n".trim((string)($p['summary']??'[Présentez ici vos réalisations les plus pertinentes pour cette offre.]'))."\n\n[À personnaliser : expliquez le lien entre vos expériences vérifiables, les missions de l’offre et les besoins de l’entreprise.]\n\nJe serais heureux d’échanger avec vous sur cette opportunité.\n\nCordialement,\n$name\n$phone\n$email";
 }
 $out=[$name,$headline,trim($city.' | '.$phone.' | '.$email),trim((string)($p['linkedin_url']??''))];
 if(trim((string)($p['summary']??''))!==''){$out[]='';$out[]='PROFIL';$out[]=trim((string)$p['summary']);}
 $ex=docs_lines($pid,'experiences');if($ex){$out[]='';$out[]='EXPÉRIENCES PROFESSIONNELLES';foreach($ex as $r){$out[]=trim($r['title'].' — '.$r['organization']).' ('.$r['start_date'].' – '.($r['end_date']?:'Aujourd’hui').')';if(trim((string)$r['description'])!=='')$out[]=trim((string)$r['description']);}}
 $sk=docs_lines($pid,'profile_skills');if($sk){$out[]='';$out[]='COMPÉTENCES';$out[]=implode(' · ',array_map(fn($r)=>$r['name'], $sk));}
 $ed=docs_lines($pid,'profile_education');if($ed){$out[]='';$out[]='FORMATION';foreach($ed as $r)$out[]=trim($r['title'].' — '.$r['organization'].' ('.$r['start_date'].' – '.$r['end_date'].')');}
 $langs=docs_lines($pid,'profile_languages');if($langs){$out[]='';$out[]='LANGUES';foreach($langs as $r)$out[]=trim($r['name'].' : '.$r['level']);}
 return implode("\n",$out);
}
function docs_action(int $uid,array $post):int {
 $action=(string)($post['action']??'');$id=(int)($post['id']??0);
 if($action==='delete'){docs_owned($uid,$id);$guard=db()->prepare('SELECT COUNT(*) FROM application_document_dispatches WHERE generated_document_id=?');$guard->execute([$id]);if((int)$guard->fetchColumn()>0)throw new InvalidArgumentException('Document déjà envoyé : suppression interdite.');$guard=db()->prepare('SELECT COUNT(*) FROM application_document_links WHERE generated_document_id=?');$guard->execute([$id]);if((int)$guard->fetchColumn()>0)throw new InvalidArgumentException('Dissociez ce document de ses candidatures avant suppression.');$s=db()->prepare('DELETE FROM generated_documents WHERE id=? AND user_id=?');$s->execute([$id,$uid]);return 0;}
 if(!in_array($action,['create','save'],true))throw new InvalidArgumentException('Action inconnue.');
 $type=(string)($post['document_type']??'cv');if(!in_array($type,['cv','letter'],true))throw new InvalidArgumentException('Type invalide.');
 $template=(string)($post['template_key']??'classic');if(!in_array($template,['classic','modern'],true))throw new InvalidArgumentException('Modèle invalide.');
 $title=trim((string)($post['title']??''));if($title===''||mb_strlen($title)>220)throw new InvalidArgumentException('Titre obligatoire (220 caractères max).');
 $offerId=(int)($post['job_offer_id']??0);if($offerId){$s=db()->prepare('SELECT id FROM job_offers WHERE id=? AND user_id=?');$s->execute([$offerId,$uid]);if(!$s->fetchColumn())throw new InvalidArgumentException('Offre inconnue.');}
 $body=(string)($post['body']??'');if(mb_strlen($body)>60000)throw new InvalidArgumentException('Document trop long.');
 $photo=isset($post['include_photo'])?1:0;
 if($action==='create'&&$body==='')$body=docs_suggest($uid,$type,$offerId);
 if($action==='save'){$old=docs_owned($uid,$id);$s=db()->prepare('UPDATE generated_documents SET job_offer_id=?,document_type=?,template_key=?,title=?,body=?,include_photo=? WHERE id=? AND user_id=?');$s->execute([$offerId?:null,$type,$template,$title,$body,$photo,$id,$uid]);return $id;}
 $s=db()->prepare('INSERT INTO generated_documents (user_id,job_offer_id,document_type,template_key,title,body,include_photo) VALUES (?,?,?,?,?,?,?)');$s->execute([$uid,$offerId?:null,$type,$template,$title,$body,$photo]);return (int)db()->lastInsertId();
}


// V0.7 : paramètres de présentation indépendants du contenu, pour préserver les documents v0.6.
function docs_templates():array {return [
 'classic'=>['cv','Executive Classic'],'modern'=>['cv','Contemporary Pro'],
 'swiss'=>['cv','Swiss Minimal'],'creative'=>['cv','Creative Impact'],'results'=>['cv','Results First'],
 'letter_classic'=>['letter','Lettre classique'],'letter_modern'=>['letter','Lettre contemporaine']
];}
function docs_template_type(string $key):?string {return docs_templates()[$key][0]??null;}
function docs_default_style(string $key):array {
 $base=['accent'=>'#087f84','font'=>'Arial','density'=>'normal','layout'=>'single','show_summary'=>true,'show_skills'=>true,'show_education'=>true,'show_languages'=>true];
 $overrides=['classic'=>['accent'=>'#153b50','font'=>'Arial'],'modern'=>['accent'=>'#079e99','font'=>'Inter','layout'=>'sidebar'],
 'swiss'=>['accent'=>'#202b34','font'=>'Helvetica'],'creative'=>['accent'=>'#cf654a','font'=>'Manrope','layout'=>'sidebar'],
 'results'=>['accent'=>'#ae744c','font'=>'Georgia'],'letter_classic'=>['accent'=>'#153b50','font'=>'Georgia'],'letter_modern'=>['accent'=>'#087f84','font'=>'Arial']];
 return array_merge($base,$overrides[$key]??[]);
}
function docs_style_sanitize(array $a,string $key):array {
 $out=docs_default_style($key);$color=(string)($a['accent']??$out['accent']);if(preg_match('/^#[0-9a-fA-F]{6}$/D',$color))$out['accent']=$color;
 foreach(['font'=>['Arial','Helvetica','Georgia','Inter','Manrope','Aptos','IBM Plex Sans'],'density'=>['compact','normal','airy'],'layout'=>['single','sidebar']] as $k=>$values){if(in_array($a[$k]??null,$values,true))$out[$k]=$a[$k];}
 foreach(['show_summary','show_skills','show_education','show_languages'] as $k)$out[$k]=isset($a[$k])&&in_array((string)$a[$k],['1','on','true'],true);
 foreach(['background_mode'=>['none','full','header','sidebar'],'export_mode'=>['premium','ats']] as $k=>$values){if(in_array($a[$k]??null,$values,true))$out[$k]=$a[$k];}
 $out['background_opacity']=max(0,min(75,(int)($a['background_opacity']??15)));
 if(docs_template_type($key)==='letter'){$out['layout']='single';$out['background_mode']='none';}
 return $out;
}
function docs_presentation(int $id):array {
 $s=db()->prepare('SELECT settings_json FROM document_presentation WHERE document_id=?');$s->execute([$id]);$r=$s->fetchColumn();$a=$r?json_decode($r,true):[];return is_array($a)?$a:[];
}
function docs_set_presentation(int $id,array $style):void {
 $s=db()->prepare('INSERT INTO document_presentation (document_id,settings_json) VALUES (?,?) ON DUPLICATE KEY UPDATE settings_json=VALUES(settings_json)');$s->execute([$id,json_encode($style,JSON_UNESCAPED_UNICODE)]);
}
function docs_variants(int $uid):array {$s=db()->prepare('SELECT * FROM document_template_variants WHERE user_id=? ORDER BY id DESC');$s->execute([$uid]);return $s->fetchAll();}
function docs_variant(int $uid,int $id):array {$s=db()->prepare('SELECT * FROM document_template_variants WHERE user_id=? AND id=?');$s->execute([$uid,$id]);$r=$s->fetch();if(!$r)throw new InvalidArgumentException('Variante inconnue.');return $r;}
function docs_suggest_title(string $type,int $offerId,int $uid):string {
 $offer=null;foreach(docs_offers($uid) as $o){if((int)$o['id']===$offerId){$offer=$o;break;}}
 $job=trim((string)($offer['title']??docs_profile($uid)['headline']??'Professionnel'));
 $company=trim((string)($offer['company']??''));
 return ($type==='cv'?'CV':'Lettre de motivation').' — '.$job.($company!==''?' — '.$company:'');
}
function docs_action_v7(int $uid,array $post):int {
 $action=(string)($post['action']??'');$id=(int)($post['id']??0);
 if($action==='delete_variant'){$v=docs_variant($uid,$id);$s=db()->prepare('DELETE FROM document_template_variants WHERE user_id=? AND id=?');$s->execute([$uid,$id]);return 0;}
 if($action==='save_variant'){
  $key=(string)($post['template_key']??'classic');$type=docs_template_type($key);if(!$type)throw new InvalidArgumentException('Modèle inconnu.');
  $name=trim((string)($post['variant_name']??''));if($name===''||mb_strlen($name)>120)throw new InvalidArgumentException('Nom de variante requis (120 caractères max).');
  $style=docs_style_sanitize((array)($post['style']??[]),$key);
  $s=db()->prepare('INSERT INTO document_template_variants (user_id,document_type,base_key,name,settings_json) VALUES (?,?,?,?,?)');$s->execute([$uid,$type,$key,$name,json_encode($style,JSON_UNESCAPED_UNICODE)]);return 0;
 }
 if(!in_array($action,['create','save','delete'],true))throw new InvalidArgumentException('Action inconnue.');
 if($action==='delete')return docs_action($uid,$post);
 $type=(string)($post['document_type']??'cv');$key=(string)($post['template_key']??'classic');
 if(docs_template_type($key)!==$type)throw new InvalidArgumentException('Le modèle sélectionné ne correspond pas au type de document.');
 $variantId=(int)($post['variant_id']??0);$style=docs_style_sanitize((array)($post['style']??[]),$key);
 if($variantId){$v=docs_variant($uid,$variantId);if($v['base_key']!==$key)throw new InvalidArgumentException('Variante incompatible.');$style=docs_style_sanitize(json_decode($v['settings_json'],true)?:[],$key);}
 $post['include_photo']=$type==='cv'&&isset($post['include_photo'])?'1':null;
 $post['title']=trim((string)($post['title']??''));if($post['title']==='')$post['title']=docs_suggest_title($type,(int)($post['job_offer_id']??0),$uid);
 // Valider les champs métier via le contrôleur v0.6, puis sauvegarder la présentation.
 // docs_action v0.6 connaît les deux anciens modèles ; utiliser temporairement classic pour le passage SQL.
 $savedKey=$key;$post['template_key']=in_array($key,['classic','modern'],true)?$key:'classic';
 $id=docs_action($uid,$post);
 if($savedKey!==$post['template_key']){$s=db()->prepare('UPDATE generated_documents SET template_key=? WHERE id=? AND user_id=?');$s->execute([$savedKey,$id,$uid]);}
 docs_set_presentation($id,$style);return $id;
}
