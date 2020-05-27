<?php

class A{
    public function __invoke()
    {
        return 1;
    }

    public function __sleep()
    {
        var_dump(__METHOD__);
        return [];
    }

    public function __wakeup()
    {
        var_dump(__METHOD__);
    }

}

$a = new A;
var_dump($a());

echo $s = serialize($a);
var_dump(unserialize($s));