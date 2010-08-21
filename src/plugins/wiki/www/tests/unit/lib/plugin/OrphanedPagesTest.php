<?php // $Id: OrphanedPagesTest.php 7181 2009-10-05 14:25:48Z vargenau $

require_once 'lib/WikiPlugin.php';
require_once 'lib/plugin/OrphanedPages.php';
require_once 'PHPUnit.php';

class OrphanedPagesTest extends phpwiki_TestCase {
    /**
     * Test that we can instantiate and run OrphanedPages plugin without error.
     */
    function testOrphanedPages() {
        global $request;

        $lp = new WikiPlugin_OrphanedPages();
        $this->assertEquals("OrphanedPages", $lp->getName());
        $basepage = "";
        $args = "";
        $result = $lp->run($request->getDbh(), $args, $request, $basepage);
        $this->assertType('object',$result,'isa PageList');
    }
}


?>
