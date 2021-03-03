<?php
declare(strict_types=1);

namespace App;

require __DIR__ . '/../vendor/autoload.php';

$lists = [
        'normshield_all_attack.ipset',
        'normshield_high_attack.ipset',
        'firehol_level3.netset',
        'ciarmy.ipset',
    ];
$blacklist = new BlackList\BlackList($lists);
$blacklist->getList();