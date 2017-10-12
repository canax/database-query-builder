<?php

namespace Anax\Database;

/**
 * Mocking a User class to test the ActiveRecordModel.
 */
class Book extends ActiveRecordModel
{
    /**
     * @var string $tableName name of the database table.
     */
    public $tableName = "Book";

    /**
     * @var string $idColumn name of the id column in the database table.
     */
    protected $tableIdColumn = "idColumn";



    /**
     * Columns in the table.
     *
     * @var integer $id primary key auto incremented.
     */
    public $idColumn;
    public $title;
}
