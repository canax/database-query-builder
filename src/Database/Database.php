<?php

namespace Anax\Database;

use \Anax\Database\Exception\Exception;

/**
 * Database wrapper, provides a database API on top of PHP PDO for
 * enhancing the API and dealing with error reporting and tracking.
 */
class Database
{
    /**
     * @var array        $options used when creating the PDO object
     * @var PDO          $pdo     the PDO object
     * @var PDOStatement $stmt    the latest PDOStatement used
     */
    protected $options;
    private $pdo = null;
    private $stmt = null;



    /**
     * Constructor creating a PDO object connecting to a choosen database.
     *
     * @param array $options containing details for connecting to the database.
     */
    public function __construct($options = [])
    {
        $this->setOptions($options);
    }



    /**
     * Set options and connection details.
     *
     * @param array $options containing details for connecting to the database.
     *
     * @return void
     */
    public function setOptions($options = [])
    {
        $default = [
            'dsn'             => null,
            'username'        => null,
            'password'        => null,
            'driver_options'  => null,
            'table_prefix'    => null,
            'fetch_mode'      => \PDO::FETCH_OBJ,
            'session_key'     => 'Anax\Database',
            'verbose'         => null,
            'debug_connect'   => false,
        ];
        $this->options = array_merge($default, $options);
    }



    /**
     * Set a single option.
     *
     * @param string $option which to set.
     * @param mixed  $value  to set.
     *
     * @return self
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
        return $this;
    }



    /**
     * Connect to the database, allow being called multiple times
     * but ignore when connection is already made.
     *
     * @return self
     *
     * @throws \Anax\Database\Exception
     */
    public function connect()
    {
        if ($this->pdo) {
            return $this;
        }

        if (!isset($this->options['dsn'])) {
            throw new Exception("You can not connect, missing dsn.");
        }

        try {
            $this->pdo = new \PDO(
                $this->options['dsn'],
                $this->options['username'],
                $this->options['password'],
                $this->options['driver_options']
            );

            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, $this->options['fetch_mode']);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            if ($this->options['debug_connect']) {
                throw $e;
            }
            throw new Exception("Could not connect to database, hiding connection details.");
        }

