<?php

namespace Anax\Database;

/**
* A testclass
*/
class DatabaseQueryBuilderSQLiteTest extends \PHPUnit_Framework_TestCase
{
    static $db;



    /**
     * Sets up the mock
     */
    public static function setUpBeforeClass()
    {
        self::$db = new DatabaseQueryBuilder([
            "dsn" => "sqlite::memory:",
            "table_prefix" => "mos_",
            "debug_connect" => true,
        ]);
        self::$db->connect();
    }



    /**
     * Test
     */
    public function testConfigure()
    {
        $obj = self::$db->configure([]);
        $this->assertEquals($obj, self::$db);
    }



    /**
     * Test
     */
    public function testCreateTable()
    {
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
     * Test
     */
    public function testInsert()
    {
        self::$db->insert(
            "User",
            [
                "acronym" => "doe",
                "password" => "pwd",
            ]
        )->execute();
        
        $res = self::$db->rowCount();
        $exp = 1;
        $this->assertEquals($res, $exp);

        $res = self::$db->lastInsertId();
        $exp = 1;
        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testSelectFromWhere()
    {
        $obj = self::$db->select("*")
                        ->from("User")
                        ->where("acronym = ?")
                        ->executeFetch(["doe"]);

        $res = $obj->password;
        $exp = "pwd";
        $this->assertEquals($exp, $res);
    }
}
