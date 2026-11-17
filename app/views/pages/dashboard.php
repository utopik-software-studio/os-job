<?php
$dashError=null;
try{$d=dashboard_load(local_user_id());}catch(Throwable $ex){error_log('[OS::JOB dashboard] '.$ex->getMessage());$dashError='Impossible de charger les indicateurs. Vérifiez la connexion à la base.';$d=['spaces'=>0,'offers'=>0,'preparing'=>0,'sent'=>0,'interviews'=>0,'positive'=>0,'rejected'=>0,'waiting'=>0,'overdue'=>0,'upcoming'=>0,'pending_scores'=>0,'offers_recent'=>[],'applications_recent'=>[],'reminders'=>[],'interviews_next'=>[],'activity'=>[],'weeks'=>[],'warnings'=>[]];}
$ratio=$d['sent']>0?round(100*$d['interviews']/$d['sent']):null;
$urgent=(int)$d['overdue'];$today=new DateTimeImmutable('today');
$weekMap=[];foreach($d['weeks'] as $w)$weekMap[(string)$w['wk']]=(int)$w['qty'];
$weekSeries=[];for($i=5;$i>=0;$i--){$dt=$today->modify('monday this week')->modify('-'.$i.' weeks');$weekSeries[]=['label'=>'S'.$dt->format('W'),'count'=>$weekMap[$dt->format('o-W')]??0];}
$maxWeek=max(1,...array_column($weekSeries,'count'));
?>
<div class="page-head os-dash-heading"><div><div class="eyebrow">PILOTAGE · VUE GÉNÉRALE</div><h1>Tableau de bord<span class="accent">.</span></h1><p>Votre recherche d'emploi, vos résultats et les actions qui comptent.</p></div><div class="os-dash-head-actions"><a class="button" href="<?=url('searches')?>">Voir les offres →</a><a class="button button-light" href="<?=url('applications')?>">Mes candidatures</a></div></div>
<?php if($dashError):?><div class="os-alert os-alert--danger" role="alert"><?=e($dashError)?></div><?php endif;?>
<?php if($d['warnings']):?><div class="os-alert os-alert--warning" role="status">Certains indicateurs sont temporairement indisponibles (<?=e(implode(', ',$d['warnings']))?>). Les autres sections restent accessibles.</div><?php endif;?>
<div class="os-dash-kpis">
 <a class="os-dash-kpi os-dash-kpi--teal" href="<?=url('searches')?>"><div class="os-dash-kpi__top"><span>Offres en recherche</span><span class="os-dash-kpi__symbol" aria-hidden="true">◎</span></div><strong><?= (int)$d['offers'] ?></strong><small>À étudier et en préparation, hors candidatures envoyées</small></a>
 <a class="os-dash-kpi os-dash-kpi--blue" href="<?=url('applications')?>"><div class="os-dash-kpi__top"><span>Candidatures envoyées</span><span class="os-dash-kpi__symbol" aria-hidden="true">↗</span></div><strong><?= (int)$d['sent'] ?></strong><small>Envois enregistrés dans les dossiers</small></a>
 <a class="os-dash-kpi os-dash-kpi--violet" href="<?=url('applications')?>"><div class="os-dash-kpi__top"><span>Entretiens enregistrés</span><span class="os-dash-kpi__symbol" aria-hidden="true">◷</span></div><strong><?= (int)$d['interviews'] ?></strong><small><?= (int)$d['upcoming'] ?> à venir dans les 7 prochains jours</small></a>
 <a class="os-dash-kpi os-dash-kpi--amber" href="<?=url('applications')?>"><div class="os-dash-kpi__top"><span>Relances en retard</span><span class="os-dash-kpi__symbol" aria-hidden="true">!</span></div><strong><?= (int)$d['overdue'] ?></strong><small>Rappels non terminés dont l'échéance est passée</small></a>
