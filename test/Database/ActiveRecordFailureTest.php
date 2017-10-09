<?php

namespace Anax\Database;

/**
* A testclass
*/
class ActiveRecordFailureTest extends \PHPUnit_Framework_TestCase
{
    public static $db;



    /**
     * Sets up for all test cases.
     */
    public static function setUpBeforeClass()
    {
        self::$db = new DatabaseQueryBuilder([
            "dsn" => "sqlite::memory:",
            "table_prefix" => "mos_",
            "debug_connect" => true,
        ]);

        self::$db->connect();
        self::$db->createTable(
            "User",
            [
                "id" => ["integer", "primary key", "not null"],
                "acronym" => ["integer"],
                "password" => ["string"],
            ]
        )->execute();
    }



    /**
     * Save an object without injecting the database object.
     *
     * @expectedException \Anax\Database\Exception\ActiveRecordException
     */
    public function testSaveWithoutInjectingDatabase()
    {
        $user1 = new User();
        $user1->save();
    }
}
