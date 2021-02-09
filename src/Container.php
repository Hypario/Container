<?php

namespace Hypario;

use Composer\Autoload\ClassLoader;
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
     */
    public function __construct(array $definitions = [])
    {
        $this->definitions = $definitions;
        $this->instances = [ContainerInterface::class => $this];
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id identifier of the entry to look for
     *
     * @throws ContainerExceptionInterface error while retrieving the entry
     * @throws \ReflectionException        Class does not exist
     * @throws NotFoundExceptionInterface  no entry was found for **this** identifier
     *
     * @return mixed entry
     */
    public function get($id)
    {
        // if we can get the entry else an error is throw
        if ($this->has($id)) {
            if ($id instanceof DefinitionsInterface) {
                return $id->handle($this, $this->definitions, $id);
            }
            // if the instance is already stocked, it's returned
            if (\array_key_exists($id, $this->instances)) {
                return $this->instances[$id];
            }
            // else we try to get the instance
            $instance = $this->resolve($id);

            // The instance is stocked (here the & is important) and returned
            $this->instances[$id] = &$instance;
            if (\array_key_exists($id, $this->definitions)) {
                unset($this->definitions[$id]);
            }

            return $this->instances[$id];
        }
        throw new NotFoundException("No entry was found for $id identifier");
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id identifier of the entry to look for
     */
    public function has($id): bool
    {
        // if the entry is an instance of DefinitionsInterface, we know how to instantiate it
        if ($id instanceof DefinitionsInterface) {
            return true;
        }

        // if the entry is in our definitions or instances we return true
        if (@\array_key_exists($id, $this->definitions) ||
            @\array_key_exists($id, $this->instances)
        ) {
            return true;
        }
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

    /**
     * @param $id
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    private function resolve($id)
    {
        // we get the entry by the ReflectionClass
        if (\array_key_exists($id, $this->definitions)) {
            // if defined in definitions
            if ($this->definitions[$id] instanceof DefinitionsInterface) {
                // if we used a DefinitionInterface, return instance after a proper handle
                $instance = $this->definitions[$id]->handle($this, $this->definitions, $id);

                return $instance;
            } elseif (\is_callable($this->definitions[$id])) {
                // return the called callable giving the container in the constructor
                return $this->definitions[$id]($this);
            } elseif (\is_string($this->definitions[$id])) {
                // if is_string, maybe it's a class, we try to instantiate
                try {
                    $reflectedClass = new \ReflectionClass($this->definitions[$id]);

                    return $this->autowire($reflectedClass);
                } catch (\ReflectionException $e) {
                    // if not, return the string
                    return $this->definitions[$id];
                }
            } else {
                // return everything else
                return $this->definitions[$id];
            }
        } else {
            // if not defined, instantiate the class (we passed the has method)
            // so we know it's a class
            $reflectedClass = new \ReflectionClass($id);

            return $this->autowire($reflectedClass);
        }
    }

    /**
     * @throws \ReflectionException
     *
     * @return string
     */
    private function getVendor()
    {
        $reflection = new \ReflectionClass(ClassLoader::class);

        return \dirname($reflection->getFileName(), 2);
    }

    /**
     * @throws \ReflectionException
     *
     * @return object
     */
    private function autowire(\ReflectionClass $reflectedClass)
    {
        // path to the vendor directory prepared for a regex
        $vendorPath = addcslashes($this->getVendor(), '/\\');

        // we know the class is instanciable because else it would have thrown a NotFoundException
        $constructor = $reflectedClass->getConstructor();

        // if the class have a constructor we solve it and return an instance, else an instance is returned
        // and not in the vendor directory
        if (null !== $constructor && !preg_match("/^${vendorPath}\\.*/", $reflectedClass->getFileName())) {
            $parameters = $this->solveConstructor($constructor);

            return $reflectedClass->newInstanceArgs($parameters);
        }

        return $reflectedClass->newInstance();
    }

    /**
     * @throws \ReflectionException
     */
    private function solveConstructor(\ReflectionMethod $constructor): array
    {
        // we get the parameters needed for the class
        $parameters = $constructor->getParameters();
        $received = [];

        foreach ($constructor->getParameters() as $index => $parameter) {
            if ($parameter->isOptional()) {
                continue;
            }

            $type = $parameter->getType();
            $class = null !== $type &&
            $type instanceof \ReflectionNamedType
            && !$type->isBuiltin() ? $type->getName() : null;

            if ($class) {
                if ($parameter->isPassedByReference()) {
                    $tmp_class = $this->get($class);
                    $received[] = &$tmp_class;
                } else {
                    $received[] = $this->get($class);
                }
            } else {
                $received[] = $parameter->getDefaultValue();
            }
        }

        // recursive function to instantiate all the parameters (or get the variables) the constructor need
        /*foreach ($parameters as $params) {
            if ($params->getType() && !$params->allowsNull()) {
                if ($params->isPassedByReference()) {
                    $class = $this->get($params->getType()->getName());
                    $received[] = &$class;
                } else {
                    $received[] = $this->get($params->getType()->getName());
                }
            } else {
                $received[] = $params->getDefaultValue();
            }
        }*/

        return $received;
    }
}
