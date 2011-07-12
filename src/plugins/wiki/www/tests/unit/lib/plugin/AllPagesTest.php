<?php // $Id: AllPagesTest.php 7181 2009-10-05 14:25:48Z vargenau $

require_once 'lib/WikiPlugin.php';
require_once 'lib/plugin/AllPages.php';
require_once 'PHPUnit.php';

class AllPagesTest extends phpwiki_TestCase {

    /**
     * Test that we can instantiate and run AllPages plugin without error.
     */
    function testAllPages() {
        global $request;

        $lp = new WikiPlugin_AllPages();
        $this->assertEquals("AllPages", $lp->getName());
        $args = "";
        $this->assertType('object', $request->_dbi, 'isa WikiDB');
/*
*/
        $result = $lp->run($request->_dbi, $args, $request, "AllPages");
        $this->assertType('object', $result, 'isa PageList');
        $this->assertType('object', $request->_dbi, 'isa WikiDB');
        if (!isa($request->_dbi, "WikiDB")) {
            // very very strange bug
            $request->_dbi = WikiDB::open($GLOBALS['DBParams']);
            if (!isa($request->_dbi, "WikiDB")) {
                trigger_error("strange php bug\n",E_USER_WARNING);
                return;
            }
        }
        $xml = $result->asXml();
        $this->assertType('object', $result, 'isa XmlContent');
        //$xml->asString();
        //$this->assertType('object', $result, 'isa XmlContent');
    }
}


?>
