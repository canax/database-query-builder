<?php

namespace Anax\Database;

/**
 * Database wrapper, provides a database API on top of PHP PDO for
 * enhancing the API and dealing with error reporting and tracking.
 */
class DatabaseDebug extends Database
{
    /**
     * @var integer $numQueries count all queries made
     * @var array   $queries    save all queries for debugging
     * @var array   $params     Save all parameters for debugging
     */
    private static $numQueries = 0;
    private static $queries = [];
    private static $params = [];



    /**
     * Connect to the database.
     *
     * @return self
     */
    public function connect()
    {
        if ($this->options['verbose']) {
            echo "<p>Connecting to dsn:<br><code>" . $this->options['dsn'] . "</code>";
        }

        $this->loadHistory();
        return parent::connect();
    }



    /**
     * Load query-history from session if available.
     *
     * @return int number of database queries made.
     */
    public function loadHistory()
    {
        $key = $this->options['session_key'];
        if (isset($_SESSION['CDatabase'])) {
            self::$numQueries = $_SESSION[$key]['numQueries'];
            self::$queries    = $_SESSION[$key]['queries'];
            self::$params     = $_SESSION[$key]['params'];
            unset($_SESSION[$key]);
        }
    }



    /**
     * Save query-history in session, useful as a flashmemory when redirecting to another page.
     *
     * @param string $extra enables to save some extra debug information.
     *
     * @return void
     */
    public function saveHistory($extra = null)
    {
        if (!is_null($extra)) {
            self::$queries[] = $extra;
            self::$params[] = null;
        }

        self::$queries[] = 'Saved query-history to session.';
        self::$params[] = null;

        $key = $this->options['session_key'];
        $_SESSION[$key]['numQueries'] = self::$numQueries;
        $_SESSION[$key]['queries']    = self::$queries;
        $_SESSION[$key]['params']     = self::$params;
    }



    /**
     * Get how many queries have been processed.
     *
     * @return int number of database queries made.
     */
    public function getNumQueries()
    {
        return self::$numQueries;
    }



    /**
     * Get all the queries that have been processed.
     *
     * @return array with queries.
     */
    public function getQueries()
    {
        return [self::$queries, self::$params];
    }



    /**
     * Get a HTML representation of all queries made, for debugging
     * and analysing purpose.
     *
     * @return string with HTML.
     */
    public function dump()
    {
        $html  = '<p><i>You have made ' . self::$numQueries . ' database queries.</i></p><pre>';
        
        foreach (self::$queries as $key => $val) {
            $params = empty(self::$params[$key])
                ? null
                : htmlentities(print_r(self::$params[$key], 1), null, 'UTF-8') . '<br/><br/>';
            $html .= htmlentities($val, null, 'UTF-8') . '<br/><br/>' . $params;
        }
        
        return $html . '</pre>';
    }



    /**
     * Execute a SQL-query and ignore the resultset.
     *
     * @param string $query  the SQL statement
     * @param array  $params the params array
     *
     * @throws Exception when failing to prepare question.
     *
     * @return boolean returns TRUE on success or FALSE on failure.
     */
    public function execute($query, $params = [])
    {
        self::$queries[] = $query;
        self::$params[]  = $params;
        self::$numQueries++;

        if ($this->options['verbose']) {
            echo "<p>Num query = "
                . self::$numQueries
                . "</p><p>Query = </p><pre>"
                . htmlentities($query)
                . "</pre>"
                . (empty($params)
                    ? null
                    : "<p>Params:</p><pre>" . htmlentities(print_r($params, 1)) . "</pre>"
                );
        }

        return parent::execute($query, $params);
    }
}
