#!/usr/bin/env php
<?php

namespace Clippings;

require_once __DIR__.'/vendor/autoload.php';

$client = new \GuzzleHttp\Client();

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('You must provide GitHub organisation name as 1st argument');
}

$org = $argv[1];
$reposUrl = "https://api.github.com/orgs/$org/repos?visibility=public";
$repos = [];
while ($reposUrl) {
    $response = $client->request('GET', $reposUrl);
    $repos = array_merge($repos, json_decode($response->getBody(), true));

    $reposUrl = null;
    $links = explode(', ', $response->getHeader('link')[0]);
    foreach ($links as $link) {
        list($url, $rel) = explode('; ', $link);
        if ('rel="next"' === $rel) {
            $reposUrl = rtrim(ltrim($url, '<'), '>');
            break;
        }
    }
}

$outputs = [];

foreach ($repos as $repo) {
    exec("git clone {$repo['git_url']} repos/{$repo['full_name']}", $outputs);
    echo end($outputs);
}
