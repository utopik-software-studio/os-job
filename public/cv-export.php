<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/config/config.php';require APP_ROOT.'/app/core/database.php';require APP_ROOT.'/app/modules/documents/generator.php';require APP_ROOT.'/app/modules/cv/studio.php';
function h(string $s):string{return htmlspecialchars($s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function x(string $s):string{return htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u','',$s)??'',ENT_XML1|ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function cv_rich(string $s):string {
 $s=h($s);
 $s=preg_replace('/\*\*(.+?)\*\*/u','<strong>$1</strong>',$s)??$s;
 $s=preg_replace('/(?m)^\s*[-•]\s*/u','• ',$s)??$s;
 return nl2br($s);
}
function cv_section(string $key,string $title,array $c):string{
 $items=$c[$key]??[];if(!$items)return '';$html='<section class="section"><h2>'.h($title).'</h2>';
 foreach($items as $it){if(is_array($it)){$html.='<article><strong>'.h((string)($it['title']??'')).'</strong><small>'.h(trim((string)($it['organization']??'').' · '.(string)($it['period']??''),' ·')).'</small><p>'.cv_rich((string)($it['description']??'')).'</p></article>';}else $html.='<div class="pill">'.h((string)$it).'</div>';}
 return $html.'</section>';
}
try{
 $d=cv_owned(cv_owner(),(int)($_GET['id']??0));$c=$d['content'];$format=(string)($_GET['format']??'print');$premium=$d['template_key']==='executive';
 if($format==='docx'){
  if(!class_exists('ZipArchive'))throw new RuntimeException('Activez extension=zip dans php.ini.');
  // WordprocessingML structuré : styles, sections, colonne latérale Executive et paragraphes éditables.
  $para=function(string $value,string $style='Normal') : string {
    $value=trim($value);if($value==='')return '';
    $props='<w:pPr><w:pStyle w:val="'.$style.'"/><w:spacing w:after="80"/></w:pPr>';
    $result='';foreach(preg_split('/\R/u',$value)?:[] as $line){$line=trim($line);if($line==='')continue;
      $bullet=preg_match('/^[-•]\s*/u',$line)===1;
      $line=preg_replace('/^[-•]\s*/u','• ',$line)??$line;
      $parts=preg_split('/(\*\*[^*]+\*\*)/u',$line,-1,PREG_SPLIT_DELIM_CAPTURE)?:[];
      $runs='';foreach($parts as $part){if($part==='')continue;$strong=str_starts_with($part,'**')&&str_ends_with($part,'**');$part=$strong?substr($part,2,-2):$part;
        $runs.='<w:r>'.($strong?'<w:rPr><w:b/></w:rPr>':'').'<w:t xml:space="preserve">'.x($part).'</w:t></w:r>';
      }
      $result.='<w:p>'.$props.$runs.'</w:p>';
    }return $result;
  };
  $group=function(string $key,string $heading,array $content)use($para):string{
    if(empty($content[$key]))return '';$r=$para($heading,'Section');foreach($content[$key] as $item){
      if(is_array($item)){$r.=$para((string)($item['title']??''),'ItemTitle');$r.=$para(trim((string)($item['organization']??'').' · '.(string)($item['period']??'')),'Meta');$r.=$para((string)($item['description']??''),'Normal');}
      else $r.=$para((string)$item,'Normal');
    }return $r;
  };
  $main=$para((string)$c['name'],'Name').$para((string)$c['headline'],'Headline');
  if(!$premium)$main.=$para(preg_replace('/\s*\|\s*/u',"\n",(string)$c['contact'])??(string)$c['contact'],'Meta');
  if(trim((string)$c['summary'])!=='')$main.=$para('PROFIL PROFESSIONNEL','Section').$para((string)$c['summary']);
  $main.=$group('experiences','EXPÉRIENCES PROFESSIONNELLES',$c).$group('achievements','RÉALISATIONS MARQUANTES',$c).$group('education','FORMATION',$c);
  if(!$premium)$main.=$group('skills','COMPÉTENCES',$c).$group('languages','LANGUES',$c);
  if($premium){
    $side=$para('CONTACT','SideHeading').$para(preg_replace('/\s*\|\s*/u',"\n",(string)$c['contact'])??(string)$c['contact'],'SideText');
    foreach(['skills'=>'COMPÉTENCES','languages'=>'LANGUES'] as $key=>$heading){if(empty($c[$key]))continue;$side.=$para($heading,'SideHeading');foreach($c[$key] as $item)$side.=$para((string)$item,'SideText');}
    $cell=function(string $body,int $width,string $fill='') : string {return '<w:tc><w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/>'.($fill?'<w:shd w:fill="'.$fill.'"/>':'').'<w:tcMar><w:top w:w="230" w:type="dxa"/><w:left w:w="230" w:type="dxa"/><w:bottom w:w="230" w:type="dxa"/><w:right w:w="230" w:type="dxa"/></w:tcMar></w:tcPr>'.$body.'</w:tc>';};
    $body='<w:tbl><w:tblPr><w:tblW w:w="10000" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblBorders><w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/><w:insideH w:val="nil"/><w:insideV w:val="nil"/></w:tblBorders></w:tblPr><w:tblGrid><w:gridCol w:w="2900"/><w:gridCol w:w="7100"/></w:tblGrid><w:tr>'.$cell($side,2900,'102532').$cell($main,7100).'</w:tr></w:tbl>';
  }else $body=$main;
  $styles='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="19"/><w:color w:val="243846"/></w:rPr></w:rPrDefault></w:docDefaults>';
  foreach(['Normal'=>[19,'243846',false],'Name'=>[35,'203746',true],'Headline'=>[19,'B78642',true],'Section'=>[19,'203746',true],'ItemTitle'=>[19,'203746',true],'Meta'=>[16,'71808B',false],'SideHeading'=>[19,'D1AC70',true],'SideText'=>[17,'FFFFFF',false]] as $key=>$cfg){$styles.='<w:style w:type="paragraph" w:styleId="'.$key.'"><w:name w:val="'.$key.'"/><w:rPr><w:sz w:val="'.$cfg[0].'"/><w:color w:val="'.$cfg[1].'"/>'.($cfg[2]?'<w:b/>':'').'</w:rPr><w:pPr><w:spacing w:before="'.($key==='Section'?160:0).'" w:after="'.($key==='Section'?100:60).'"/></w:pPr></w:style>';}
  $styles.='</w:styles>';
  $xml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$body.'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="850" w:right="850" w:bottom="850" w:left="850"/></w:sectPr></w:body></w:document>';
  $temp=tempnam(sys_get_temp_dir(),'cv_');$z=new ZipArchive();if($z->open($temp,ZipArchive::OVERWRITE)!==true)throw new RuntimeException('Impossible de créer le DOCX.');$z->addFromString('[Content_Types].xml','<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>');$z->addFromString('_rels/.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');$z->addFromString('word/document.xml',$xml);$z->addFromString('word/styles.xml',$styles);$z->addFromString('word/_rels/document.xml.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');$z->close();header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');header('Content-Disposition: attachment; filename="OSJOB_CV_'.(int)$d['id'].'.docx"');header('Content-Length: '.filesize($temp));header('X-Content-Type-Options: nosniff');readfile($temp);unlink($temp);exit;
 }
 if(!in_array($format,['print','html'],true))throw new InvalidArgumentException('Format invalide.');
 header('Content-Type: text/html; charset=utf-8');header('X-Content-Type-Options: nosniff');header('Content-Security-Policy: default-src \'none\'; style-src \'unsafe-inline\'; script-src \'unsafe-inline\'; img-src \'self\' data:; base-uri \'none\'');
 $main='<main><header><h1>'.h((string)$c['name']).'</h1><h3>'.h((string)$c['headline']).'</h3>'.(!$premium?'<p>'.nl2br(h(preg_replace('/\s*\|\s*/u',"\n",(string)$c['contact'])??(string)$c['contact'])).'</p>':'').'</header>';
 if(trim((string)$c['summary'])!=='')$main.='<section class="section"><h2>Profil professionnel</h2><p>'.nl2br(h((string)$c['summary'])).'</p></section>';
 $main.=cv_section('experiences','Expériences professionnelles',$c).cv_section('achievements','Réalisations marquantes',$c).cv_section('education','Formation',$c);
 if(!$premium)$main.=cv_section('skills','Compétences',$c).cv_section('languages','Langues',$c);$main.='</main>';
 $side=$premium?'<aside>'.(!empty($d['include_photo'])?'<div class="photo"><img src="photo.php" alt="Photo du profil"></div>':'').'<h4>CONTACT</h4><p>'.nl2br(h(preg_replace('/\s*\|\s*/u',"\n",(string)$c['contact'])??(string)$c['contact'])).'</p>'.cv_section('skills','Compétences',$c).cv_section('languages','Langues',$c).'</aside>':'';
 echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>'.h($d['title']).'</title><style>@page{size:A4;margin:0}*{box-sizing:border-box}body{margin:0;background:#e8eef2;color:#203443;font:9.5pt/1.28 Arial,sans-serif}.controls{padding:12px;text-align:center;background:#e8eef2}.controls button{padding:9px 18px;background:#173c51;color:white;border:0;border-radius:6px;cursor:pointer}.sheet{width:210mm;min-height:297mm;margin:15px auto;background:white;box-shadow:0 2px 20px #aaa;display:flex}.sheet.ats{display:block;padding:15mm 18mm}.sheet aside{background:#102532;color:#edf2f5;width:31%;padding:12mm 6mm;overflow-wrap:anywhere}.sheet main{flex:1;padding:12mm 10mm;min-width:0;overflow-wrap:anywhere}h1{font-size:21pt;line-height:1.05;margin:0}h3{font-size:10pt;color:#b9823b;letter-spacing:1px;text-transform:uppercase;margin:7px 0 12px}h2{font-size:10pt;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #cda464;padding-bottom:4px;margin:13px 0 8px}h4{color:#cda464;margin-top:16px}p{margin:4px 0;white-space:normal}article{margin:0 0 11px;break-inside:avoid-page}article p{line-height:1.25}article p strong{display:inline}article strong,article small{display:block}article small{color:#687986}.sheet aside article small{color:#b9c9d2}.pill{margin-bottom:3px}.photo{background:#34505e;text-align:center;margin-bottom:14px}.photo img{display:block;width:100%;max-height:39mm;object-fit:cover}.sheet.ats h2{border-color:#94a4ae}.sheet.ats h3{color:#203443}@media print{body{background:white}.controls{display:none}.sheet{margin:0;box-shadow:none;min-height:0;width:210mm;print-color-adjust:exact;-webkit-print-color-adjust:exact}.sheet.executive{display:flex;align-items:stretch}.sheet.executive aside{position:static;flex:0 0 65.1mm;width:65.1mm;min-height:0}.sheet.executive main{flex:1;width:auto;padding:12mm 10mm}.sheet.ats{padding:15mm 18mm}article{break-inside:avoid-page}.section:last-child{break-inside:avoid-page}h2,h3,h4{break-after:avoid-page}.section{break-inside:auto}}@media screen and (max-width:800px){.sheet{transform-origin:top left;zoom:.65}}</style></head><body>'.($format==='print'?'<div class="controls"><button type="button" id="print-cv">Imprimer / Enregistrer en PDF</button></div>':'').'<div class="sheet '.($premium?'executive':'ats').'">'.$side.$main.'</div><script>var b=document.getElementById("print-cv");if(b){b.addEventListener("click",function(){window.focus();window.print();});}</script></body></html>';
}catch(Throwable $e){http_response_code(500);header('Content-Type: text/plain; charset=utf-8');echo $e->getMessage();}
