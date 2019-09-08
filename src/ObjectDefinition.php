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

        foreach ($this->params as $param) {
            $params[] = $container->get($param);
        }

        // the rest that need to be autowired
        $rest = array_slice($reflectedClass->getConstructor()->getParameters(), count($params));

        // resolve the rest of constructor
        /** @var \ReflectionParameter $param */
        foreach ($rest as $param) {
            if ($param->getClass()) {
                $params[] = $container->get($param->getClass()->getName());
            } else {
                $params[] = $param->getDefaultValue();
            }
        }

        // inject the wanted vars to the constr

        return $reflectedClass->newInstanceArgs($params);
    }

}
