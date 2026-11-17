<?php
declare(strict_types=1);
/** v0.13.4 — exports locaux, périmètre utilisateur, aucune écriture SQL. */
function reports_period(string $p): string { return in_array($p,['30','90','180','365','all'],true)?$p:'90'; }
function reports_label(string $p): string {return ['30'=>'30 jours','90'=>'90 jours','180'=>'6 mois','365'=>'12 mois','all'=>'Tout l’historique'][reports_period($p)];}
function reports_rows(int $uid,string $period): array {
 $period=reports_period($period);$days=['30'=>30,'90'=>90,'180'=>180,'365'=>365,'all'=>null][$period];
 $from=$days===null?null:(new DateTimeImmutable('today'))->modify('-'.($days-1).' days')->format('Y-m-d 00:00:00');
 $sql='SELECT a.id,a.applied_at,a.status,a.application_platform,o.title,o.company,o.city FROM applications a JOIN job_offers o ON o.id=a.job_offer_id AND o.user_id=a.user_id WHERE a.user_id=? AND a.applied_at IS NOT NULL';
 $args=[$uid];if($from!==null){$sql.=' AND a.applied_at>=?';$args[]=$from;}
 $sql.=' ORDER BY a.applied_at DESC,a.id DESC LIMIT 5000';
 $stmt=db()->prepare($sql);$stmt->execute($args);return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function reports_safe_cell($value):string {
 $s=trim((string)($value??''));$s=str_replace(["\r","\n","\t"],' ',$s);
 $s=preg_replace('/^[\x{FEFF}\x{200B}\x{200C}\x{200D}]+/u','',$s)??$s;
 // Excel formula injection: escape leading formula operators, even after whitespace.
 if(preg_match('/^[\s\x00-\x1F]*[=+@\-]/u',$s))$s="'".$s;
 return $s;
}
function reports_csv(array $rows,string $period):void {
 $name='osjob_candidatures_'.reports_period($period).'_'.date('Ymd').'.csv';
 header('Content-Type: text/csv; charset=UTF-8');header('Content-Disposition: attachment; filename="'.$name.'"');header('X-Content-Type-Options: nosniff');header('Cache-Control: no-store');
 $out=fopen('php://output','wb');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,['Date d’envoi','Poste','Entreprise','Ville','Statut','Plateforme'],';', '"','');
 foreach($rows as $r)fputcsv($out,array_map('reports_safe_cell',[$r['applied_at'],$r['title'],$r['company'],$r['city'],$r['status'],$r['application_platform']]),';','"','');
 fclose($out);
}
/** Minimal dependency-free PDF/A4 generator; text is transliterated to WinAnsi for Helvetica. */
function reports_pdf_text(string $s):string {
 $s=preg_replace('/\s+/u',' ',trim($s))??trim($s);
 $v=@iconv('UTF-8','Windows-1252//TRANSLIT//IGNORE',$s);if($v===false)$v=preg_replace('/[^\x20-\x7E]/','?', $s);
 return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$v);
}
function reports_pdf(array $stats,string $period):string {
 $lines=[['OS::JOB | Rapport d activite',17],['Periode : '.reports_label($period).'  |  Genere le '.date('d/m/Y H:i'),10],['',10],['SYNTHESE DES CANDIDATURES',13],['Candidatures envoyees : '.$stats['sent'],11],['Taux de reponse indicatif : '.analytics_pct($stats['responded'],$stats['sent']),11],['Acces a l entretien : '.analytics_pct($stats['interviewed'],$stats['sent']),11],['Issues positives : '.analytics_pct($stats['positive'],$stats['sent']),11],['',10],['REPARTITION PAR STATUT',13]];
 foreach($stats['statuses'] as $k=>$v)$lines[]=[(app_statuses()[$k]??$k).' : '.$v,10];
 $lines[]=['',10];$lines[]=['PLATEFORMES DE CANDIDATURE',13];foreach($stats['platforms'] as $k=>$v)$lines[]=[$k.' : '.$v,10];
 $lines[]=['',10];$lines[]=['SCORES DE COMPATIBILITE',13];$lines[]=['Offres notees : '.$stats['scores_count'],10];$lines[]=['Score initial moyen : '.($stats['baseline_avg']===null?'N/D':$stats['baseline_avg'].' %'),10];$lines[]=['Score effectif moyen : '.($stats['effective_avg']===null?'N/D':$stats['effective_avg'].' %'),10];
 $lines[]=['',10];$lines[]=['METHODOLOGIE',13];
 foreach(['Les indicateurs reposent sur les dates d envoi et evenements saisis.', 'Les reponses et entretiens sont indicatifs, pas verifies aupres des recruteurs.', 'Les scores concernent les offres creees sur la periode selectionnee.', 'Aucune donnee nominative ni contenu de messages ne figure dans ce rapport.'] as $t)$lines[]=[$t,9];
 if($stats['warnings'])$lines[]=['Attention : donnees partielles ('.implode(', ',$stats['warnings']).').',9];
 $pages=[];$ops='';$y=800;
 foreach($lines as [$txt,$size]){
  if($y<62){$pages[]=$ops;$ops='';$y=800;}
  $txt=function_exists('mb_strimwidth')?mb_strimwidth((string)$txt,0,82,'...', 'UTF-8'):substr((string)$txt,0,82);
  $ops.="BT /F1 $size Tf 48 $y Td (".reports_pdf_text($txt).") Tj ET\n";
  $y-=($size>=13?24:19);
 }
 $pages[]=$ops;
 $objects=[];$add=static function(string $data)use(&$objects):int{$objects[]=$data;return count($objects);};
 $catalog=$add('');$pagesRoot=$add('');$font=$add('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>');$pageIds=[];
 foreach($pages as $stream){$content=$add('<< /Length '.strlen($stream).' >>' ."\nstream\n".$stream."endstream");$pageIds[]=$add('<< /Type /Page /Parent '.$pagesRoot.' 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 '.$font.' 0 R >> >> /Contents '.$content.' 0 R >>');}
 $objects[$catalog-1]='<< /Type /Catalog /Pages '.$pagesRoot.' 0 R >>';$objects[$pagesRoot-1]='<< /Type /Pages /Kids ['.implode(' ',array_map(static fn($id)=>$id.' 0 R',$pageIds)).'] /Count '.count($pageIds).' >>';
 $pdf="%PDF-1.4\n";$offset=[0];foreach($objects as $i=>$obj){$offset[]=strlen($pdf);$pdf.=($i+1)." 0 obj\n".$obj."\nendobj\n";}
 $xref=strlen($pdf);$pdf.="xref\n0 ".(count($objects)+1)."\n0000000000 65535 f \n";foreach(array_slice($offset,1) as $pos)$pdf.=sprintf('%010d 00000 n ', $pos)."\n";
 return $pdf."trailer\n<< /Size ".(count($objects)+1)." /Root $catalog 0 R >>\nstartxref\n$xref\n%%EOF";
}
function reports_handle_export():void {
 if(!hash_equals((string)($_SESSION['csrf']??''),(string)($_POST['csrf']??''))){http_response_code(403);exit('Session expirée. Rechargez la page.');}
 $period=reports_period((string)($_POST['period']??'90'));$format=(string)($_POST['format']??'');
 if(!in_array($format,['csv','pdf'],true)){http_response_code(400);exit('Format inconnu.');}
 try{
  $uid=local_user_id();
  if($format==='csv'){ $rows=reports_rows($uid,$period); reports_csv($rows,$period); exit; }
  $stats=analytics_load($uid,$period);$pdf=reports_pdf($stats,$period);
  header('Content-Type: application/pdf');header('Content-Disposition: attachment; filename="osjob_rapport_'. $period.'_'.date('Ymd').'.pdf"');header('Content-Length: '.strlen($pdf));header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');echo $pdf;exit;
 }catch(Throwable $e){error_log('[OS::JOB reports] '.$e->getMessage());http_response_code(500);exit('Export indisponible. Vérifiez les journaux PHP.');}
}
