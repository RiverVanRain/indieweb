<?php

/**
 * Elgg JF2 feed output pageshell
 *
 */

use p3k\XRay;
use GuzzleHttp\Client;

elgg_set_http_header("Content-Type: application/jf2feed+json;charset=utf-8");

$config = [
    'verify' => true,
    'timeout' => 30,
];

$client = new Client($config);

// Remove feed from URL
$path = elgg_http_remove_url_query_element(elgg_get_current_url(), 'view');
$data = [];
try {
    $response = $client->get($path);
    $body = $response->getBody()->getContents();

    $xray = new XRay();
    $data = $xray->parse($path, $body, ['expect' => 'feed']);
} catch (\Exception $e) {
    elgg_log('Error generating JF2 feed: ' . $e->getMessage(), \Psr\Log\LogLevel::NOTICE);
}

echo json_encode($data['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
