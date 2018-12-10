<?php

namespace Anax\DatabaseQueryBuilder;

use PHPUnit\Framework\TestCase;

/**
 * Test the query builder together with SQLite.
 */
class QueryBuilderWithSQLiteTest extends TestCase
{
    /** Database $db the database object. */
    private $db;



    /**
     * Setup before each test case, a table with some rows.
     */
    protected function setUp()
    {
        $this->db = new DatabaseQueryBuilder([
            "dsn" => "sqlite::memory:",
        ]);
        $this->db->setDefaultsFromConfiguration();

        // Connect
        $db = $this->db->connect();
        $this->assertInstanceOf(DatabaseQueryBuilder::class, $db);

        // Create a table
        $res = $this->db->createTable(
            'user',
            [
                'id'    => ['integer', 'primary key', 'not null'],
                'age'   => ['integer'],
                'name'  => ['varchar(10)']
            ]
        )->execute();

        $this->assertInstanceOf(DatabaseQueryBuilder::class, $res);

        // Add rows to table and do assertions
        // Add row 1
        $res = $this->db->insert(
            "user",
            [
                "age" => 3,
                "name" => "three",
            ]
        )->execute();

        $last = $this->db->lastInsertId();
        $this->assertEquals(1, $last);
        
        $rows = $this->db->rowCount();
        $this->assertEquals(1, $rows);
        

        // Add row 2
        $res = $this->db->insert(
            "user",
            ["age", "name"],
            [7, "seven"]
        )->execute();

        $last = $this->db->lastInsertId();
        $this->assertEquals(2, $last);
        
        $rows = $this->db->rowCount();
        $this->assertEquals(1, $rows);

        // Add row 3
        $res = $this->db->insert(
            "user",
            ["age", "name"],
            [9, "nine"]
        )->execute();

        $last = $this->db->lastInsertId();
        $this->assertEquals(3, $last);
        
        $rows = $this->db->rowCount();
        $this->assertEquals(1, $rows);
    }



    /**
     * Execute a query and fetch a single row using default fetch.
     */
    public function testFetchOneRow()
    {
        $res = $this->db->select("*")
                        ->from("user")
                        ->where("id = 1")
                        ->execute()
                        ->fetch();
    
        $this->assertInstanceOf(\stdClass::class, $res);
        $this->assertEquals(1, $res->id);
        $this->assertEquals(3, $res->age);
        $this->assertEquals("three", $res->name);
    }




