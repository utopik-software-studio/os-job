<?php
$importError=null;$importSuccess=null;$importRecent=[];$importSpaces=[];
try {
    $uid=local_user_id();
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        if (!hash_equals((string)($_SESSION['csrf']??''),(string)($_POST['csrf']??''))) throw new InvalidArgumentException('Session expirée : actualisez la page.');
        if (($_POST['action']??'')!=='import_manual') throw new InvalidArgumentException('Action inconnue.');
        if (($_POST['confirm']??'')!=='1') throw new InvalidArgumentException('Veuillez confirmer l’enregistrement après vérification.');
        $id=os_import_manual($uid,$_POST);
        header('Location: '.url('import').'&created='.$id,true,303);exit;
    }
    $importRecent=os_import_recent($uid);
    $importSpaces=search_spaces_all($uid);
    $created=filter_var($_GET['created']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
    if ($created) {
        $check=db()->prepare('SELECT title FROM job_offers WHERE id=? AND user_id=?');$check->execute([$created,$uid]);
        if ($check->fetchColumn()) $importSuccess=$created;
    }
} catch(Throwable $e) { $importError=$e->getMessage(); }
