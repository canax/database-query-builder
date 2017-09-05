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
    private function getProperties()
    {
        $properties = get_object_vars($this);
        unset($properties['tableName']);
        unset($properties['db']);
        unset($properties['di']);
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
        $this->checkDb();
        return $this->db->connect()
                        ->select()
                        ->from($this->tableName)
                        ->where("$column = ?")
                        ->execute([$value])
                        ->fetchInto($this);
    }



    /**
     * Find and return all.
     *
     * @return array
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
    private function create()
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
    private function update()
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
