<?php
/**
 * 1st important test: Check if all pgsrc files import without failure
 */

require_once 'lib/loadsave.php';
require_once 'PHPUnit.php';

class SetupWiki extends phpwiki_TestCase {

    function _loadPage($pagename) {
        global $request;
        $dbi = $request->getDbh();
        $dbi->purgePage($pagename);
        $this->assertFalse($dbi->isWikiPage($pagename));

        $request->setArg('source', FindFile('pgsrc/'.urlencode($pagename)));
        $request->setArg('overwrite', 1);
        LoadAny($request, $request->getArg('source'));
        $request->setArg('source', false);
        $this->assertTrue($dbi->isWikiPage($pagename));
    }
    
    /* PCRE memory problem (crash) with such big pages and anchored blocks */
    function testOldMarkupTestPage() {
    	$this->_loadPage('OldMarkupTestPage');
    }
    
    /* ADODB set_links _id_cache error: IncludePagePlugin => HomePage */
    function testIncludePagePlugin() {
    	$this->_loadPage('IncludePagePlugin');
    }
    
    function testSetupWiki() {
        global $request;

        purge_testbox();
        
        $dbi = $request->getDbh();
        $dbi->purgePage('HomePage'); // possibly in cache
        $this->assertFalse($dbi->isWikiPage('HomePage'));

        $request->setArg('source', FindFile('pgsrc'));
        $request->setArg('overwrite', 1);
        LoadAny($request, $request->getArg('source'));
        $request->setArg('source', false);
        $request->setArg('overwrite', false);
        
        $this->assertTrue($dbi->isWikiPage('HomePage'));
    }
}

?>