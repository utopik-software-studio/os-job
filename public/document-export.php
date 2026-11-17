<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/config/config.php';require APP_ROOT.'/app/core/database.php';require APP_ROOT.'/app/modules/documents/generator.php';
function xmlsafe(string $v):string{return htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u','',$v)??'',ENT_XML1|ENT_QUOTES,'UTF-8');}
try {
 $d=docs_owned(docs_owner(),(int)($_GET['id']??0));if(!class_exists('ZipArchive'))throw new RuntimeException('Extension PHP zip manquante. Activez extension=zip dans php.ini.');
 $key=(string)$d['template_key'];$style=docs_style_sanitize(array_merge(docs_default_style($key),docs_presentation((int)$d['id'])),$key);
 $color=strtoupper(substr($style['accent'],1));$font=xmlsafe($style['font']);$type=$d['document_type'];
 $sectionMap=['PROFIL'=>'show_summary','COMPÉTENCES'=>'show_skills','FORMATION'=>'show_education','LANGUES'=>'show_languages'];$paragraphs='';$i=0;$skip=false;
 foreach(explode("\n",str_replace("\r",'',(string)$d['body'])) as $line){$line=trim($line);$upper=mb_strtoupper($line);$head=$type==='cv'&&array_key_exists($upper,$sectionMap);
  if($head)$skip=!$style[$sectionMap[$upper]];
  if($skip)continue;
  $isFirst=$i===0;$i++;
  $size=$head?24:($isFirst&&$type==='cv'?34:21);$bold=$head||($isFirst&&$type==='cv');$spacing=$head?210:95;
  $paragraphs.='<w:p><w:pPr><w:spacing w:after="'.$spacing.'"/><w:keepTogether/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="'.$font.'" w:hAnsi="'.$font.'"/><w:sz w:val="'.$size.'"/>'.($bold?'<w:b/>':'').($head?'<w:color w:val="'.$color.'"/>':'').'</w:rPr><w:t xml:space="preserve">'.xmlsafe($line).'</w:t></w:r></w:p>';
 }
 $xml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$paragraphs.'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134"/></w:sectPr></w:body></w:document>';
 $temp=tempnam(sys_get_temp_dir(),'osjob_docx_');$z=new ZipArchive();if($z->open($temp,ZipArchive::OVERWRITE)!==true)throw new RuntimeException('Impossible de créer le Word.');
 $z->addFromString('[Content_Types].xml','<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
 $z->addFromString('_rels/.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');$z->addFromString('word/document.xml',$xml);$z->close();
 $filename='OSJOB_'.(int)$d['id'].'_'.($type==='cv'?'CV':'Lettre').'.docx';header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');header('Content-Disposition: attachment; filename="'.$filename.'"');header('Content-Length: '.filesize($temp));header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');readfile($temp);unlink($temp);
}catch(Throwable $e){http_response_code(500);header('Content-Type: text/plain; charset=utf-8');echo $e->getMessage();}
