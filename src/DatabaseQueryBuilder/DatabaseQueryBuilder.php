<?php

namespace Anax\DatabaseQueryBuilder;

use Anax\Database\Database;
use Anax\DatabaseQueryBuilder\Exception\BuildException;

/**
 * Build SQL queries by method calling.
 */
class DatabaseQueryBuilder extends Database
{
    use QueryBuilderTrait;



    /**
     * Update builder settings from active configuration.
     *
     * @return void
     */
    public function setDefaultsFromConfiguration()
    {
        if ($this->options['dsn']) {
            $dsn = explode(':', $this->options['dsn']);
            $this->setSQLDialect($dsn[0]);
        }

        $this->setTablePrefix($this->options['table_prefix']);
    }



    /**
     * Execute a SQL-query.
     *
     * @param string|null|array $query  the SQL statement (or $params)
     * @param array             $params the params array
     *
     * @return self
     */
    public function execute($query = null, array $params = []) : object
    {
        // When using one argument and its array, assume its $params
        if (is_array($query)) {
            $params = $query;
            $query = null;
        }

        if (!$query) {
            $query = $this->getSQL();
        }

        return parent::execute($query, $params);
    }
}
