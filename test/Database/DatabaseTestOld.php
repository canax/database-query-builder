<?php

namespace Anax\Database;

/**
* A testclass
*/
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /*
    private $mysqlOptions = [
        // Set up details on how to connect to the database
        'dsn'     => "mysql:host=localhost;dbname=test;",
        'username'        => "test",
        'password'        => "test",
        'driver_options'  => [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"],
        'table_prefix'    => "",
        'verbose' => true,
    ];
    */


    private $sqliteOptions = [
        'dsn' => "sqlite:memory::",
        "verbose" => false
    ];



    private $rows = [
        [22, "Mumintrollet"],
        [44, "Mumindalen"],
        [66, "Lilla My"],
    ];



    private $db;
    //private $selectSQL;



    /**
     * Testcase
     */
    protected function setUp()
    {
        $this->db = new Database();
        $this->db->setOptions($this->sqliteOptions);
        //$this->db->setOptions($this->mysqlOptions);
        $this->db->connect();
        //$this->selectSQL = "SELECT id, age, text FROM test;";
        /*
        $this->db->select("id, age, text")
                                    ->from('test')->getSQL();
        */
    }



    /**
     * Testcase
     */
    public function testCreateObject()
    {
        $this->assertInstanceOf("\Anax\Database\Database", $this->db);
    }



    /**
     * Testcase
     */
    public function testConnect()
    {
        $this->db->connect();
    }



    /**
     * Testcase
     */
    public function testConnectGetException()
    {
        $this->db->setOptions([]);
        try {
            $this->db->connect();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }



    /**
     * Testcase
     */
    public function testCreateTable()
    {
        /*
        $this->db->createTable(
            'test',
            [
                'id'    => ['integer', 'auto_increment', 'primary key', 'not null'],
                'age'   => ['integer'],
                'text'  => ['varchar(20)'],
            ]
        );
        */
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
        /*
        $this->db->insert(
            'test',
            [
                'age'  => $this->rows[0][0],
                'text' => $this->rows[0][1],
            ]
        );
        */
        $sql = <<<EOD
INSERT INTO test (age, text)
VALUES
    (?, ?)
;
EOD;
        $this->db->execute($sql, $this->rows[0]);
    }



    /**
     * Testcase
     */
    /*public function testInsertAsArray()
    {
        $this->db->insert(
            'test',
            ['age', 'text'],
            $this->rows[0]
        );

        $this->db->execute();
    }*/



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
    public function testLimit()
    {
        $sql = "SELECT * FROM test LIMIT ?, ?";
        $param = [0, 2];

        $res = $this->db->executeFetchAll($sql, $param);
        $res;
        //$res = $this->db->executeFetchAll($sql);
        //print_r($this->db->dump());
        //var_dump($res);
    }



    /**
     * Testcase
     */
    public function testDropTable()
    {
        //$this->db->dropTable('test');
        $sql = "DROP TABLE test;";
        $this->db->execute($sql);
    }
}
