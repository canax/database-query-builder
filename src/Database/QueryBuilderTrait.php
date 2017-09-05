<?php

namespace Anax\Database;

use \Anax\Database\Exception\BuildException;

/**
 * Build SQL queries by method calling.
 */
trait QueryBuilderTrait
{
    /**
     * @var $dialect current database dialect used
     * @var $sql     the query built
     * @var $prefix  prefix to attach to all table names
     */
    private $dialect;
    private $sql;
    private $prefix;

    /**
     * @var $start   first line of the sql query
     * @var $from    from part
     * @var $join    join part
     * @var $set     set part for a update
     * @var $where   where part
     * @var $groupby group part
     * @var $orderby order part
     * @var $limit   limit part
     * @var $offset  offset part
     */
    private $start;
    private $from;
    private $join;
    private $set;
    private $where;
    private $groupby;
    private $orderby;
    private $limit;
    private $offset;



    /**
     * Get SQL.
     *
     * @return string with the built sql-query
     */
    public function getSQL()
    {
        if ($this->sql) {
            return $this->sql;
        }
        return $this->build();
    }



    /**
     * Build the SQL query from its parts.
     *
     * @return string as SQL query
     */
    protected function build()
    {
        $sql = $this->start . "\n"
            . ($this->from    ? $this->from . "\n"    : null)
            . ($this->join    ? $this->join           : null)
            . ($this->set     ? $this->set . "\n"     : null)
            . ($this->where   ? $this->where . "\n"   : null)
            . ($this->groupby ? $this->groupby . "\n" : null)
            . ($this->orderby ? $this->orderby . "\n" : null)
            . ($this->limit   ? $this->limit . "\n"   : null)
            . ($this->offset  ? $this->offset . "\n"  : null)
            . ";";

        return $sql;
    }



    /**
     * Clear all previous sql-code.
     *
     * @return void
     */
    protected function clear()
    {
        $this->sql      = null;
        $this->start    = null;
        $this->from     = null;
        $this->join     = null;
        $this->set      = null;
        $this->where    = null;
        $this->groupby  = null;
        $this->orderby  = null;
        $this->limit    = null;
        $this->offset   = null;
    }



    /**
     * Set database type/dialect to consider when generating SQL.
     *
     * @param string $dialect representing database type.
     *
     * @return self
     */
    public function setSQLDialect($dialect)
    {
        $this->dialect = $dialect;
        return $this;
    }



