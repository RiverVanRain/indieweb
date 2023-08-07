<?php
/**
 * JF2 feed object view
 */
use p3k\XRay;
use GuzzleHttp\Client;

elgg_set_http_header("Content-Type: application/jf2+json;charset=utf-8");

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggEntity) {
	return;
}

$config = [
	'verify' => true,
	'timeout' => 30,
];

$client = new Client($config);

$path = $entity->getURL();
$data = [];
try {
	$response = $client->get($path);
    $body = $response->getBody()->getContents();
   
	$xray = new XRay();
    $data = $xray->parse($path, $body, ['expect' => 'feed']);
} catch (\Exception $e) {
    elgg_log('Error generating JF2 feed: ' . $e->getMessage(), 'NOTICE');
}

echo json_encode($data['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
