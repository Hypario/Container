<?php


namespace Hypario;

use Psr\Container\ContainerInterface;

class GetDefinition implements DefinitionsInterface
{

    private $key;

    /**
     * GetDefinition constructor.
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
     * This function must implement what your definition will do
     * @param ContainerInterface $container
     * @param array $definitions
     * @param $id
     * @return mixed
     */
    public function handle(ContainerInterface $container, array $definitions, $id)
    {
        if ($id instanceof GetDefinition) {
            return $container->get($id->getKey());
        } else {
            return $container->get($definitions[$id]->getKey());
        }
    }
}
