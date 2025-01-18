<?php

require_once (dirname(__DIR__) . '/vendor/autoload.php');

use Google\Protobuf\Value;

$v = new Value();

var_dump($v->hasNullValue());

$v->setNullValue(0);

var_dump($v->hasNullValue());
