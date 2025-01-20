<?php

require_once (dirname(__DIR__) . '/vendor/autoload.php');
require_once (__DIR__ . '/test_util.php');

use Foo\TestMessage;

$message = new TestMessage();
$values = array_filter([
    'true_optional_string' => null,
]);

$message->mergeFromJsonString(json_encode($values));
//$message->mergeFromJsonString('[]');

var_dump($message->hasTrueOptionalString());