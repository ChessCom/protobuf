<?php

require_once (dirname(__DIR__) . '/vendor/autoload.php');
require_once (__DIR__ . '/test_util.php');

use Foo\TestMessage;

class TestMessageProxy extends TestMessage
{
    private $foo;

    public  function __construct($data = NULL) {
        if (0 < func_num_args()) {
            parent::__construct($data);
        }
    }

    public function setFoo($foo) {
        print("setFoo($foo)\n");
        $this->foo = $foo;
    }

    public function getFoo() {
        print("getFoo()\n");

        return $this->foo;
        //return 'bar';
    }
}

$p = new TestMessageProxy();

try {
    $p->setFoo('bar');
} catch (Exception $e) {
    print("TraceAsString: " .  $e->getTraceAsString() . "\n");
}

assert('bar' === $p->getFoo());
