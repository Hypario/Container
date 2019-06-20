<?php

namespace Hypario;

use Hypario\Exceptions\NotFoundException;
use Hypario\Exceptions\TypeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Builder
{
    /**
     * @var array
     */
    private $definitions = [];

    public function __construct()
    {
        require_once('functions.php');
    }

    /**
     * @param string | array $definition the path to the definition.
     *
     * @throws TypeException Wrong type of definition
     *
     * @return void
     */
    public function addDefinitions($definition): void
    {
        // get the content of the file else throw an exception if not found
        if (is_string($definition)) {
            $required = require($definition);
            if (\is_array($required)) {
                $this->definitions[] = $required;
            } else {
                throw new TypeException('The definition must return an array');
            }
        } elseif (is_array($definition)) {
            $this->definitions[] = $definition;
        }
    }

    /**
     * Build the Container.
     *
     * @return ContainerInterface
     */
    public function build(): ContainerInterface
    {
        if (!empty($this->definitions)) {
            if (\count($this->definitions) > 1) {
                $definitions = [];
                for ($i = 0; $i < \count($this->definitions); $i++) {
                    $definitions = array_merge($definitions, $this->definitions[$i]);
                }
                return new Container($definitions);
            }
            return new Container($this->definitions[0]);
        }
        return new Container();
    }
}
