<?php

require_once (dirname(__DIR__) . '/vendor/autoload.php');
require_once (__DIR__ . '/test_util.php');

use Foo\TestMessage;

$message = new TestMessage();

$message->setOptionalString('bar');

var_dump(property_exists(TestMessage::class, 'optional_string'));
var_dump(property_exists(TestMessage::class, 'foo'));

var_dump(property_exists($message, 'optional_string'));
var_dump(property_exists($message, 'foo'));

var_dump(isset($message->optional_string));
var_dump(isset($message->foo));
