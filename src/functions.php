<?php

namespace Hypario;

if (!\function_exists('Hypario\factory')) {
    function factory($id)
    {
        return new FactoryDefinition($id);
    }
}
