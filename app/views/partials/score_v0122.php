<?php
/** $baseline (nullable), $accepted (nullable), $compact (bool) */
$baseLabel=$baseline===null?'—':((int)$baseline).' %';
$hasAccepted=is_array($accepted??null);
$reviewScore=$hasAccepted?(int)$accepted['proposed_score']:null;
$delta=$hasAccepted&&$baseline!==null?$reviewScore-(int)$baseline:null;
?>
<span class="score-comparison <?= $hasAccepted?'score-comparison-accepted':'' ?>">
 <span class="score-original" title="Score initial conservé"><?=e($baseLabel)?></span>
 <?php if($hasAccepted):?><span class="score-arrow" aria-hidden="true">→</span><strong class="score-final" title="Score réévalué et accepté"><?=$reviewScore?> %</strong><small class="score-change <?=($delta??0)<0?'score-down':'score-up'?>" title="Évolution par rapport au score initial"><?= $delta===null?'Validé':(($delta>0?'+':'').$delta.' pt') ?></small><?php endif;?>
</span>
