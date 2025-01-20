<?php

require_once (dirname(__DIR__) . '/vendor/autoload.php');
require_once (__DIR__ . '/test_util.php');

use Google\Protobuf\FieldMask;

$m1 = new FieldMask();
$m2 = new FieldMask();

$m1->setPaths(['phoneNumber', 'email_address']);

$m2->mergeFromJsonString($m1->serializeToJsonString());

var_dump($m2->getPaths());
var_dump($m2->serializeToJsonString());
