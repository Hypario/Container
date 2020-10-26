<?php

namespace Hypario;

if (!\function_exists('Hypario\factory')) {
    function factory($id)
    {
        return new FactoryDefinition($id);
    }
}

if (!\function_exists('Hypario\get')) {
    function get($key)
    {
        return new GetDefinition($key);
    }
}

if (!\function_exists('Hypario\object')) {
    function object($className = null)
    {
        return new ObjectDefinition($className);
    }
}
