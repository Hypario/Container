<?php

namespace Hypario;

use Psr\Container\ContainerInterface;

interface DefinitionsInterface
{
    /**
     * This function must implement what your definition will do.
     *
     * @param $id
     */
    public function handle(ContainerInterface $container, array $definitions, $id);
}