    /**
     * Execute a query without a match, and fetch a single row using default
     * fetch.
     */
    public function testFetchOneRowNoResult()
    {
        $res = $this->db->select("*")
                        ->from("user")
                        ->where("id = 99")
                        ->execute()
                        ->fetch();
        $this->assertNull($res);
    }




//     /**
//      * Test
//      */
//     public function testSelectWhereAndWhere()
//     {
//         $this->query->select("*")
//                     ->from('test')
//                     ->where('id = 1')
//                     ->andWhere('name = mumin');
//
//         $res = $this->query->getSQL();
//
//         $exp = <<<EOD
// SELECT
// \t*
// FROM mos_test
// WHERE\n\t(id = 1)
// \tAND (name = mumin)
// ;
// EOD;
//
//         $this->assertEquals($res, $exp);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testSelectLimitOffset()
//     {
//         $this->query->select("*")
//                    ->from('test')
//                    ->limit('1')
//                    ->offset('2');
//
//         $res = $this->query->getSQL();
//
//         $exp = <<<EOD
// SELECT
// \t*
// FROM mos_test
// LIMIT \n\t1
// OFFSET \n\t2
// ;
// EOD;
//
//         $this->assertEquals($res, $exp);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testDelete()
//     {
//         $this->query->deleteFrom('test');
//
//         $res = $this->query->getSQL();
//
//         $exp = "DELETE\nFROM mos_test\n;";
//
//         $this->assertEquals($exp, $res);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testDeleteWhere()
//     {
//         $this->query->deleteFrom('test', "id = 2");
//
//         $res = $this->query->getSQL();
//
//         $exp = "DELETE\nFROM mos_test\nWHERE\n\t(id = 2)\n;";
//
//         $this->assertEquals($exp, $res);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testDropTable()
//     {
//         $this->query->dropTable('test');
//
//         $res = $this->query->getSQL();
//
//         $exp = "DROP TABLE mos_test;\n";
//
//         $this->assertEquals($res, $exp);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testdropTableIfExists()
//     {
//         $this->query->dropTableIfExists('test');
//
//         $res = $this->query->getSQL();
//
//         $exp = "DROP TABLE IF EXISTS mos_test;\n";
//
//         $this->assertEquals($res, $exp);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testGroupBy()
//     {
//         $this->query->select()
//                     ->from('test')
//                     ->groupBy('test');
//
//
//         $res = $this->query->getSQL();
//         $exp = <<<EOD
// SELECT
// \t*
// FROM mos_test
// GROUP BY test
// ;
// EOD;
//         $this->assertEquals($res, $exp);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testOrderBy()
//     {
//         $this->query->select()
//                     ->from('test')
//                     ->orderBy('test');
//
//
//         $res = $this->query->getSQL();
//         $exp = <<<EOD
// SELECT
// \t*
// FROM mos_test
// ORDER BY test
// ;
// EOD;
//         $this->assertEquals($res, $exp);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testUpdateTwoArrays()
//     {
//         $this->query->update(
//             'test',
//             ['age', 'text', 'text1'],
//             [22, "Mumintrollet", "asd"]
//         )->where("id = ?");
//
//         $res = $this->query->getSQL();
//
//         $exp = <<<EOD
// UPDATE mos_test
// SET
// \tage = 22,
// \ttext = 'Mumintrollet',
// \ttext1 = 'asd'
// WHERE
// \t(id = ?)
// ;
// EOD;
//         $this->assertEquals($exp, $res);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testRightJoin()
//     {
//         $this->query->select("t1.*, t2.id AS id2, t3.id AS id3")
//                     ->from('test AS t1')
//                     ->rightJoin('test AS t2', 't1.id = t2.id')
//                     ->rightJoin('test AS t3', 't1.id = t3.id');
//
//         $res = $this->query->getSQL();
//
//         $exp = <<<EOD
// SELECT
// \tt1.*, t2.id AS id2, t3.id AS id3
// FROM mos_test AS t1
// RIGHT OUTER JOIN mos_test AS t2
// \tON t1.id = t2.id
// RIGHT OUTER JOIN mos_test AS t3
// \tON t1.id = t3.id
// ;
// EOD;
//         $this->assertEquals($res, $exp);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testLeftJoin()
//     {
//         $this->query->select("t1.*, t2.id AS id2, t3.id AS id3")
//                     ->from('test AS t1')
//                     ->leftJoin('test AS t2', 't1.id = t2.id')
//                     ->leftJoin('test AS t3', 't1.id = t3.id');
//
//         $res = $this->query->getSQL();
//
//         $exp = <<<EOD
// SELECT
// \tt1.*, t2.id AS id2, t3.id AS id3
// FROM mos_test AS t1
// LEFT OUTER JOIN mos_test AS t2
// \tON t1.id = t2.id
// LEFT OUTER JOIN mos_test AS t3
// \tON t1.id = t3.id
// ;
// EOD;
//         $this->assertEquals($res, $exp);
//     }
//
//
//
//     /**
//      * Test
//      */
//     public function testInnerJoin()
//     {
//         $this->query->select("t1.*, t2.id AS id2, t3.id AS id3")
//                     ->from('test AS t1')
//                     ->join('test AS t2', 't1.id = t2.id')
//                     ->join('test AS t3', 't1.id = t3.id');
//
//         $res = $this->query->getSQL();
//
//         $exp = <<<EOD
// SELECT
// \tt1.*, t2.id AS id2, t3.id AS id3
// FROM mos_test AS t1
// INNER JOIN mos_test AS t2
// \tON t1.id = t2.id
// INNER JOIN mos_test AS t3
// \tON t1.id = t3.id
// ;
// EOD;
//         $this->assertEquals($res, $exp);
//     }
}
