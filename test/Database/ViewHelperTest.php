<?php

namespace Anax\View;

/**
 * Views.
 */
class ViewHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider for test values
     *
     * @return array
     */
    public function providerClassList()
    {
        return [
            [
                [ null ],
                "class=\"\"",
            ],
            [
                [ "a" ],
                "class=\"a\"",
            ],
            [
                [ "a", "b" ],
                "class=\"a b\"",
            ],
            [
                [ "a", "b", ["c", "d"] ],
                "class=\"a b c d\"",
            ],
            [
                [ [], "a" ],
                "class=\"a\"",
            ],
        ];
    }



    /**
     * Test
     *
     * @return void
     *
     * @dataProvider providerClassList
     */
    public function testClassList($args, $exp)
    {
        $view = new MockViewHelper();
        $res = $view->classList(...$args);
        $this->assertEquals($exp, $res, "Classlist did not match.");
    }
}
