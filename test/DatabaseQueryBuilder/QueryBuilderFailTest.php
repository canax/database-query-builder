<?php

namespace Anax\DatabaseQueryBuilder;

use PHPUnit\Framework\TestCase;

/**
* Negative tests for query builder.
*/
class QueryBuilderFailTest extends TestCase
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
     *
     * @expectedException \Anax\DatabaseQueryBuilder\Exception\BuildException
     */
    public function testInsertWrongData()
    {
        $this->query->insert(
            'test',
            ['id', 'text', 'text2'],
            [2, "Mumintrollet"]
        );
    }



    /**
     * Test
     *
     * @expectedException \Anax\DatabaseQueryBuilder\Exception\BuildException
     */
    public function testUpdateWrongData()
    {
        $this->query->update(
            'test',
            ['age', 'text', 'text1'],
            [22, "Mumindalen"],
            "id = 2"
        );
    }
}
