<?php

namespace Hypario;

use Psr\Container\ContainerInterface;

class FactoryDefinition implements DefinitionsInterface
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function handle(ContainerInterface $container, array $definitions, $id)
    {
        return new \ReflectionClass($definitions[$id]->id);
    }
}
