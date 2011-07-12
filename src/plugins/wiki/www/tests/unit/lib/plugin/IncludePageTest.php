<?php // $Id: IncludePageTest.php 7181 2009-10-05 14:25:48Z vargenau $

require_once 'lib/WikiPlugin.php';
require_once 'lib/plugin/IncludePage.php';
require_once 'PHPUnit.php';

class IncludePageTest extends phpwiki_TestCase {

    function _ensure_loaded($pagename) {
        global $request;
        $dbi = $request->getDbh();
	if (! $dbi->isWikiPage($pagename)) {
	    require_once 'lib/loadsave.php';

	    $request->setArg('source', FindFile('pgsrc/'.urlencode($pagename)));
	    $request->setArg('overwrite', 1);
	    LoadAny($request, $request->getArg('source'));
	    $request->setArg('source', false);
	}
    }

    // extract Synopsis
    function testOldTextFormattingRules() {
        global $request;

	$pagename = 'Help/OldTextFormattingRules';
    	$this->_ensure_loaded($pagename);
        $dbi = $request->getDbh();
        $p = $dbi->getPage($pagename);
	$r = $p->getCurrentRevision();
        $c = $r->getContent();
	$section = extractSection('Synopsis', $c, $pagename, 1);
	$this->assertTrue(strstr(join("", $section), "OldTextFormattingRules%%%"));
    }

    // extract Synopsis
    function testTextFormattingRules() {
        global $request;

	$pagename = 'Help/TextFormattingRules';
    	$this->_ensure_loaded($pagename);
        $dbi = $request->getDbh();
        $p = $dbi->getPage($pagename);
	$r = $p->getCurrentRevision();
        $c = $r->getContent();
	$section = extractSection('Synopsis', $c, $pagename, 1);
	$this->assertTrue(strstr(join("", $section), "TextFormattingRules%%%"));
    }

    /**
     * Test the plugin with the typical editpage template call.
     */
    function testIncludePageSynopsis() {
        global $request;
	$pagename = 'Help/TextFormattingRules';

        $lp = new WikiPlugin_IncludePage();
        $this->assertEquals("IncludePage", $lp->getName());
        $dbi = $request->getDbh();
        $result = $lp->run($dbi, "page=$pagename section=Synopsis quiet=1",
			   $request, "IncludePage");
        $this->assertType('object', $result, 'isa HtmlElement');
	//TODO: check content for found and extracted section
    }
}

?>
