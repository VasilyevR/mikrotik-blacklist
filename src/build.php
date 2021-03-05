<?php

namespace App;

require __DIR__ . '/../vendor/autoload.php';

$lists = file(__DIR__ . '/' . 'lists.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$realPath = realpath(__DIR__ . '/../blocklist-ipsets/') . DIRECTORY_SEPARATOR;
$blacklist = new BlackList\Builder($realPath, $lists);
$blacklist->buildFile(__DIR__ . '/../blocklist');
