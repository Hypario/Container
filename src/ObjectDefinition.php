<?php


namespace Hypario;

use Psr\Container\ContainerInterface;

class ObjectDefinition implements DefinitionsInterface
{

    /**
     * Store all the parameters for the constructor
     * @var array
     */
    public $params;

    /**
     * @var string|null
     */
    private $className;

    /**
     * ObjectDefinition constructor.
     * @param string|null $className
     */
    public function __construct(?string $className = null)
    {
        $this->className = $className;
    }

    /**
     * @param mixed ...$params
     * @return ObjectDefinition
     */
    public function constructor(...$params): self
    {
        $this->params = $params;
        return $this;
    }

    public function handle(ContainerInterface $container, array $definitions, $id)
    {
        if (!is_null($this->className)) {
            $reflectedClass = new \ReflectionClass($this->className);
        } else {
            $reflectedClass = new \ReflectionClass($id);
        }

        $params = [];
        foreach ($this->params as $param) {
            if (is_object($param)) {
                $params[] = $container->get($param);
            } else {
                $params[] = $param;
            }
        }
        return $reflectedClass->newInstanceArgs($params);
    }

}