    /**
     * Set a table prefix.
     *
     * @param string $prefix to use in front of all tables.
     *
     * @return self
     */
    public function setTablePrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }



    /**
     * Create a table.
     *
     * @param string $name    the table name.
     * @param array  $columns the columns in the table.
     *
     * @return $this
     */
    public function createTable($name, $columns)
    {
        $cols = null;

        foreach ($columns as $col => $options) {
            $cols .= "\t" . $col . ' ' . implode(' ', $options) . ",\n";
        }
        $cols = substr($cols, 0, -2);

        $this->sql = "CREATE TABLE "
            . $this->prefix
            . $name
            . "\n(\n"
            . $cols
            . "\n);\n";

        if ($this->dialect == 'sqlite') {
            $this->sql = str_replace('auto_increment', '', $this->sql);
        }

        return $this;
    }



    /**
     * Drop a table.
     *
     * @param string $name the table name.
     *
     * @return $this
     */
    public function dropTable($name)
    {
        $this->sql = "DROP TABLE "
            . $this->prefix
            . $name
            . ";\n";

        return $this;
    }



    /**
     * Drop a table if it exists.
     *
     * @param string $name the table name.
     *
     * @return $this
     */
    public function dropTableIfExists($name)
    {
        $this->sql = "DROP TABLE IF EXISTS "
            . $this->prefix
            . $name
            . ";\n";

        return $this;
    }



    /**
     * Build a insert-query.
     *
     * @param string $table   the table name.
     * @param array  $columns to insert och key=>value with columns and values.
     * @param array  $values  to insert or empty if $columns has both
     *                        columns and values.
     *
     * @throws \Anax\Database\BuildException
     *
     * @return self for chaining
     */
    public function insert($table, $columns, $values = null)
    {
        list($columns, $values) = $this->mapColumnsWithValues($columns, $values);

        if (count($columns) !== count($values)) {
            throw new BuildException("Columns does not match values, not equal items.");
        }

        $cols = null;
        $vals = null;

        $max = count($columns);
        for ($i = 0; $i < $max; $i++) {
            $cols .= $columns[$i] . ', ';

            $val = $values[$i];

            if ($val == '?') {
                $vals .= $val . ', ';
            } else {
                $vals .= (is_string($val)
                    ? "'$val'"
                    : $val)
                    . ', ';
            }
        }

        $cols = substr($cols, 0, -2);
        $vals = substr($vals, 0, -2);

        $this->sql = "INSERT INTO "
            . $this->prefix
            . $table
            . "\n\t("
            . $cols
            . ")\n"
            . "\tVALUES\n\t("
            . $vals
            . ");\n";

        return $this;
    }



    /**
     * Build an update-query.
     *
     * @param string $table   the table name.
     * @param array  $columns to update or key=>value with columns and values.
     * @param array  $values  to update or empty if $columns has bot columns and values.
     *
     * @throws \Anax\Database\BuildException
     *
     * @return void
     */
    public function update($table, $columns, $values = null)
    {
        $this->clear();
        list($columns, $values) = $this->mapColumnsWithValues($columns, $values);

        if (count($columns) != count($values)) {
            throw new BuildException("Columns does not match values, not equal items.");
        }

        $cols = null;
        $max = count($columns);
        
        for ($i = 0; $i < $max; $i++) {
            $cols .= "\t" . $columns[$i] . ' = ';

            $val = $values[$i];
            if ($val == '?') {
                $cols .= $val . ",\n";
            } else {
                $cols .= (is_string($val)
                    ? "'$val'"
                    : $val)
                    . ",\n";
            }
        }

        $cols = substr($cols, 0, -2);

        $this->start = "UPDATE "
            . $this->prefix
            . $table;
        $this->set = "SET\n$cols";

        return $this;
    }



    /**
     * Build a delete-query.
     *
     * @param string $table the table name.
     * @param array  $where limit which rows are updated.
     *
     * @return self
     */
    public function deleteFrom($table, $where = null)
    {
        $this->clear();

        if (isset($where)) {
            $this->where = "WHERE\n\t(" . $where . ")";
        }

        $this->start = "DELETE";
        $this->from($table);
        return $this;
    }



    /**
     * Build a select-query.
     *
     * @param string $columns which columns to select.
     *
     * @return $this
     */
    public function select($columns = '*')
    {
        $this->clear();
        $this->start = "SELECT\n\t$columns";
        return $this;
    }



    /**
     * Build the from part.
     *
     * @param string $table name of table.
     *
     * @return $this
     */
    public function from($table)
    {
        $this->from = "FROM " . $this->prefix . $table;
        return $this;
    }



    /**
     * Build the inner join part.
     *
     * @param string $table     name of table.
     * @param string $condition to join.
     *
     * @return $this
     */
    public function join($table, $condition)
    {

        return $this->createJoin($table, $condition, 'INNER');
    }



    /**
     * Build the right join part.
     *
     * @param string $table     name of table.
     * @param string $condition to join.
     *
     * @throws \Anax\Database\BuildException when dialect does not support.
     *
     * @return $this
     */
    public function rightJoin($table, $condition)
    {
        if ($this->dialect == 'sqlite') {
            throw new BuildException("SQLite does not support RIGHT JOIN");
        }

        return $this->createJoin($table, $condition, 'RIGHT OUTER');
    }



    /**
     * Build the left join part.
     *
     * @param string $table     name of table.
     * @param string $condition to join.
     *
     * @return $this
     */
    public function leftJoin($table, $condition)
    {
        return $this->createJoin($table, $condition, 'LEFT OUTER');
    }



    /**
     * Create a inner or outer join.
     *
     * @param string $table     name of table.
     * @param string $condition to join.
     * @param string $type      what type of join to create.
     *
     * @return void
     */
    private function createJoin($table, $condition, $type)
    {
        $this->join .= $type
            . " JOIN " . $this->prefix . $table
            . "\n\tON " . $condition . "\n";

        return $this;
    }



    /**
     * Build the where part.
     *
     * @param string $condition for building the where part of the query.
     *
     * @return $this
     */
    public function where($condition)
    {
        $this->where = "WHERE\n\t(" . $condition . ")";

        return $this;
    }



    /**
     * Build the where part with conditions.
     *
     * @param string $condition for building the where part of the query.
     *
     * @return $this
     */
    public function andWhere($condition)
    {
        $this->where .= "\n\tAND (" . $condition . ")";

        return $this;
    }



    /**
    * Build the group by part.
    *
    * @param string $condition for building the group by part of the query.
    *
    * @return $this
    */
    public function groupBy($condition)
    {
        $this->groupby = "GROUP BY " . $condition;

        return $this;
    }



    /**
    * Build the order by part.
    *
    * @param string $condition for building the where part of the query.
    *
    * @return $this
    */
    public function orderBy($condition)
    {
        $this->orderby = "ORDER BY " . $condition;

        return $this;
    }



    /**
     * Build the LIMIT by part.
     *
     * @param string $condition for building the LIMIT part of the query.
     *
     * @return $this
     */
    public function limit($condition)
    {
        $this->limit = "LIMIT \n\t" . intval($condition);

        return $this;
    }



    /**
     * Build the OFFSET by part.
     *
     * @param string $condition for building the OFFSET part of the query.
     *
     * @return $this
     */
    public function offset($condition)
    {
        $this->offset = "OFFSET \n\t" . intval($condition);

        return $this;
    }



    /**
     * Create a proper column value arrays from incoming $columns and $values.
     *
     * @param array       $columns
     * @param array|null  $values
     *
     * @return array that can be parsed with list($columns, $values)
     */
    public function mapColumnsWithValues($columns, $values)
    {
        // If $values is null, then use $columns to build it up
        if (is_null($values)) {

            if ($this->isAssoc($columns)) {

                // Incoming is associative array, split it up in two
                $values = array_values($columns);
                $columns = array_keys($columns);

            } else {

                // Create an array of '?' to match number of columns
                $max = count($columns);
                for ($i = 0; $i < $max; $i++) {
                    $values[] = '?';
                }
            }
        }

        return [$columns, $values];
    }



    /**
     * Utility to check if array is associative array.
     *
     * http://stackoverflow.com/questions/173400/php-arrays-a-good-way-to-check-if-an-array-is-associative-or-sequential/4254008#4254008
     *
     * @param array $array input array to check.
     *
     * @return boolean true if array is associative array with at least
     *                      one key, else false.
     *
     */
    private function isAssoc($array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }
}
