<?php


namespace Test;

class TestClassParameters
{

    private $a;
    private $b;
    private $randomParameter;

    public function __construct(TestClass $a, TestClass2 $b, int $randomParameter = 1)
    {
        $this->a = $a;
        $this->b = $b;
        $this->randomParameter = $randomParameter;
    }
}
