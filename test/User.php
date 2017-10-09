<?php

namespace Anax\Database;

/**
 * Mocking a User class to test the ActiveRecordModel.
 */
class User extends ActiveRecordModel
{
    /**
     * @var string $tableName name of the database table.
     */
    public $tableName = "User";



    /**
     * Columns in the table.
     *
     * @var integer $id primary key auto incremented.
     */
    public $id;
    public $acronym;
    public $password;
}
