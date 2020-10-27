<?php

namespace Test\helpers;

class TestFactory
{
    public function __invoke()
    {
        return 'Hello again !';
    }
}
