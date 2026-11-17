<?php
/** OS::JOB v0.14.1 — feuille de route informative, aucun accès SQL requis. */
$history = [
 ['0.14.1','Import par copier-coller','En recette','Extraction locale heuristique depuis une annonce collée, édition et confirmation avant création.'],
 ['0.14.0.1','Correctif Centre d’import et roadmap','Validée','Liste déroulante des plateformes et planification détaillée des cycles futurs.'],
 ['0.14.0','Centre d’import','Validée','Saisie manuelle, prévisualisation, validation et création d’une offre à étudier.'],
 ['0.13.5','Stabilisation & UX','Validée','Renforcement des exports, accessibilité et contrôles de non-régression.'],
 ['0.13.4','Rapports & Exports','Validée','Exports CSV compatibles Excel et rapports PDF synthétiques, filtrés par période et sans données personnelles détaillées.'],
 ['0.13.3','Roadmap & Versionning','Validée','Page intégrée de suivi des versions, des lots et de l’historique. Correctifs statistiques regroupés dans cette livraison.'],
 ['0.13.2','Statistiques & Analytics','Validée','Indicateurs, filtres temporels, graphiques d’activité et analyse des scores.'],
 ['0.13.1','Dashboard 2.0','Validée','Pilotage, actions prioritaires et graphique d’activité.'],
 ['0.13.0.1','Hotfix profil','Validée','Correction de collision de variable dans la navigation.'],
 ['0.13.0','Design System & Navigation','Validée','Navigation modernisée, icônes SVG locales, adaptation mobile.'],
 ['0.12.2','Consolidation & historique','Validée','Historique complet des dossiers et consolidation.'],
 ['0.12.1','Améliorations des candidatures','Livrée','Lots fonctionnels et correctifs ciblés.'],
 ['0.12.0','Communication & entretiens','Livrée','Suivi des échanges et des entretiens.'],
 ['0.11.x','Atelier de candidature','Livrée','Édition et amélioration des CV et documents.'],
 ['0.10.x','Parcours intelligent','Livrée','Enrichissements du parcours et des analyses.'],
 ['0.9.x','IA locale & fiabilisation','Livrée','Génération assistée et contrôles de cohérence.'],
 ['0.8.x','Analyse des offres & traçabilité','Livrée','ATS, analyses et historiques documentaires.'],
 ['0.7.0','Modèles & personnalisation','Livrée','Personnalisation des documents.'],
 ['0.6.0','Génération de documents','Livrée','Production de CV et lettres.'],
 ['0.5.0','Alertes & scoring','Livrée','Scores, alertes et prévisualisation PDF.'],
 ['0.4.0','Tableau & Kanban','Livrée','Suivi des candidatures.'],
 ['0.3.0','Suggestions & zones','Livrée','Ciblage de la recherche.'],
 ['0.2.0','Mon profil','Livrée','Profil professionnel structuré.'],
];
$cycles = [
 ['v0.14.x','Import et acquisition des offres','Réduire le temps nécessaire pour transformer une annonce Internet en offre structurée.',[
 ['0.14.0','Centre d’import','Validée','Page dédiée, saisie manuelle, offres récentes ; journal dédié encore à développer.'],
 ['0.14.1','Import par copier-coller','En recette','Extraction locale du poste, entreprise, lieu, contrat, salaire et compétences, à confirmer manuellement.'],
 ['0.14.2','Import par URL','Prévue','Récupération des annonces publiques lorsque le site l’autorise.'],
 ['0.14.3','Import de documents','Prévue','Extraction depuis PDF, DOCX et TXT.'],
 ['0.14.4','Import CSV','Prévue','Import multiple et correspondance configurable des colonnes.'],
 ['0.14.5','Détection des doublons','Prévue','Identifier les offres similaires avant l’enregistrement.'],
 ['0.14.6','Prévisualisation et validation','Prévue','Vérifier, corriger et compléter les données extraites, tous modes confondus.'],
 ['0.14.7','Stabilisation','Prévue','Tests, erreurs, performances et documentation.'],
 ]],
 ['v0.15.x','Multilingue','Internationaliser l’interface et les contenus en français, espagnol et anglais.',[
 ['0.15.0','Architecture des traductions','Prévue','Architecture centralisée FR/ES/EN.'],
 ['0.15.1','Traduction de l’interface','Prévue','Traduction complète de l’interface.'],
 ['0.15.2','Préférences linguistiques','Prévue','Préférences linguistiques du profil.'],
 ['0.15.3','Langue des documents','Prévue','CV, lettres et messages.'],
 ['0.15.4','Analyse multilingue','Prévue','Offres et IA.'],
 ['0.15.5','Formats locaux','Prévue','Dates, nombres, devises et PDF.'],
 ['0.15.6','Stabilisation multilingue','Prévue','Tests de cohérence et stabilisation.'],
 ]],
 ['v0.16.x','Connecteurs et intégrations','Intégrations externes autorisées, sécurisées et facultatives.',[
 ['0.16.0','Centre des connecteurs','Prévue','Catalogue, état de connexion, configuration et tests.'],
 ['0.16.1','Plateformes d’emploi','Prévue','France Travail, Adzuna et autres sources via API ou accès autorisé.'],
 ['0.16.2','Messagerie','Prévue','IMAP ou OAuth, réponses ciblées et association aux candidatures.'],
 ['0.16.3','Calendriers','Prévue','Entretiens via ICS ou API.'],
 ['0.16.4','Services IA','Prévue','Ollama et fournisseurs externes facultatifs.'],
 ['0.16.5','Autres intégrations','Prévue','Webhooks et services tiers compatibles.'],
 ['0.16.6','Sécurité et stabilisation','Prévue','Autorisations, secrets, déconnexion, erreurs et documentation.'],
 ]],
 ['v0.17.x','Automatisations','Planifier et tracer les opérations répétitives.',[
 ['0.17.0','Centre des automatisations','Prévue','Paramètres et tableau de bord.'],
 ['0.17.1','Relances et rappels','Prévue','Déclenchements configurables.'],
 ['0.17.2','Automatisations à l’import','Prévue','Actions après import.'],
 ['0.17.3','Suggestions IA automatiques','Prévue','Analyses et suggestions facultatives.'],
 ['0.17.4','Surveillance des réponses','Prévue','Réponses et synchronisations.'],
 ['0.17.5','Rapports périodiques','Prévue','Production planifiée.'],
 ['0.17.6','Journal et stabilisation','Prévue','Journal d’exécution, erreurs et stabilisation.'],
 ]],
 ['v1.0','Sécurité, installation et distribution','Préparer une version stable distribuable.',[
 ['1.0.0-alpha.1','Authentification','Prévue','Compte initial, sessions et protections d’accès.'],
 ['1.0.0-alpha.2','Assistant d’installation','Prévue','Diagnostic de l’environnement.'],
 ['1.0.0-alpha.3','Dépendances','Prévue','Installation ou configuration guidée.'],
 ['1.0.0-alpha.4','Sauvegardes et migrations','Prévue','Sauvegarde, restauration et migrations.'],
 ['1.0.0-beta.1','Audit de sécurité','Prévue','Audit et tests de migration.'],
 ['1.0.0-beta.2','Tests installation vierge','Prévue','Tests et documentation.'],
 ['1.0.0','Version distribuable','Objectif','Version stable distribuable.'],
 ]],
];
$transversal = [
 ['v0.14','Dépendances, compatibilité, préparation des sauvegardes et conventions de migration.'],
 ['v0.15','Préférences utilisateur et paramètres linguistiques persistants.'],
 ['v0.16','Gestion sécurisée des identifiants de connecteurs et autorisations.'],
 ['v0.17','Planification locale, journalisation et surveillance des tâches.'],
 ['v0.18–v0.19','Renforcement de la qualité des données et des contrôles métier (périmètre détaillé à définir).'],
 ['v1.0','Authentification, installateur Windows, restauration, audit et tests sur machine vierge.'],
];
?>
<div class="page-head"><div><div class="eyebrow">SYSTÈME · ÉVOLUTION</div><h1>Roadmap & versions<span class="accent">.</span></h1><p>Suivez les versions livrées, les travaux en recette et les prochaines étapes d’OS::JOB.</p></div><a class="button button-light" href="<?=url('dashboard')?>">← Tableau de bord</a></div>
<div class="rm-overview"><div class="os-card"><span>Version installée</span><strong><?=e(APP_VERSION)?></strong><small>Version applicative locale</small></div><div class="os-card"><span>Cycle actif</span><strong>v0.14</strong><small>Import et acquisition des offres</small></div><div class="os-card"><span>Étape suivante</span><strong>v0.14.2</strong><small>Import par URL</small></div></div>
<div class="rm-note" role="note"><strong>Statuts :</strong> « Validée » indique une recette utilisateur confirmée ; « Livrée » indique une fonctionnalité publiée sans validation exhaustive attestée ; « En recette » indique des vérifications restantes. Cette page est informative : les mises à jour ne s’installent pas automatiquement.</div>
<section class="rm-section"><h2>Plan de développement</h2><p class="rm-muted">Versions futures prévisionnelles ; les fonctionnalités annoncées ne sont pas encore disponibles.</p>
<?php foreach($cycles as $cycle): ?><div class="rm-cycle"><h3><?=e($cycle[0])?> — <?=e($cycle[1])?></h3><p class="rm-muted"><?=e($cycle[2])?></p><div class="rm-timeline">
<?php foreach($cycle[3] as $item): ?><article class="rm-item"><div class="rm-version">v<?=e($item[0])?></div><div class="rm-info"><div class="rm-heading"><h3><?=e($item[1])?></h3><span class="rm-pill <?= $item[2]==='Validée'?'rm-pill--done':'rm-pill--future' ?>"><?=e($item[2])?></span></div><p><?=e($item[3])?></p></div></article><?php endforeach; ?></div></div><?php endforeach; ?></section>
<section class="rm-section"><h2>Sécurité et portabilité : travaux transversaux</h2><div class="rm-timeline"><?php foreach($transversal as $row): ?><article class="rm-item"><div class="rm-version"><?=e($row[0])?></div><div class="rm-info"><p><?=e($row[1])?></p></div></article><?php endforeach; ?></div></section>
<section class="rm-section"><h2>Historique des livraisons</h2><p class="rm-muted">Récapitulatif des principales évolutions. Les sous-versions antérieures sont regroupées lorsqu’elles appartiennent à une même famille.</p><div class="rm-timeline">
<?php foreach($history as $item):?><article class="rm-item"><div class="rm-version">v<?=e($item[0])?></div><div class="rm-info"><div class="rm-heading"><h3><?=e($item[1])?></h3><span class="rm-pill <?=str_starts_with($item[2],'Validée')?'rm-pill--done':(str_starts_with($item[2],'En recette')?'rm-pill--review':'')?>"><?=e($item[2])?></span></div><p><?=e($item[3])?></p></div></article><?php endforeach;?></div></section>
<p class="rm-footnote">Historique maintenu dans le code de l’application. Il ne constitue pas un journal automatique des installations ni un registre d’audit.</p>
