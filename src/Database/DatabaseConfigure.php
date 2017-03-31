<?php

namespace Anax\Database;

/**
 * Namespaced exception.
 */
class DatabaseConfigure extends Database implements \Anax\Common\ConfigureInterface
{
    use \Anax\Common\ConfigureTrait;



    /**
     * Set options and connection details from configuration.
     *
     * @return void
     */
    public function setOptions()
    {
        parent::setOptions($this->config);
    }
}
