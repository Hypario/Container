<?php

namespace Test\helpers;

class TestClass
{
    public $id;

    public function __construct()
    {
        $this->id = uniqid();
    }
}
