<?php

require_once (dirname(__DIR__) . '/vendor/autoload.php');
require_once (__DIR__ . '/test_util.php');

use Foo\TestMessage;

$m = new TestMessage([]);

$m->setTrueOptionalString(null);

var_dump($m->getTrueOptionalString());
