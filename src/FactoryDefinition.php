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
     * @param ContainerInterface $container
     * @param array $definitions
     * @param $id
     * @return mixed
     * @throws ContainerException
     */
    public function handle(ContainerInterface $container, array $definitions, $id)
    {
        // try to call the function
        if (is_callable($this->id)) {
            return ($this->id)($container);
        } else {
            // else get the class instance and call it
            $instance = $container->get($this->id);
            if (is_callable($instance)) {
                return $instance($container);
            } else {
                throw new ContainerException("$this->id is not a callable");
            }

        }
    }
}
