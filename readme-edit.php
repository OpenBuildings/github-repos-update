#!/usr/bin/env php
<?php

namespace Clippings;

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('You must provide text for updating as 1st argument');
}

$text = $argv[1];

$dirs = array_filter(
    array_filter(glob('*'), 'is_dir'),
    function ($dir) {
        return $dir != 'vendor';
    });

$dirsHavingReademe = array_filter($dirs, function($dir) {
    return file_exists('./'.$dir.'/README.md');
});

function editFiles() {
    global $dirsHavingReademe;
    global $text;

    $files = array_map(function ($dir) {
        return './'.$dir.'/README.md';
    }, $dirsHavingReademe);

    foreach ($files as $fname) {
        $fhandle = fopen($fname,"r");
        $content = fread($fhandle,filesize($fname));

        $content = preg_replace("/((?<=\# )([a-zA-Z0-9\-_:\[!\]\(\)\/\.\?\= ]+))|(([a-zA-Z0-9\-_:\[!\]\(\)\/\.\?\= ]+)(\n[=|-]{2,}))/", "$0\n$text", $content, 1);

        echo "Editing: $fname".PHP_EOL;

        $fhandle = fopen($fname,"w");
        fwrite($fhandle,$content);
        fclose($fhandle);
    }
}

function revert() {
    global $dirsHavingReademe;

    foreach ($dirsHavingReademe as $dir) {
        exec('cd '.$dir.'/; git checkout .; cd ..;');
    }
}

function getDiffs() {
    global $dirsHavingReademe;

    exec('cp /dev/null dif.txt');
    foreach ($dirsHavingReademe as $dir) {
        exec('cd '.$dir.'/; git diff >> ../dif.txt; cd ..;');
    }
}

function pushChanges() {
    global $dirsHavingReademe;
    global $argv;

    if (isset($argv[1]) and $argv[1] == '--push') {
        foreach ($dirsHavingReademe as $dir) {
            exec('cd '.$dir.'/; git commit -am\'Add we are hiring quote\'; git push origin\master;');
        }
    }
}

revert();
editFiles();
getDiffs();
pushChanges();
