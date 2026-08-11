<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/config.php';

$email=strtolower(trim(env('OWNER_EMAIL','')??''));$password=env('OWNER_PASSWORD','')??'';
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<10)exit("Set a valid OWNER_EMAIL and OWNER_PASSWORD of at least 10 characters.\n");
$schema=file_get_contents(__DIR__.'/schema.sql');
foreach(array_filter(array_map('trim',preg_split('/;\s*(?:\r?\n|$)/',$schema)?:[])) as $sql)db()->exec($sql);
$stmt=db()->prepare('INSERT INTO owners(email,password_hash) VALUES(?,?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash)');$stmt->execute([$email,password_hash($password,PASSWORD_DEFAULT)]);
$samples=require dirname(__DIR__).'/app/sample.php';
$stmt=db()->prepare('INSERT INTO content_documents(type,slug,title,draft_data,published_data,is_published,sort_order,published_at) VALUES(?,?,?,?,?,1,?,NOW()) ON DUPLICATE KEY UPDATE type=type');
foreach($samples as $item){$json=json_encode($item['data'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);$stmt->execute([$item['type'],$item['slug'],$item['title'],$json,$json,$item['sort_order']]);}
echo "Database seeded. Sign in with {$email}.\n";
