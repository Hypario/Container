<?php

namespace Hypario;

use Psr\Container\ContainerInterface;

class GetDefinition implements DefinitionsInterface
{
    private $key;

    /**
     * GetDefinition constructor.
     *
     * @param $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * This function must implement what your definition will do.
     *
     * @param $id
     *
     * @return mixed
     */
    public function handle(ContainerInterface $container, array $definitions, $id)
    {
        return $container->get($this->getKey());
    }
}
