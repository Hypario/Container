<?php

namespace Test;

class TestFactory
{
    public function __invoke()
    {
        return 'Hello again !';
    }
}
