<?php

require_once __DIR__.'/vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$res = $client->request('GET', 'https://api.github.com/orgs/OpenBuildings/repos');

$response = json_decode($res->getBody(), true);
$repos = array_filter($response, function ($repo) {
    return false == $repo['private'];
});

$outputs = [];

foreach ($repos as $repo) {
    exec('hub clone openbuildings/'.$repo['name'], $outputs);
    echo end($outputs);
}
