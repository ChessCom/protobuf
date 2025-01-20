<?php

require_once (dirname(__DIR__) . '/vendor/autoload.php');

use Google\Protobuf\Timestamp;

class MyTimestamp extends Timestamp
{
}

$t = new MyTimestamp();

var_dump($t->hasSeconds());
