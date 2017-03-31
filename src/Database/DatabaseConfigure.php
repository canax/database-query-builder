<?php

namespace Anax\Database;

/**
 * Namespaced exception.
 */
class DatabaseConfigure extends Database implements \Anax\Common\ConfigureInterface
{
    use \Anax\Common\ConfigureTrait;



    /**
     * Set options by using configuration.
     *
     * @return void
     */
    public function setDefaultsFromConfiguration()
    {
        parent::setOptions($this->config);
    }
}
