<?php

namespace Anax\Database;

use \Anax\Configure\ConfigureInterface;
use \Anax\Configure\ConfigureTrait;

/**
 * Namespaced exception.
 */
class DatabaseConfigure extends Database implements ConfigureInterface
{
    use ConfigureTrait {
        configure as protected loadConfiguration;
    }



    /**
     * Load and apply configurations.
     *
     * @param array|string $what is an array with key/value config options
     *                           or a file to be included which returns such
     *                           an array.
     *
     * @return self
     */
    public function configure($what)
    {
        $this->loadConfiguration($what);
        parent::setOptions($this->config);
        return $this;
    }
}
