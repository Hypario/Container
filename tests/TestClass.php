<?php

namespace Test;

class TestClass
{
    public $id;

    public function __construct()
    {
        $this->id = uniqid();
    }
}
