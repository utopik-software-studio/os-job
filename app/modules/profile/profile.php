<?php
declare(strict_types=1);
const PROFILE_SECTIONS = [
 'identity'=>['title'=>'Informations personnelles','table'=>'profiles'],
 'experience'=>['title'=>'Expériences','table'=>'experiences'],
 'education'=>['title'=>'Formations','table'=>'profile_education'],
 'skills'=>['title'=>'Compétences','table'=>'profile_skills'],
 'languages'=>['title'=>'Langues','table'=>'profile_languages'],
 'targets'=>['title'=>'Positionnements','table'=>'career_targets'],
 'preferences'=>['title'=>'Préférences','table'=>'profiles'],
];
const PROFILE_FIELDS = [
 'identity'=>['first_name'=>'Prénom','last_name'=>'Nom','headline'=>'Titre professionnel','email'=>'Email','phone'=>'Téléphone','city'=>'Ville','country'=>'Pays','linkedin_url'=>'LinkedIn (URL)','website_url'=>'Site web (URL)','summary'=>'Présentation professionnelle'],
 'preferences'=>['salary_min'=>'Salaire brut annuel minimum (€)','preferred_locations'=>'Zones recherchées','mobility'=>'Mobilité','work_mode'=>'Mode de travail','availability'=>'Disponibilité'],
 'experience'=>['title'=>'Poste occupé','organization'=>'Entreprise','location'=>'Lieu','start_date'=>'Début (AAAA-MM)','end_date'=>'Fin (AAAA-MM)','description'=>'Missions et résultats vérifiables'],
 'education'=>['title'=>'Diplôme / formation','organization'=>'Établissement','start_date'=>'Début (AAAA-MM)','end_date'=>'Fin (AAAA-MM)','description'=>'Détails'],
 'skills'=>['name'=>'Compétence','level'=>'Niveau','description'=>'Précisions'],
 'languages'=>['name'=>'Langue','level'=>'Niveau','description'=>'Précisions'],
 'targets'=>['title'=>'Poste ciblé','level'=>'Niveau de responsabilité','description'=>'Positionnement / arguments'],
];
function profile_record(): array {
 $stmt=db()->prepare('SELECT p.* FROM profiles p JOIN users u ON u.id=p.user_id WHERE u.email=? LIMIT 1');
 $stmt->execute(['local@os-job.invalid']);
 $row=$stmt->fetch();
 if (!$row) throw new RuntimeException('Profil local introuvable. Importez les migrations 001 puis 002.');
 return $row;
}
function profile_rows(string $section,int $profileId): array {
 if (!isset(PROFILE_SECTIONS[$section]) || in_array($section,['identity','preferences'],true)) return [];
 $table=PROFILE_SECTIONS[$section]['table'];
 $stmt=db()->prepare("SELECT * FROM `$table` WHERE profile_id=? ORDER BY id DESC");
 $stmt->execute([$profileId]);return $stmt->fetchAll();
}
function profile_save(string $section,array $data,int $profileId,?int $id=null): void {
 if (!isset(PROFILE_FIELDS[$section])) throw new InvalidArgumentException('Section inconnue.');
 $fields=array_keys(PROFILE_FIELDS[$section]);
 $clean=[];
 foreach ($fields as $field) {
   $v=trim((string)($data[$field]??''));
   if (mb_strlen($v)>($field==='description'||$field==='summary'||$field==='preferred_locations'?6000:($field==='linkedin_url'||$field==='website_url'?500:190))) throw new InvalidArgumentException('Champ trop long : '.$field);
   if ($field==='email' && $v!=='' && !filter_var($v,FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Adresse email incorrecte.');
   if (in_array($field,['linkedin_url','website_url'],true) && $v!=='' && (!filter_var($v,FILTER_VALIDATE_URL)||!in_array(strtolower((string)parse_url($v,PHP_URL_SCHEME)),['http','https'],true))) throw new InvalidArgumentException('URL incorrecte.');
   if (in_array($field,['start_date','end_date'],true) && $v!=='' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$v)) throw new InvalidArgumentException('Date attendue : AAAA-MM.');
   if ($field==='salary_min') { if ($v==='' || !ctype_digit($v) || (int)$v>10000000) throw new InvalidArgumentException('Salaire annuel invalide.'); $v=(int)$v; }
   $clean[$field]=$v;
 }
 if($section==='skills'){ $cat=db()->prepare('INSERT IGNORE INTO skill_catalog (name) VALUES (?)');$cat->execute([$clean['name']]); }
 $table=PROFILE_SECTIONS[$section]['table'];
 if ($table==='profiles') {
   $set=implode(',',array_map(fn($f)=>"`$f`=?",$fields));
   $stmt=db()->prepare("UPDATE profiles SET $set WHERE id=?");
   $stmt->execute([...array_values($clean),$profileId]);return;
 }
 if (trim((string)($clean['title']??$clean['name']??''))==='') throw new InvalidArgumentException('Le premier champ est obligatoire.');
 if ($id!==null) {
   $set=implode(',',array_map(fn($f)=>"`$f`=?",$fields));
   $stmt=db()->prepare("UPDATE `$table` SET $set WHERE id=? AND profile_id=?");
   $stmt->execute([...array_values($clean),$id,$profileId]);
   if (!$stmt->rowCount()) { /* Une sauvegarde identique peut ne modifier aucune ligne. */ }
 } else {
   $columns=implode(',',array_map(fn($f)=>"`$f`",$fields));
   $placeholders=implode(',',array_fill(0,count($fields)+1,'?'));
   $stmt=db()->prepare("INSERT INTO `$table` (profile_id,$columns) VALUES ($placeholders)");
   $stmt->execute([$profileId,...array_values($clean)]);
 }
}
function profile_delete(string $section,int $profileId,int $id): void {
 if (!isset(PROFILE_SECTIONS[$section]) || in_array($section,['identity','preferences'],true)) throw new InvalidArgumentException('Section non supprimable.');
 $table=PROFILE_SECTIONS[$section]['table'];
 $stmt=db()->prepare("DELETE FROM `$table` WHERE id=? AND profile_id=?");$stmt->execute([$id,$profileId]);
}
function profile_completion(array $p,array $counts): int {
 $checks=['first_name','last_name','headline','email','phone','city','country','summary','preferred_locations','availability'];
 $filled=0;foreach($checks as $key) if (trim((string)($p[$key]??''))!=='') $filled++;
 foreach(['experience','education','skills','languages','targets'] as $key) if (($counts[$key]??0)>0) $filled++;
 return (int)round(100*$filled/15);
}
