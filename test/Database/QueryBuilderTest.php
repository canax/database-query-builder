<?php

namespace Anax\Database;

/**
* A testclass
*/
class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the mock
     */
    protected function setUp()
    {
        $this->query = new QueryBuilder();
        $this->query->setTablePrefix('mos_');
    }



    /**
     * Test
     */
    public function testCreateTable()
    {
        $this->query->createTable(
            'test',
            [
                'id'    => ['integer', 'primary key', 'not null'],
                'age'   => ['integer'],
                'text'  => ['varchar(20)'],
                'text2' => ['varchar(20)']
            ]
        );

        $res = $this->query->getSQL();
        $exp = <<<EOD
CREATE TABLE mos_test
(
\tid integer primary key not null,
\tage integer,
\ttext varchar(20),
\ttext2 varchar(20)
);

EOD;

        $this->assertEquals($res, $exp, "The SQL for create table does not match.");
    }



    /**
     * Test
     */
    public function testSelectWhereAndWhere()
    {
        $this->query->select("*")
                    ->from('test')
                    ->where('id = 1')
                    ->andWhere('name = mumin');

        $res = $this->query->getSQL();

        $exp = <<<EOD
SELECT
\t*
FROM mos_test
WHERE\n\t(id = 1)
\tAND (name = mumin)
;
EOD;

        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testSelectLimitOffset()
    {
        $this->query->select("*")
                   ->from('test')
                   ->limit('1')
                   ->offset('2');

        $res = $this->query->getSQL();

        $exp = <<<EOD
SELECT
\t*
FROM mos_test
LIMIT \n\t1
OFFSET \n\t2
;
EOD;

        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testDelete()
    {
        $this->query->deleteFrom('test');

        $res = $this->query->getSQL();

        $exp = "DELETE\nFROM mos_test\n;";

        $this->assertEquals($exp, $res);
    }



    /**
     * Test
     */
    public function testDeleteWhere()
    {
        $this->query->deleteFrom('test', "id = 2");

        $res = $this->query->getSQL();

        $exp = "DELETE\nFROM mos_test\nWHERE\n\t(id = 2)\n;";

        $this->assertEquals($exp, $res);
    }



    /**
     * Test
     */
    public function testDropTable()
    {
        $this->query->dropTable('test');

        $res = $this->query->getSQL();

        $exp = "DROP TABLE mos_test;\n";

        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testdropTableIfExists()
    {
        $this->query->dropTableIfExists('test');

        $res = $this->query->getSQL();

        $exp = "DROP TABLE IF EXISTS mos_test;\n";

        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testGroupBy()
    {
        $this->query->select()
                    ->from('test')
                    ->groupBy('test');


        $res = $this->query->getSQL();
        $exp = <<<EOD
SELECT
\t*
FROM mos_test
GROUP BY test
;
EOD;
        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testOrderBy()
    {
        $this->query->select()
                    ->from('test')
                    ->orderBy('test');


        $res = $this->query->getSQL();
        $exp = <<<EOD
SELECT
\t*
FROM mos_test
ORDER BY test
;
EOD;
        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testInsertSingleRow()
    {
        $this->query->insert(
            'test',
            [
                'id' => 2,
                'text' => "Mumintrollet",
                'text2' => "Mumindalen",
            ]
        );

        $res = $this->query->getSQL();

        $exp = <<<EOD
INSERT INTO mos_test
\t(id, text, text2)
\tVALUES
\t(2, 'Mumintrollet', 'Mumindalen');

EOD;
        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testInsertSingleRowTwoArray()
    {
        $this->query->insert(
            'test',
            ['id', 'text', 'text2'],
            [2, "Mumintrollet", "Mumindalen"]
        );

        $res = $this->query->getSQL();

        $exp = <<<EOD
INSERT INTO mos_test
\t(id, text, text2)
\tVALUES
\t(2, 'Mumintrollet', 'Mumindalen');

EOD;
        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testInsertSingleRowNoValues()
    {
        $this->query->insert(
            'test',
            ['id', 'text', 'text2']
        );

        $res = $this->query->getSQL();

        $exp = <<<EOD
INSERT INTO mos_test
\t(id, text, text2)
\tVALUES
\t(?, ?, ?);

EOD;
        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testUpdateTwoArrays()
    {
        $this->query->update(
            'test',
            ['age', 'text', 'text1'],
            [22, "Mumintrollet", "asd"]
        )->where("id = ?");

        $res = $this->query->getSQL();

        $exp = <<<EOD
UPDATE mos_test
SET
\tage = 22,
\ttext = 'Mumintrollet',
\ttext1 = 'asd'
WHERE
\t(id = ?)
;
EOD;
        $this->assertEquals($exp, $res);
    }



    /**
     * Test
     */
    public function testRightJoin()
    {
        $this->query->select("t1.*, t2.id AS id2, t3.id AS id3")
                    ->from('test AS t1')
                    ->rightJoin('test AS t2', 't1.id = t2.id')
                    ->rightJoin('test AS t3', 't1.id = t3.id');

        $res = $this->query->getSQL();

        $exp = <<<EOD
SELECT
\tt1.*, t2.id AS id2, t3.id AS id3
FROM mos_test AS t1
RIGHT OUTER JOIN mos_test AS t2
\tON t1.id = t2.id
RIGHT OUTER JOIN mos_test AS t3
\tON t1.id = t3.id
;
EOD;
        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testLeftJoin()
    {
        $this->query->select("t1.*, t2.id AS id2, t3.id AS id3")
                    ->from('test AS t1')
                    ->leftJoin('test AS t2', 't1.id = t2.id')
                    ->leftJoin('test AS t3', 't1.id = t3.id');

        $res = $this->query->getSQL();

        $exp = <<<EOD
SELECT
\tt1.*, t2.id AS id2, t3.id AS id3
FROM mos_test AS t1
LEFT OUTER JOIN mos_test AS t2
\tON t1.id = t2.id
LEFT OUTER JOIN mos_test AS t3
\tON t1.id = t3.id
;
EOD;
        $this->assertEquals($res, $exp);
    }



    /**
     * Test
     */
    public function testInnerJoin()
    {
        $this->query->select("t1.*, t2.id AS id2, t3.id AS id3")
                    ->from('test AS t1')
                    ->join('test AS t2', 't1.id = t2.id')
                    ->join('test AS t3', 't1.id = t3.id');

        $res = $this->query->getSQL();

        $exp = <<<EOD
SELECT
\tt1.*, t2.id AS id2, t3.id AS id3
FROM mos_test AS t1
INNER JOIN mos_test AS t2
\tON t1.id = t2.id
INNER JOIN mos_test AS t3
\tON t1.id = t3.id
;
EOD;
        $this->assertEquals($res, $exp);
    }
}