</div>
<div class="os-dash-main">
 <section class="os-card os-dash-priorities"><div class="os-dash-section-head"><div><span class="os-dash-kicker">À TRAITER</span><h2>Mes priorités</h2></div><a href="<?=url('applications')?>">Ouvrir les dossiers →</a></div>
  <?php if($urgent):?><div class="os-dash-action os-dash-action--danger"><span class="os-dash-action__icon" aria-hidden="true">!</span><div><strong><?= $urgent ?> rappel<?= $urgent>1?'s':'' ?> en retard</strong><small>Une action est attendue sur vos candidatures.</small></div><a href="<?=url('applications')?>">Traiter →</a></div><?php endif;?>
  <?php if($d['upcoming']):?><div class="os-dash-action os-dash-action--info"><span class="os-dash-action__icon" aria-hidden="true">◷</span><div><strong><?= (int)$d['upcoming'] ?> entretien<?= $d['upcoming']>1?'s':'' ?> à venir</strong><small>Dans les 7 prochains jours.</small></div><a href="<?=url('applications')?>">Préparer →</a></div><?php endif;?>
  <?php if($d['pending_scores']):?><div class="os-dash-action os-dash-action--warning"><span class="os-dash-action__icon" aria-hidden="true">✦</span><div><strong><?= (int)$d['pending_scores'] ?> réévaluation<?= $d['pending_scores']>1?'s':'' ?> à décider</strong><small>Propositions IA en attente de validation humaine.</small></div><a href="<?=url('searches')?>">Examiner →</a></div><?php endif;?>
  <?php if($d['preparing']):?><div class="os-dash-action"><span class="os-dash-action__icon" aria-hidden="true">↗</span><div><strong><?= (int)$d['preparing'] ?> candidature<?= $d['preparing']>1?'s':'' ?> en préparation</strong><small>Finalisez vos documents avant l'envoi.</small></div><a href="<?=url('applications')?>">Continuer →</a></div><?php endif;?>
  <?php if(!$urgent&&!$d['upcoming']&&!$d['pending_scores']&&!$d['preparing']):?><div class="os-dash-empty">Aucune priorité détectée pour le moment. Consultez vos offres pour préparer la suite.</div><?php endif;?>
 </section>
 <section class="os-card os-dash-progress"><div class="os-dash-section-head"><div><span class="os-dash-kicker">ACTIVITÉ</span><h2>Les 6 dernières semaines</h2></div></div><p class="os-dash-sub">Candidatures envoyées, regroupées par semaine d'envoi.</p><div class="os-dash-chart" role="img" aria-label="Nombre de candidatures envoyées chaque semaine">
 <?php foreach($weekSeries as $w):?><div class="os-dash-chart__col"><span class="os-dash-chart__number"><?= $w['count'] ?></span><div class="os-dash-chart__track"><span style="height:<?= $w['count']?max(9,round(100*$w['count']/$maxWeek)):0 ?>%"></span></div><small><?=e($w['label'])?></small></div><?php endforeach;?></div>
 <div class="os-dash-mini-stats"><div><strong><?= (int)$d['spaces'] ?></strong><small>Espaces de recherche</small></div><div><strong><?= (int)$d['waiting'] ?></strong><small>En attente de réponse</small></div><div><strong><?= (int)$d['positive'] ?></strong><small>Propositions / acceptées</small></div></div>
 </section>
