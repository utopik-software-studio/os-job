<?php
declare(strict_types=1);
/** Import manuel : une offre à étudier, jamais une candidature. */
function os_import_manual(int $uid, array $post): int {
    $title=trim((string)($post['title']??''));
    $company=trim((string)($post['company']??''));
    $city=trim((string)($post['city']??''));
    $url=trim((string)($post['source_url']??''));
    $description=trim((string)($post['description']??''));
    $platform=trim((string)($post['discovery_platform']??''));
    if ($title==='' || mb_strlen($title)>200) throw new InvalidArgumentException('Intitulé obligatoire (200 caractères maximum).');
    if (mb_strlen($company)>190 || mb_strlen($city)>160 || mb_strlen($platform)>80) throw new InvalidArgumentException('Un champ dépasse la longueur autorisée.');
    if (mb_strlen($description)>50000) throw new InvalidArgumentException('Description trop longue (50 000 caractères maximum).');
    if ($url!=='' && (!filter_var($url,FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['http','https'],true))) throw new InvalidArgumentException('URL invalide (HTTP ou HTTPS requis).');
    $min=nullable_salary($post['salary_min']??'');
    $max=nullable_salary($post['salary_max']??'');
    if ($min!==null && $max!==null && $max<$min) throw new InvalidArgumentException('Salaire maximum inférieur au minimum.');
    $pdo=db();
    $pdo->beginTransaction();
    try {
        $stmt=$pdo->prepare('INSERT INTO job_offers (user_id,title,company,city,salary_min,salary_max,source_url,description,status,discovery_platform) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$uid,$title,$company,$city,$min,$max,$url,$description,'to_review',$platform!==''?$platform:'Import manuel']);
        $id=(int)$pdo->lastInsertId();
        $space=filter_var($post['space_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
        if ($space) {
            $check=$pdo->prepare('SELECT id FROM search_spaces WHERE id=? AND user_id=?');
            $check->execute([$space,$uid]);
            if (!$check->fetchColumn()) throw new InvalidArgumentException('Espace de recherche invalide.');
            $link=$pdo->prepare('INSERT INTO search_offer_links (search_space_id,job_offer_id) VALUES (?,?)');
            $link->execute([$space,$id]);
        }
        $pdo->commit();
        return $id;
    } catch(Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
function os_import_recent(int $uid): array {
    $s=db()->prepare('SELECT id,title,company,city,discovery_platform,created_at FROM job_offers WHERE user_id=? ORDER BY created_at DESC,id DESC LIMIT 12');
    $s->execute([$uid]);return $s->fetchAll(PDO::FETCH_ASSOC);
}