        return $this;
    }



    /**
     * Support arrays in params, extract array items and add to $params
     * and insert ? for each entry in the array.
     *
     * @param string $query  as the query to prepare.
     * @param array  $params the parameters that may contain arrays.
     *
     * @return array with query and params.
     */
    private function expandParamArray($query, $params)
    {
        $param = [];
        $offset = -1;

        foreach ($params as $val) {
            $offset = strpos($query, '?', $offset + 1);

            if (is_array($val)) {
                $nrOfItems = count($val);

                if ($nrOfItems) {
                    $query = substr($query, 0, $offset)
                        . str_repeat('?,', $nrOfItems  - 1)
                        . '?'
                        . substr($query, $offset + 1);
                    $param = array_merge($param, $val);
                } else {
                    $param[] = null;
                }
            } else {
                $param[] = $val;
            }
        }

        return [$query, $param];
    }



    /**
     * Execute a select-query with arguments and return the resultset.
     *
     * @param string $query  the SQL statement
     * @param array  $params the params array
     *
     * @return array with resultset
     */
    public function executeFetchAll($query, $params = [])
    {
        $this->execute($query, $params);
        return $this->stmt->fetchAll();
    }



    /**
     * Execute a select-query with arguments and return the first row
     * in the resultset.
     *
     * @param string $query  the SQL statement
     * @param array  $params the params array
     *
     * @return array with resultset
     */
    public function executeFetch($query, $params = [])
    {
        $this->execute($query, $params);
        return $this->stmt->fetch();
    }



    /**
     * Execute a select-query with arguments and insert the results into
     * a new object of the class.
     *
     * @param string $query  the SQL statement
     * @param array  $params the params array
     * @param string $class  the class to create an object of and insert into
     *
     * @return array with resultset
     */
    public function executeFetchClass($query, $params, $class)
    {
        $this->execute($query, $params);
        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $class);
        return $this->stmt->fetch();
    }



    /**
     * Execute a select-query with arguments and insert the results into
     * an existing object.
     *
     * @param string $query  the SQL statement
     * @param array  $params the params array
     * @param string $object the existing object to insert into
     *
     * @return array with resultset
     */
    public function executeFetchInto($query, $param, $object = null)
    {
        if (!$object) {
            $object = $param;
            $param = [];
        }
        $this->execute($query, $param);
        $this->stmt->setFetchMode(\PDO::FETCH_INTO, $object);
        return $this->stmt->fetch();
    }



    /**
     * Fetch all resultset.
     *
     * @return array with resultset.
     */
    public function fetchAll()
    {
        return $this->stmt->fetchAll();
    }



    /**
     * Fetch one resultset.
     *
     * @return array with resultset.
     */
    public function fetch()
    {
        return $this->stmt->fetch();
    }



    /**
     * Fetch one resultset as a new object from this class.
     *
     * @param object $class which type of object to instantiate.
     *
     * @return array with resultset.
     */
    public function fetchClass($class)
    {
        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $class);
        return $this->stmt->fetch();
    }



    /**
     * Fetch full resultset as new objects from this class.
     *
     * @param object $class which type of object to instantiate.
     *
     * @return array with resultset.
     */
    public function fetchAllClass($class)
    {
        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $class);
        return $this->stmt->fetchAll();
    }



    /**
     * Fetch one resultset into an object.
     *
     * @param object $object to insert values into.
     *
     * @return array with resultset.
     */
    public function fetchInto($object)
    {
        $this->stmt->setFetchMode(\PDO::FETCH_INTO, $object);
        return $this->stmt->fetch();
    }



    /**
     * Execute a SQL-query and ignore the resultset.
     *
     * @param string $query  the SQL statement
     * @param array  $params the params array
     *
     * @return self
     *
     * @throws Exception when failing to prepare question.
     */
    public function execute($query, $params = [])
    {
        list($query, $params) = $this->expandParamArray($query, $params);

        $this->stmt = $this->pdo->prepare($query);
        if (!$this->stmt) {
            $this->pdoException($query, $params);
        }

        if ($this->options["verbose"]) {
            echo $query . "\n";
            print_r($params);
        }

        $res = $this->stmt->execute($params);
        if (!$res) {
            $this->statementException($query, $params);
        }

        return $this;
    }



    /**
     * Throw exception using detailed message.
     *
     * @param string       $msg     detailed error message from PDO
     * @param string       $query   query to execute
     * @param array        $param   to match ? in statement
     *
     * @return void
     *
     * @throws \Anax\Database\Exception
     */
    protected function createException($msg, $query, $param)
    {
        throw new Exception(
            $msg
            . "<br><br>SQL ("
            . substr_count($query, "?")
            . " params):<br><pre>$query</pre><br>PARAMS ("
            . count($param)
            . "):<br><pre>"
            . implode($param, "\n")
            . "</pre>"
            . ((count(array_filter(array_keys($param), 'is_string')) > 0)
                ? "WARNING your params array has keys, should only have values."
                : null)
        );
    }



    /**
     * Throw exception when pdo failed using detailed message.
     *
     * @param string       $query   query to execute
     * @param array        $param   to match ? in statement
     *
     * @return void
     *
     * @throws \Anax\Database\Exception
     */
    protected function pdoException($query, $param)
    {
        $this->createException($this->pdo->errorInfo()[2], $query, $param);
    }



    /**
     * Throw exception when statement failed using detailed message.
     *
     * @param string       $query   query to execute
     * @param array        $param   to match ? in statement
     *
     * @return void
     *
     * @throws \Anax\Database\Exception
     */
    protected function statementException($query, $param)
    {
        $this->createException($this->stmt->errorInfo()[2], $query, $param);
    }



    /**
     * Return last insert id from autoincremented key on INSERT.
     *
     * @return integer as last insert id.
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }



    /**
    * Return rows affected of last INSERT, UPDATE, DELETE
    *
    * @return integer as rows affected on last statement
    */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }



    /**
     * Fetch one resultset as an object from this class.
     * OBSOLETE replaced by fetchClass
     *
     * @param object $class which type of object to instantiate.
     *
     * @return array with resultset.
     */
    public function fetchObject($class)
    {
        return $this->stmt->fetchObject($class);
    }



    /**
     * Fetch one resultset. OBSOLETE replace by fetch()
     *
     * @return array with resultset.
     */
    public function fetchOne()
    {
        return $this->stmt->fetch();
    }
}