</div>
<div class="os-dash-main os-dash-main--lower">
 <section class="os-card"><div class="os-dash-section-head"><div><span class="os-dash-kicker">PROCHAINES ÉCHÉANCES</span><h2>Rappels & entretiens</h2></div><a href="<?=url('applications')?>">Tout voir →</a></div>
 <?php if(!$d['reminders']&&!$d['interviews_next']):?><div class="os-dash-empty">Aucun rappel en attente ni entretien futur.</div><?php endif;?>
 <?php foreach($d['reminders'] as $r):$isLate=strtotime((string)$r['due_at'])<time();?><a class="os-dash-list-row" href="<?=e(url('applications').'&id='.(int)$r['application_id'])?>"><span class="os-badge <?= $isLate?'os-badge--danger':'os-badge--warning' ?>"><?= $isLate?'En retard':'Rappel' ?></span><span class="os-dash-list-row__body"><strong><?=e((string)$r['title'])?></strong><small><?=e((string)$r['company'])?> · <?=e(dashboard_date((string)$r['due_at'],true))?></small></span><span aria-hidden="true">→</span></a><?php endforeach;?>
 <?php foreach($d['interviews_next'] as $i):?><a class="os-dash-list-row" href="<?=e(url('applications').'&id='.(int)$i['application_id'])?>"><span class="os-badge os-badge--info">Entretien</span><span class="os-dash-list-row__body"><strong><?=e((string)$i['title'])?></strong><small><?=e((string)$i['company'])?> · <?=e(dashboard_date((string)$i['starts_at'],true))?></small></span><span aria-hidden="true">→</span></a><?php endforeach;?>
 </section>
 <section class="os-card"><div class="os-dash-section-head"><div><span class="os-dash-kicker">DERNIÈRES CANDIDATURES</span><h2>Suivi des dossiers</h2></div><a href="<?=url('applications')?>">Toutes les candidatures →</a></div>
 <?php if(!$d['applications_recent']):?><div class="os-dash-empty">Aucune candidature envoyée pour le moment.</div><?php endif;?>
 <?php foreach($d['applications_recent'] as $a):?><a class="os-dash-list-row" href="<?=e(url('applications').'&id='.(int)$a['id'])?>"><span class="os-dash-list-row__body"><strong><?=e((string)$a['title'])?></strong><small><?=e((string)$a['company'])?> · <?=e(dashboard_date($a['applied_at']??null))?></small></span><span class="os-badge <?=in_array($a['status'],['rejected'],true)?'os-badge--danger':(in_array($a['status'],['accepted','proposal'],true)?'os-badge--success':'os-badge--info')?>"><?=e(app_statuses()[(string)$a['status']]??(string)$a['status'])?></span></a><?php endforeach;?>
 </section>
</div>
<div class="os-dash-main os-dash-main--lower">
 <section class="os-card"><div class="os-dash-section-head"><div><span class="os-dash-kicker">OFFRES RÉCENTES</span><h2>À explorer</h2></div><a href="<?=url('searches')?>">Mes recherches →</a></div>
 <?php if(!$d['offers_recent']):?><div class="os-dash-empty">Aucune offre en recherche. Ajoutez votre première offre.</div><?php endif;?>
 <?php foreach($d['offers_recent'] as $o):$isPrep=($o['application_status']??'')==='to_apply';$score=$o['accepted_score']??$o['initial_score'];$href=$isPrep?url('applications').'&id='.(int)$o['application_id']:url('searches');?><a class="os-dash-list-row" href="<?=e($href)?>"><span class="os-dash-list-row__body"><strong><?=e((string)$o['title'])?></strong><small><?=e((string)$o['company'])?><?=!empty($o['city'])?' · '.e((string)$o['city']):''?></small></span><span class="os-dash-list-row__end"><span class="os-badge <?= $isPrep?'os-badge--warning':'os-badge--info' ?>"><?= $isPrep?'En préparation':'À étudier' ?></span><?php if($score!==null):?><small>Score <?= (int)$score ?> %<?= $o['accepted_score']!==null?' · validé':'' ?></small><?php endif;?></span></a><?php endforeach;?>
 </section>
 <section class="os-card"><div class="os-dash-section-head"><div><span class="os-dash-kicker">CHRONOLOGIE</span><h2>Activité récente</h2></div><a href="<?=url('applications')?>">Historique complet →</a></div>
 <?php if(!$d['activity']):?><div class="os-dash-empty">Aucune activité de candidature enregistrée.</div><?php endif;?>
 <?php foreach($d['activity'] as $ev):?><a class="os-dash-list-row" href="<?=e(url('applications').'&id='.(int)$ev['application_id'])?>"><span class="os-dash-list-row__body"><strong><?=e((string)$ev['company'])?> · <?=e((string)$ev['title'])?></strong><small><?=e((string)($ev['details']?:$ev['event_type']))?></small></span><time><?=e(dashboard_date((string)$ev['occurred_at']))?></time></a><?php endforeach;?>
 </section>
</div>
<div class="os-dash-bottom"><span>Indicateurs issus des données locales de votre application. Les candidatures envoyées sont distinctes des offres en recherche.</span><a href="<?=url('atelier')?>">Préparer une candidature →</a></div>
