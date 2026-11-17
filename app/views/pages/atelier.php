<div class="page-heading"><div><div class="eyebrow">OS::JOB · V0.11.1</div><h1>Atelier de candidature</h1><p>Un seul espace pour créer, préparer et retrouver vos documents. Les anciens CV et lettres sont conservés.</p></div></div>
<?php if(isset($atelierError)):?><div class="doc-alert"><?=e($atelierError)?></div><?php endif;?>
<nav class="atelier-tabs" aria-label="Sections de l’atelier">
<?php foreach(['cv'=>'Mes CV','lettres'=>'Mes lettres','candidatures'=>'Mes candidatures','bibliotheque'=>'Bibliothèque'] as $key=>$name):?><a class="<?= $atelierTab===$key?'selected':'' ?>" href="<?=e(url('atelier').'&tab='.$key)?>"><?=e($name)?></a><?php endforeach;?></nav>
<div class="atelier-panel">
<?php if($atelierTab==='cv'):?>
<h2>Mes CV</h2><p>Éditeur structuré, modèle Executive Premium ou ATS Classique, aperçu et export.</p><div class="atelier-actions"><a class="doc-btn" href="<?=e(url('cv-studio'))?>">Créer depuis mon profil</a><a class="doc-btn doc-secondary" href="<?=e(url('cv-studio').'&source=blank')?>">Créer un CV vierge</a></div>
<?php foreach($atelierCvRows as $cv):?><div class="atelier-entry"><div><strong><?=e($cv['title'])?></strong><small><?=e($cv['template_key']==='executive'?'Executive Premium':'ATS Classique')?> · <?=e(substr($cv['updated_at'],0,10))?></small></div><a href="<?=e(url('cv-studio').'&id='.(int)$cv['id'])?>">Modifier / exporter →</a></div><?php endforeach;?>
<?php elseif($atelierTab==='lettres'):?>
<h2>Mes lettres</h2><p>Le générateur historique reste accessible : ses brouillons et analyses ne sont pas modifiés.</p><div class="atelier-actions"><a class="doc-btn" href="<?=e(url('documents'))?>">Créer une lettre</a></div>
<?php foreach($atelierDocs as $doc):if($doc['document_type']!=='letter')continue;?><div class="atelier-entry"><div><strong><?=e($doc['title'])?></strong><small><?=e(substr($doc['updated_at'],0,10))?></small></div><a href="<?=e(url('documents').'&id='.(int)$doc['id'])?>">Ouvrir →</a></div><?php endforeach;?>
<?php elseif($atelierTab==='candidatures'):?>
<h2>Mes candidatures</h2><p>Les documents envoyés et leurs instantanés restent gérés dans le suivi des candidatures.</p><div class="atelier-actions"><a class="doc-btn" href="<?=e(url('applications'))?>">Ouvrir le suivi complet</a></div>
<?php foreach($atelierApplications as $app):?><div class="atelier-entry"><div><strong><?=e($app['offer_title'])?></strong><small><?=e($app['company'].' · '.$app['status'])?></small></div><a href="<?=e(url('applications'))?>">Voir le suivi →</a></div><?php endforeach;?>
<?php else:?>
<h2>Bibliothèque de documents</h2><p>CV structurés et documents historiques sont listés ensemble, sans migration ni suppression des originaux.</p>
<?php foreach($atelierCvRows as $cv):?><div class="atelier-entry"><div><strong><?=e($cv['title'])?></strong><small>CV structuré · <?=e(substr($cv['updated_at'],0,10))?></small></div><a href="<?=e(url('cv-studio').'&id='.(int)$cv['id'])?>">Ouvrir →</a></div><?php endforeach;?>
<?php foreach($atelierDocs as $doc):?><div class="atelier-entry"><div><strong><?=e($doc['title'])?></strong><small><?=e($doc['document_type']==='cv'?'CV historique':'Lettre')?> · <?=e(substr($doc['updated_at'],0,10))?></small></div><a href="<?=e(url('documents').'&id='.(int)$doc['id'])?>">Ouvrir →</a></div><?php endforeach;?>
<?php endif;?></div>
