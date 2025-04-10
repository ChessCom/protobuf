<?php
# optional_constructor.php

require_once (dirname(__DIR__) . '/vendor/autoload.php');

use Foo\TestMessage;

$m1 = new TestMessage([
    'true_optional_message' => null,
]);

$m2 = new TestMessage();

$m2->setTrueOptionalMessage(null);

var_dump($m1->getTrueOptionalMessage());
var_dump($m2->getTrueOptionalMessage());
