<?php

namespace App;

require __DIR__ . '/../vendor/autoload.php';

$fileListsContent = file_get_contents(__DIR__ . '/' . 'lists.txt');
$lists = explode("\n", $fileListsContent);
$realPath = realpath(__DIR__ . '/../blocklist-ipsets/');
$blacklist = new BlackList\Builder($realPath, $lists);
$blacklist->buildFile(__DIR__ . '/../blocklist.rsc');
