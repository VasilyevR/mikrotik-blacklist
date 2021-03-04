<?php

namespace App;

require __DIR__ . '/../vendor/autoload.php';

$fileListsContent = file_get_contents(__DIR__ . '/' . 'lists.txt');
$lists = explode("\n", $fileListsContent);
$blacklist = new BlackList\Builder(__DIR__ . '/../blocklist-ipsets/', $lists);
$blacklist->buildFile(__DIR__ . '/../blocklist.rsc');
