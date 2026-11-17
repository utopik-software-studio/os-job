<?php
declare(strict_types=1);
$section=(string)($_GET['section']??'identity');
if (!isset(PROFILE_SECTIONS[$section])) $section='identity';
$profileError=null;$profile=null;$rows=[];$counts=[];$editing=null;
try {
 $profile=profile_record();
 if ($_SERVER['REQUEST_METHOD']==='POST') {
   if (!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??''))) throw new InvalidArgumentException('Session expirée : rechargez la page.');
   $action=(string)($_POST['action']??'save');
   $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: null;
   if ($action==='photo') { if(!isset($_FILES['photo']))throw new InvalidArgumentException('Choisissez une photo.');photo_upload((int)$profile['id'],$_FILES['photo']);}
   elseif ($action==='geo') {geo_save((int)$profile['id'],$_POST['locations']??[]);score_refresh_all(local_user_id());}
   elseif ($action==='delete') {if ($id===null) throw new InvalidArgumentException('Identifiant manquant.');profile_delete($section,(int)$profile['id'],$id);score_refresh_all(local_user_id());}
   elseif ($action==='save') {profile_save($section,$_POST,(int)$profile['id'],$id);score_refresh_all(local_user_id());}
   else throw new InvalidArgumentException('Action inconnue.');
   header('Location: '.url('profile').'&section='.rawurlencode($section).'&saved=1',true,303);exit;
 }
 foreach(PROFILE_SECTIONS as $key=>$meta) {
   if ($meta['table']!=='profiles') $counts[$key]=count(profile_rows($key,(int)$profile['id']));
 }
 $rows=profile_rows($section,(int)$profile['id']);
 $geoLocations=geo_all();$selectedGeo=geo_selected((int)$profile['id']);$skillCatalog=db()->query('SELECT name FROM skill_catalog ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
 $editId=filter_var($_GET['edit']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: null;
 if ($editId!==null) foreach($rows as $r) if ((int)$r['id']===$editId) $editing=$r;
} catch(Throwable $ex) {
 $profileError=$ex instanceof PDOException ? 'Connexion MySQL impossible. Vérifiez que MySQL est démarré, que la base et les tables sont importées et que database.php est correct.' : $ex->getMessage();
}
