<?php

namespace Anax\Database;

/**
* A testclass
*/
class ActiveRecordUsageTest extends \PHPUnit_Framework_TestCase
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
     * Save and find objects.
     */
    public function testSaveFind()
    {
        $user1 = new User();
        $user1->setDb(self::$db);
        $user1->acronym = "user1";
        $user1->password = "pass1";
        $user1->save();

        $user2 = new User();
        $user2->setDb(self::$db);
        $user2->acronym = "user2";
        $user2->password = "pass2";
        $user2->save();

        // find()
        $user3 = new User();
        $user3->setDb(self::$db);
        $user3->find("acronym", "user1");
        $this->assertEquals($user1, $user3);

        $user4 = new User();
        $user4->setDb(self::$db);
        $user4->find("acronym", "user2");
        $this->assertEquals($user2, $user4);

        // findById()
        $user5 = new User();
        $user5->setDb(self::$db);
        $user5->findById($user1->id);
        $this->assertEquals($user1, $user5);

        $user6 = new User();
        $user6->setDb(self::$db);
        $user6->findById($user2->id);
        $this->assertEquals($user2, $user6);

        // findWhere()
        $user7 = new User();
        $user7->setDb(self::$db);
        $user7->findWhere("id = ? AND acronym = ?", [$user1->id, "user1"]);
        $this->assertEquals($user1, $user7);

        $user8 = new User();
        $user8->setDb(self::$db);
        $user8->findWhere("id = ? AND acronym = ?", [$user2->id, "user2"]);
        $this->assertEquals($user2, $user8);
    }



    /**
     * Save and find all objects.
     */
    public function testSaveFindAll()
    {
        $user11 = new User();
        $user11->setDb(self::$db);
        $user11->acronym = "user11";
        $user11->password = "pass11";
        $user11->save();

        $user12 = new User();
        $user12->setDb(self::$db);
        $user12->acronym = "user12";
        $user12->password = "pass12";
        $user12->save();

        // findAll
        $user = new User();
        $user->setDb(self::$db);
        $users = $user->findAll();
        $count = count($users);
        $res = $users[$count - 2]->acronym;
        $exp = $user11->acronym;
        $this->assertEquals($exp, $res);
        $res = $users[$count - 1]->acronym;
        $exp = $user12->acronym;
        $this->assertEquals($exp, $res);
    }



    /**
     * Save and find all objects using findAllWhere.
     */
    public function testSaveFindAllWhere()
    {
        $user1 = new User();
        $user1->setDb(self::$db);
        $user1->acronym = "user21";
        $user1->password = "pass21";
        $user1->save();

        $user2 = new User();
        $user2->setDb(self::$db);
        $user2->acronym = "user22";
        $user2->password = "pass22";
        $user2->save();

        $user = new User();
        $user->setDb(self::$db);

        // Argument is single value
        $users = $user->findAllWhere(
            "acronym = ?",
            "user21"
        );
        $count = count($users);
        $this->assertEquals(1, $count);

        $res = $users[0]->acronym;
        $exp = $user1->acronym;
        $this->assertEquals($exp, $res);

        // Argument is array
        $users = $user->findAllWhere(
            "acronym IN (?, ?)",
            ["user21", "user22"]
        );
        $count = count($users);
        $this->assertEquals(2, $count);

        $res = $users[0]->acronym;
        $exp = $user1->acronym;
        $this->assertEquals($exp, $res);
        $res = $users[1]->acronym;
        $exp = $user2->acronym;
        $this->assertEquals($exp, $res);
    }



    /**
     * Save, update and find objects.
     */
    public function testSaveUpdateFind()
    {
        $user1 = new User();
        $user1->setDb(self::$db);
        $user1->acronym = "";
        $user1->password = "";
        $user1->save();
        $id1 = $user1->id;

        $user1->acronym = "user31";
        $user1->password = "pass31";
        $user1->save();

        $user2 = new User();
        $user2->setDb(self::$db);
        $user2->acronym = "";
        $user2->password = "";
        $user2->save();
        $id2 = $user2->id;

        $user2->acronym = "user32";
        $user2->password = "pass32";
        $user2->save();

        // findById()
        $user3 = new User();
        $user3->setDb(self::$db);
        $user3->findById($id1);
        $this->assertEquals($user1, $user3);

        $user4 = new User();
        $user4->setDb(self::$db);
        $user4->findById($id2);
        $this->assertEquals($user2, $user4);
    }
}
