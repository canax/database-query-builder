<?php

namespace Anax\Database;

use \Anax\Database\DatabaseQueryBuilder;
use \Anax\Database\Exception\ActiveRecordException;

/**
 * An implementation of the Active Record pattern to be used as
 * base class for database driven models.
 */
class ActiveRecordModel
{
    /**
     * @var DatabaseQueryBuilder $db the object for persistent
     *                               storage.
     */
    protected $db = null;

    /**
     * @var string $tableName name of the database table.
     */
    protected $tableName = null;

    /**
     * @var string $idColumn name of the id column in the database table.
     */
    protected $idColumn = "id";



    /**
     * Set the database object to use for accessing storage.
     *
     * @param DatabaseQueryBuilder $db as database access object.
     *
     * @return void
     */
    public function setDb(DatabaseQueryBuilder $db)
    {
        $this->db = $db;
    }



    /**
     * Check if database is injected or throw an exception.
     *
     * @throws ActiveRecordException when database is not set.
     *
     * @return void
     */
    protected function checkDb()
    {
        if (!$this->db) {
            throw new ActiveRecordException("Missing \$db, did you forget to inject/set is?");
        }
    }



    /**
     * Get essential object properties.
     *
     * @return array with object properties.
     */
    protected function getProperties()
    {
        $properties = get_object_vars($this);
        unset(
            $properties['tableName'],
            $properties['db'],
            $properties['di'],
            $properties['idColumn']
        );
        return $properties;
    }



    /**
     * Find and return first object found by search criteria and use
     * its data to populate this instance.
     *
     * @param string $column to use in where statement.
     * @param mixed  $value  to use in where statement.
     *
     * @return this
     */
    public function find($column, $value)
    {
        return $this->findWhere("$column = ?", $value);
    }



    /**
     * Find and return first object by its idColumn and use
     * its data to populate this instance.
     *
     * @param string $column to use in where statement.
     * @param mixed  $value  to use in where statement.
     *
     * @return this
     */
    public function findById($value)
    {
        return $this->findWhere("{$this->idColumn} = ?", $value);
    }



    /**
     * Find and return first object found by search criteria and use
     * its data to populate this instance.
     *
     * The search criteria `$where` of can be set up like this:
     *  `id = ?`
     *  `id1 = ? and id2 = ?`
     *
     * The `$value` can be a single value or an array of values.
     *
     * @param string $where to use in where statement.
     * @param mixed  $value to use in where statement.
     *
     * @return this
     */
    public function findWhere($where, $value)
    {
        $this->checkDb();
        $params = is_array($value) ? $value : [$value];
        return $this->db->connect()
                        ->select()
                        ->from($this->tableName)
                        ->where($where)
                        ->execute($params)
                        ->fetchInto($this);
    }



    /**
     * Find and return all.
     *
     * @return array of object of this class
     */
    public function findAll()
    {
        $this->checkDb();
        return $this->db->connect()
                        ->select()
                        ->from($this->tableName)
                        ->execute()
                        ->fetchAllClass(get_class($this));
    }



    /**
     * Find and return all matching a search criteria of
     * for example `id = ?` or `id IN [?, ?]`.
     *
     * @param string $where to use in where statement.
     * @param mixed  $value to use in where statement.
     *
     * @return array of object of this class
     */
    public function findAllWhere($where, $value)
    {
        $this->checkDb();
        $params = is_array($value) ? $value : [$value];
        return $this->db->connect()
                        ->select()
                        ->from($this->tableName)
                        ->where($where)
                        ->execute($params)
                        ->fetchAllClass(get_class($this));
    }



    /**
     * Save current object/row, insert if id is missing and do an
     * update if the id exists.
     *
     * @return void
     */
    public function save()
    {
        if (isset($this->id)) {
            return $this->update();
        }

        return $this->create();
    }



    /**
     * Create new row.
     *
     * @return void
     */
    protected function create()
    {
        $this->checkDb();
        $properties = $this->getProperties();
        unset($properties['id']);
        $columns = array_keys($properties);
        $values  = array_values($properties);

        $this->db->connect()
                 ->insert($this->tableName, $columns)
                 ->execute($values);

        $this->id = $this->db->lastInsertId();
    }



    /**
     * Update row.
     *
     * @return void
     */
    protected function update()
    {
        $this->checkDb();
        $properties = $this->getProperties();
        unset($properties['id']);
        $columns = array_keys($properties);
        $values  = array_values($properties);
        $values[] = $this->id;

        $this->db->connect()
                 ->update($this->tableName, $columns)
                 ->where("id = ?")
                 ->execute($values);
    }



    /**
     * Delete row.
     *
     * @param integer $id to delete or use $this->id as default.
     *
     * @return void
     */
    public function delete($id = null)
    {
        $this->checkDb();
        $id = $id ?: $this->id;

        $this->db->connect()
                 ->deleteFrom($this->tableName)
                 ->where("id = ?")
                 ->execute([$id]);

        $this->id = null;
    }
}
