<?php

namespace Hypario;

use Hypario\Exceptions\ContainerException;
use Psr\Container\ContainerInterface;

class FactoryDefinition implements DefinitionsInterface
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param $id
     *
     * @throws ContainerException
     *
     * @return mixed
     */
    public function handle(ContainerInterface $container, array $definitions, $id)
    {
        // try to call the function
        if (\is_callable($this->id)) {
            return ($this->id)($container);
        }
        // else get the class instance and call it
        $instance = $container->get($this->id);
        if (\is_callable($instance)) {
            return $instance($container);
        }
        throw new ContainerException("$this->id is not a callable");
    }
}
