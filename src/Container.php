<?php

namespace Hypario;

use Hypario\Exceptions\ContainerException;
use Hypario\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{

    /**
     * @var array
     */
    private $definitions;

    /**
     * @var array
     */
    private $instances;

    /**
     * Container constructor.
     * @param array $definitions
     */
    public function __construct(array $definitions = [])
    {
        $this->definitions = $definitions;
        $this->instances = [ContainerInterface::class => $this];
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @throws \ReflectionException Class does not exist
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        // if we can get the entry else an error is throw
        if ($this->has($id)) {
            // if the instance is already stocked, it's returned
            if (array_key_exists($id, $this->instances)) {
                return $this->instances[$id];
            }
            // else we try to get the instance
            $instance = $this->resolve($id);

            if ($this->isFactory($id)) {
                return $instance;
            }
            // The instance is stocked (here the & is important) and returned
            $this->instances[$id] = &$instance;
            if (array_key_exists($id, $this->definitions)) {
                unset($this->definitions[$id]);
            }
            return $this->instances[$id];
        } else {
            throw new NotFoundException("No entry was found for $id identifier");
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id): bool
    {
        // if the entry is in our definitions or instances we return true
        if (array_key_exists($id, $this->definitions) ||
            array_key_exists($id, $this->instances)
        ) {
            return true;
        } else {
            // else we look if the entry exist and instantiable, return True if it is, False otherwise
            try {
                $class = new \ReflectionClass($id);
                if ($class->isInstantiable()) {
                    return true;
                }
                return false;
            } catch (\ReflectionException $e) {
                return false;
            }
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws ContainerException
     * @throws \ReflectionException
     */
    private function resolve($id)
    {
        // we get the entry by the ReflectionClass
        if (array_key_exists($id, $this->definitions)) {
            if ($this->definitions[$id] instanceof FactoryDefinition) {
                $reflectedClass = new \ReflectionClass($this->definitions[$id]->getId());
            } elseif (is_callable($this->definitions[$id])) {
                return $this->definitions[$id]($this);
            } else {
                $reflectedClass = new \ReflectionClass($this->definitions[$id]);
            }
        } else {
            $reflectedClass = new \ReflectionClass($id);
        }
        // look if it is instantiable, throw an error otherwise
        if ($reflectedClass->isInstantiable()) {
            $constructor = $reflectedClass->getConstructor();
            // if the class have a constructor we solve it and return an instance, else an instance is returned
            if (!is_null($constructor)) {
                $parameters = $this->solveConstructor($constructor);
                return $reflectedClass->newInstanceArgs($parameters);
            } else {
                return $reflectedClass->newInstance();
            }
        } else {
            throw new ContainerException("{$reflectedClass->getName()} is not instanciable");
        }
    }

    /**
     * @param \ReflectionMethod $constructor
     * @return array
     * @throws \ReflectionException
     */
    private function solveConstructor(\ReflectionMethod $constructor): array
    {
        // we get the parameters needed for the class
        $parameters = $constructor->getParameters();
        $received = [];
        // recursive function to instantiate all the parameters (or get the variables) the constructor need
        foreach ($parameters as $params) {
            if ($params->getClass()) {
                $received[] = $this->get($params->getClass()->getName());
            } else {
                $received[] = $params->getDefaultValue();
            }
        }
        return $received;
    }

    private function isFactory($id): bool
    {
        if (array_key_exists($id, $this->definitions)
        and $this->definitions[$id] instanceof FactoryDefinition) {
            return true;
        }
        return false;
    }

}
