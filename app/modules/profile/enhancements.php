<?php
function geo_all():array {return db()->query('SELECT id,parent_id,kind,name FROM geo_locations ORDER BY id')->fetchAll();}
function geo_selected(int $pid):array {$s=db()->prepare('SELECT location_id FROM profile_locations WHERE profile_id=?');$s->execute([$pid]);return array_map('intval',$s->fetchAll(PDO::FETCH_COLUMN));}
function geo_save(int $pid,array $ids):void {
 $ids=array_values(array_unique(array_filter(array_map('intval',$ids),fn($n)=>$n>0)));if(count($ids)>100)throw new InvalidArgumentException('100 zones maximum.');
 $db=db();$db->beginTransaction();try{$s=$db->prepare('DELETE FROM profile_locations WHERE profile_id=?');$s->execute([$pid]);$check=$db->prepare('SELECT id FROM geo_locations WHERE id=?');$add=$db->prepare('INSERT INTO profile_locations(profile_id,location_id) VALUES (?,?)');foreach($ids as $id){$check->execute([$id]);if(!$check->fetchColumn())throw new InvalidArgumentException('Zone inconnue.');$add->execute([$pid,$id]);}$db->commit();}catch(Throwable $e){$db->rollBack();throw $e;}
}
function photo_upload(int $pid,array $file):void {
 if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new InvalidArgumentException('Échec du transfert de la photo.');
 if(($file['size']??0)>3*1024*1024)throw new InvalidArgumentException('Photo de 3 Mo maximum.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
 if(!$ext||!@getimagesize($file['tmp_name']))throw new InvalidArgumentException('Format accepté : JPG, PNG ou WebP.');
 $folder=APP_ROOT.'/storage/profile-photos';if(!is_dir($folder)&&!mkdir($folder,0700,true))throw new RuntimeException('Impossible de créer le dossier photo.');
 $name='profile-'.$pid.'-'.bin2hex(random_bytes(10)).'.'.$ext;
 if(!move_uploaded_file($file['tmp_name'],$folder.'/'.$name))throw new RuntimeException('Impossible d’enregistrer la photo.');
 $s=db()->prepare('SELECT photo_path FROM profiles WHERE id=?');$s->execute([$pid]);$old=$s->fetchColumn();
 try{$s=db()->prepare('UPDATE profiles SET photo_path=? WHERE id=?');$s->execute([$name,$pid]);}catch(Throwable $e){@unlink($folder.'/'.$name);throw $e;}
 if($old&&is_file($folder.'/'.basename((string)$old)))@unlink($folder.'/'.basename((string)$old));
}
