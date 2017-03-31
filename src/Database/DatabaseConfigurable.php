<?php

namespace Anax\Database;

/**
 * Namespaced exception.
 */
class DatabaseConfigurable extends Database implements \Anax\Common\ConfigurableInterface
{
    use \Anax\Common\ConfigurableTrait;



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
