<?php // $Id: PageListTest.php 7181 2009-10-05 14:25:48Z vargenau $

require_once 'lib/PageList.php';
require_once 'PHPUnit.php';

class PageListTest extends phpwiki_TestCase {

    function testPageList() {
        // Completely empty PageList
        $columns = "";
        $exclude = "";
        $options = "";
        $pl = new PageList($columns, $exclude, $options);
        $this->assertTrue($pl->isEmpty(), "empty");
        $this->assertEquals(0, $pl->getTotal(), "count 0");
        $cap = $pl->getCaption();
        $this->assertTrue(empty($cap), "empty caption");

        // PageList sorting
        $columns[] = 'pagename';
        $pl = new PageList($columns, $exclude, $options);
        //global $request;
        $pl->addPage("foo");
        $pl->addPage("blarg");
        $this->assertEquals(2, $pl->getTotal(), "count 2");
        //print_r($pl->getContent());
    }
}


?>
