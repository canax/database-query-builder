<?php

namespace Anax\Database;

/**
* A testclass
*/
class ActiveRecordSaveWhereTest extends \PHPUnit_Framework_TestCase
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
     * Save and find objects using custom where-statements.
     */
    public function testSaveFindWhere()
    {
        $user1 = new User();
        $user1->setDb(self::$db);
        $user1->acronym = "user";
        $user1->password = "pass";
        $user1->save();

        // findWhere()
        $user2 = new User();
        $user2->setDb(self::$db);
        $user2->findWhere("acronym = ? AND password = ?", [$user1->acronym, $user1->password]);
        $this->assertEquals($user1, $user2);

        // saveWhere()
        $user2->acronym = "user1";
        $user2->password = "pass1";
        $user2->saveWhere("acronym = ? AND password = ?", [$user1->acronym, $user1->password]);

        // findWhere() again
        $user3 = new User();
        $user3->setDb(self::$db);
        $user3->findWhere("acronym = ? AND password = ?", [$user2->acronym, $user2->password]);
        $user1->findById();
        $this->assertEquals($user1, $user3);

        // deleteWhere()
        $user4 = new User();
        $user4->setDb(self::$db);
        $user4->findWhere("acronym = ? AND password = ?", [$user1->acronym, $user1->password]);
        $user4->deleteWhere("acronym = ? AND password = ?", [$user4->acronym, $user4->password]);
        $this->assertEquals($user1->id, $user4->id);
        $user4->delete();
        $this->assertNull($user4->id);
    }
}
