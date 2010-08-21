<?php // $Id: ListPagesTest.php 7181 2009-10-05 14:25:48Z vargenau $

require_once 'lib/WikiPlugin.php';
require_once 'lib/plugin/ListPages.php';
require_once 'PHPUnit.php';

class ListPagesTest extends phpwiki_TestCase {

    /**
     * Test that we can instantiate and run ListPages plugin without error.
     */
    function testListPages() {
        global $request;

        $lp = new WikiPlugin_ListPages();
        $this->assertEquals("ListPages", $lp->getName());
        $dbi = $request->getDbh();
        $result = $lp->run($dbi, "pages=foo", $request, "ListPages");
        $this->assertType('object',$result,'isa PageList');
        $this->assertEquals(1, $result->getTotal());
        //$this->assertEquals(3, $result->_maxlen);
    }
}

?>
