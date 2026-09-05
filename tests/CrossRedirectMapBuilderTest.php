<?php
declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

function crossMapAssert(bool $condition,string $message): void {if(!$condition)throw new RuntimeException($message);}
$directory=sys_get_temp_dir().'/cross-map-'.bin2hex(random_bytes(5));mkdir($directory);
$csv=$directory.'/legacy.csv';$xml=$directory.'/sitemap.xml';
file_put_contents($csv,"source_url;source_path;proposed_target;classification;reason\nhttps://old/crossing-ABC.html;crossing-abc.html;;deferred_crossing;no_exact_canonical_alias\nhttps://old/page.html;page.html;;unclassified;no_exact_canonical_alias\nhttps://old/crossing-MISSING.html;crossing-missing.html;;deferred_crossing;no_exact_canonical_alias\nhttps://old/crossing-BAD%20VALUE.html;crossing-bad value.html;;deferred_crossing;no_exact_canonical_alias\nhttps://old/crossing-%D0%B0%D0%B1%D0%B2.html;crossing-абв.html;;deferred_crossing;no_exact_canonical_alias\n");
file_put_contents($xml,'<?xml version="1.0"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>https://techtires.ru/cross/abc</loc></url><url><loc>https://techtires.ru/cross/bad%20value</loc></url><url><loc>https://techtires.ru/cross/%D0%B0%D0%B1%D0%B2</loc></url></urlset>');
$result=(new \app\services\CrossRedirectMapBuilder())->build($csv,$xml);
crossMapAssert($result['summary']['ready_redirects']===1,'Ready redirect count is incorrect.');
crossMapAssert($result['summary']['deferred_urls']===3,'Deferred redirect count is incorrect.');
crossMapAssert($result['summary']['unique_ready_targets']===1,'Unique target count is incorrect.');
crossMapAssert($result['ready'][0]['target_path']==='cross/abc','Cross target is incorrect.');
crossMapAssert($result['deferred'][0]['reason']==='cross_not_present_on_techtires','Deferred reason is incorrect.');
crossMapAssert($result['deferred'][1]['reason']==='legacy_path_requires_web_server_rule','Whitespace source must be deferred.');
crossMapAssert($result['deferred'][2]['reason']==='unsupported_cross_route_alias','Unsupported route alias must be deferred.');
@unlink($csv);@unlink($xml);@rmdir($directory);
echo "Cross redirect map checks passed.\n";
