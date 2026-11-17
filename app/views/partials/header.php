<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> — Espace carrière</title>
<link rel="stylesheet" href="assets/css/app.css">
<link rel="stylesheet" href="assets/css/design-system.css?v=0130"><link rel="stylesheet" href="assets/css/stability.css?v=0135"><?php if($page==='analytics'):?><link rel="stylesheet" href="assets/css/analytics.css?v=0132"><?php endif;?><?php if($page==='reports'):?><link rel="stylesheet" href="assets/css/reports.css?v=0134"><?php endif;?><?php if($page==='roadmap'):?><link rel="stylesheet" href="assets/css/roadmap.css?v=0133"><?php endif;?><?php if($page==='import'):?><link rel="stylesheet" href="assets/css/import.css?v=0141"><?php endif;?><?php if($page==='dashboard'):?><link rel="stylesheet" href="assets/css/dashboard.css?v=0131"><?php endif;?><link rel="stylesheet" href="assets/css/documents.css"><link rel="stylesheet" href="assets/css/profile.css"><link rel="stylesheet" href="assets/css/job.css"><link rel="stylesheet" href="assets/css/applications.css?v=0122"><link rel="stylesheet" href="assets/css/cv-studio.css"><link rel="stylesheet" href="assets/css/atelier.css"></head><body>
<a class="os-skip-link" href="#os-main-content">Aller au contenu principal</a><div class="layout"><aside class="sidebar" id="sidebar"><a class="brand" href="<?= url() ?>"><span class="brand-mark">O</span><span>OS<span class="brand-accent">::</span>JOB<small>CAREER INTELLIGENCE</small></span></a>
<?php
// Icônes SVG intégrées, sans dépendance CDN et sans images décoratives.
$osIcons = [
 'reports'=>'<path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v6h6M9 14h7M9 18h7"/>',
 'roadmap'=>'<path d="M4 20V4m0 3h12l-2 4 2 4H4"/><circle cx="4" cy="4" r="1"/>',
 'analytics'=>'<path d="M4 20V12M10 20V5M16 20v-8M22 20V8"/><path d="M2 21h21"/>',
 'dashboard'=>'<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
 'import'=>'<path d="M12 3v12m-4-4 4 4 4-4"/><path d="M4 17v3h16v-3"/>',
 'searches'=>'<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 9h10M7 13h7M7 17h5"/>',
 'applications'=>'<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12c5 3 13 3 18 0M12 12v3"/>',
 'atelier'=>'<path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="m16 4 4 4M8 16l3.5-.7L21 5.8 18.2 3 8.7 12.5 8 16Z"/>',
 'profile'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
 'settings'=>'<path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path d="M19 13.5a7 7 0 0 0 0-3l2-1.5-2-3.5-2.4 1a8 8 0 0 0-2.6-1.5L13.5 2h-4L9 5a8 8 0 0 0-2.6 1.5L4 5.5 2 9l2 1.5a7 7 0 0 0 0 3L2 15l2 3.5 2.4-1A8 8 0 0 0 9 19l.5 3h4l.5-3a8 8 0 0 0 2.6-1.5l2.4 1L21 15Z"/>',
];
$osNav = [
 'PILOTAGE'=>[['dashboard','Tableau de bord'],['analytics','Statistiques'],['reports','Rapports & exports']],
 'RECHERCHE D’EMPLOI'=>[['searches','Mes recherches'],['import','Importer des offres'],['applications','Mes candidatures']],
 'OUTILS'=>[['atelier','Atelier de candidature'],['profile','Mon profil']],
 'SYSTÈME'=>[['roadmap','Roadmap & versions'],['settings','Paramètres']],
];
?>
<?php foreach ($osNav as $navGroupLabel=>$links): ?>
<div class="nav-label"><?= e($navGroupLabel) ?></div><nav aria-label="<?= e($navGroupLabel) ?>">
<?php foreach ($links as [$key,$label]): ?>
<a class="nav-link<?= active($key,$page) ?>" href="<?= url($key) ?>" <?= $page===$key?'aria-current="page"':'' ?>><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $osIcons[$key] ?></svg></span><span><?= e($label) ?></span></a>
<?php endforeach; ?></nav>
<?php endforeach; ?>
<div class="sidebar-bottom"><span class="status-dot"></span> Mode local <small>Version <?= e(APP_VERSION) ?></small></div></aside>
<button class="sidebar-backdrop" id="sidebarBackdrop" type="button" aria-label="Fermer la navigation" tabindex="-1"></button><div class="main-shell"><header class="topbar"><button class="menu-toggle" id="menuToggle" aria-label="Afficher le menu" aria-controls="sidebar" aria-expanded="false"><svg viewBox="0 0 24 24" width="23" height="23" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button><span class="breadcrumb">Workspace <span>/</span> <?= e(['reports'=>'Rapports & exports','roadmap'=>'Roadmap & versions','analytics'=>'Statistiques','dashboard'=>'Vue générale','searches'=>'Recherches','import'=>'Centre d’import','applications'=>'Candidatures','documents'=>'Atelier de candidature','cv-studio'=>'Atelier de candidature','atelier'=>'Atelier de candidature','profile'=>'Profil','settings'=>'Paramètres','not-found'=>'Introuvable','score-review'=>'Réévaluation du score'][$page] ?? 'Espace carrière') ?></span><span class="topbar-right"><span class="live-dot"></span> Environnement local</span><div class="os-notifications"><button id="os-bell" type="button" aria-label="Rappels" aria-expanded="false"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg> <span id="os-count" hidden>0</span></button><div id="os-reminder-panel" hidden><h3>Rappels à traiter</h3><div id="os-reminder-list">Aucun rappel en retard.</div><label class="os-sound-label"><input id="os-sound" type="checkbox" checked> Signal sonore</label></div></div></header><main class="content" id="os-main-content" tabindex="-1"><div id="os-toast" role="status" aria-live="polite" hidden></div><script>window.OSJOB_CSRF=<?=json_encode($_SESSION['csrf'])?>;</script><script src="assets/js/notifications.js" defer></script>
