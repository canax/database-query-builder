<?php

namespace Anax\Database;

use \Anax\Database\DatabaseQueryBuilder;

/**
 * An implementation of the Active Record pattern to be used as
 * base class for database driven models.
 */
class ActiveRecordModel
{
    /**
     * @var string TABLE_NAME name of the database table.
     */
    protected $tableName = null;



    /**
     * Constructor.
     *
     * @param DatabaseQueryBuilder $db as database access object.
     */
    public function __construct(DatabaseQueryBuilder $db)
    {
        $this->db = $db;
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
     * Set object properties.
     *
     * @param array $properties with properties to set.
     *
     * @return void
     */
    // private function setProperties($properties)
    // {
    //     if (!empty($properties)) {
    //         foreach ($properties as $key => $val) {
    //             $this->$key = $val;
    //         }
    //     }
    // }



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
        $db = $this->db;
        return $db->connect()
                  ->select()
                  ->from($this->tableName)
                  ->execute()
                  ->fetchAllClass(__CLASS__);
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
        $db = $this->db;
        $properties = $this->getProperties();
        unset($properties['id']);
        $columns = array_keys($properties);
        $values  = array_values($properties);

        $db->connect()
           ->insert($this->tableName, $columns)
           ->execute($values);

        $this->id = $this->db->lastInsertId();
    }



    /**
     * Update row.
     *
     * @param array $values key/values to save.
     *
     * @return void
     */
    private function update($values)
    {
        $db = $this->db;
        $properties = $this->getProperties();
        unset($properties['id']);
        $columns = array_keys($properties);
        $values  = array_values($properties);
        $values[] = $this->id;

        $db->connect()
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
        $db = $this->db;
        $id = $id ?: $this->id;

        $db->connect()
           ->deleteFrom(self::CLASS_NAME)
           ->where("id = ?")
           ->execute([$id]);

        $this->id = null;
    }
}
