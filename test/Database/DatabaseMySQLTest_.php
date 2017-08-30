<?php

namespace Anax\Database;

/**
* A testclass
*/
class DatabaseMySQLTest extends \PHPUnit_Framework_TestCase
{
    private $mysqlOptions = [
        // Set up details on how to connect to the database
        'dsn'     => "mysql:host=localhost;dbname=test;",
        'username'        => "test",
        'password'        => "test",
        'driver_options'  => [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"],
        'table_prefix'    => "",
        'verbose' => true,
    ];



    private $rows = [
        [22, "Twentytwo"],
        [44, "Fourtyfour"],
        [66, "Sixtysix"],
    ];



    private $db;



    /**
     * Testcase
     */
    protected function setUp()
    {
        $this->db = new Database();
        $this->db->setOptions($this->sqliteOptions);
        $this->db->connect();
    }



    /**
     * Testcase
     */
    public function testCreateObject()
    {
        $db = new Database();
        $this->assertInstanceOf("\Anax\Database\Database", $db);
    }



    /**
     * Testcase
     *
     * @expectedException \Anax\Database\Exception
     */
    public function testConnectGetException()
    {
        $db = new Database([]);
        $db->connect();
    }



    /**
     * Testcase
     *
     * @expectedException \PDOException
     */
    public function testConnectPDOException()
    {
        $db = new Database([
            "dsn" => "nono::",
            "debug_connect" => true
        ]);
        $db->connect();
    }



    /**
     * Testcase
     */
    public function testCreateTable()
    {
        $sql = <<<EOD
CREATE TABLE test (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    age INTEGER,
    text VARCHAR(20)
);
EOD;
        $this->db->execute($sql);
    }



    /**
     * Testcase
     */
    public function testInsertSingleRow()
    {
        $sql = <<<EOD
INSERT INTO test (age, text)
VALUES
    (?, ?)
;
EOD;
        $this->db->execute($sql, $this->rows[0]);
        $this->db->execute($sql, $this->rows[1]);
        $this->db->execute($sql, $this->rows[2]);
    }



    /**
     * Testcase
     */
    public function testUpdateRow()
    {
        /*$this->db->update(
            'test',
            [
                'age' => '?',
                'text' => '?',
            ],
            "id = ?"
        );*/
        //$id2 = $this->db->lastInsertId();
        //$this->db->execute(array_merge($this->rows[1], [$id2]));
    }



    /**
     * Testcase
     */
    public function testDropTable()
    {
        $sql = "DROP TABLE test;";
        $this->db->execute($sql);
    }
}
