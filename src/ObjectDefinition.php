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
        // get the wanted class
        if (!is_null($this->className)) {
            $reflectedClass = new \ReflectionClass($this->className);
        } else {
            $reflectedClass = new \ReflectionClass($id);
        }

        // params that will be sent to the new instance
        $params = [];

        $parameters = $reflectedClass->getConstructor()->getParameters();
        for ($i = 0; $i < count($parameters); $i++) {
            if ($i < count($this->params)) {
                if ($parameters[$i]->isPassedByReference()) {
                    $var = $container->get($this->params[$i]);
                    $params[] = &$var;
                } else {
                    $params[] = $container->get($this->params[$i]);
                }
            } elseif ($parameters[$i]->getClass()) {
                if ($parameters[$i]->isPassedByReference()) {
                    $var = $container->get($parameters[$i]->getClass()->getName());
                    $params[] = &$var;
                } else {
                    $params[] = $container->get($parameters[$i]->getClass()->getName());
                }
            } else {
                $params[] = $parameters[$i]->getDefaultValue();
            }
        }

        // inject the wanted vars to the constr

        return $reflectedClass->newInstanceArgs($params);
    }

}
