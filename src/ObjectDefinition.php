<?php

namespace Hypario;

use Psr\Container\ContainerInterface;

class ObjectDefinition implements DefinitionsInterface
{
    /**
     * Store all the parameters for the constructor.
     *
     * @var array
     */
    public $params;

    /**
     * @var string|null
     */
    private $className;

    /**
     * ObjectDefinition constructor.
     */
    public function __construct(?string $className = null)
    {
        $this->className = $className;
    }

    /**
     * @param mixed ...$params
     *
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
        if (null !== $this->className) {
            $reflectedClass = new \ReflectionClass($this->className);
        } else {
            $reflectedClass = new \ReflectionClass($id);
        }

        // params that will be sent to the new instance
        $params = [];

        // the needed parameters
        $parameters = $reflectedClass->getConstructor()->getParameters();
        for ($i = 0; $i < \count($parameters); ++$i) {
            // if we already have those params
            if ($i < \count($this->params)) {
                // look if the get function is used,
                if ($parameters[$i] instanceof GetDefinition) {
                    $this->params[$i] = $this->params[$i]->handle($container, $definitions, $id);
                }

                // look if passed by reference,
                if ($parameters[$i]->isPassedByReference()) {
                    // look if it needs the container to instantiate classes
                    // or put directly the variable
                    if ($container->has($this->params[$i])) {
                        $var = $container->get($this->params[$i]);
                    } else {
                        $var = $this->params[$i];
                    }
                    $params[] = &$var;
                } else {
                    // it isn't passed by reference
                    // but we do the same verification
                    if ($container->has($this->params[$i])) {
                        $params[] = $container->get($this->params[$i]);
                    } else {
                        $params[] = $this->params[$i];
                    }
                }

                // we do not have those params, so autowiring
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
