<?php

namespace Anax\Database;

/**
* A testclass
*/
class ActiveRecordUsageIdColumnTest extends \PHPUnit_Framework_TestCase
{
    public static $db;
    public static $idColumn = "idColumn";



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
            "Book",
            [
                self::$idColumn => ["integer", "primary key", "not null"],
                "title" => ["string"],
            ]
        )->execute();
    }



    /**
     * Save and find objects.
     */
    public function testSaveFind()
    {
        // Create a book
        $book1 = new Book();
        $book1->setDb(self::$db);
        $book1->title = "";
        $book1->save();
        $id1 = $book1->{self::$idColumn};

        // Load it by findById()
        $book2 = new Book();
        $book2->setDb(self::$db);
        $book2->findById($id1);
        $this->assertEquals($book1, $book2);

        // Update the first book
        $book1->title = "Book1";
        $book1->save();

        // Load it by findById() and check its updated
        $book2 = new Book();
        $book2->setDb(self::$db);
        $book2->findById($id1);
        $this->assertEquals($book1, $book2);

        // Delete the first book
        $book1->delete();

        // Load it by findById() and check its deleted
        $book2 = new Book();
        $book2->setDb(self::$db);
        $book2->findById($id1);
        $this->assertNull($book1->{self::$idColumn});
    }
}
