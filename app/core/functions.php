<?php
declare(strict_types=1);
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $page='dashboard'): string { return 'index.php?page=' . rawurlencode($page); }
function active(string $target, string $current): string { return $target === $current ? ' active' : ''; }
