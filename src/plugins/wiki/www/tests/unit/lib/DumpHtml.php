<?php // $Id: DumpHtml.php 7181 2009-10-05 14:25:48Z vargenau $
/**
 * 2nd important test:
 *   Check if all standard pages can be rendered (dumped)
 */

require_once 'lib/loadsave.php';
require_once 'PHPUnit.php';

class DumpHtml extends phpwiki_TestCase {

    function _dumpPage($pagename) {
        global $request, $cur_dir;

        $request->setArg('directory',$cur_dir.'/.dumphtml');
        $request->setArg('pages', $pagename);
        $request->setArg('action', 'dumphtml');
        unlink($cur_dir."/.dumphtml/$pagename.html");
        DumpHtmlToDir($request);
        $this->assertTrue(file_exists($cur_dir."/.dumphtml/$pagename.html"));
    }

    /* at first dump some problematic pages */
    function test01RateIt() {
        $this->_dumpPage('RateIt');
    }
    function test02OrphanedPages() {
        $this->_dumpPage('OrphanedPages');
    }
    function test03OldTextFormattingRules() {
        $this->_dumpPage('OldTextFormattingRules');
    }
    function test04LinkDatabase() {
        $this->_dumpPage('LinkDatabase');
    }

    /* finally all. esp. with start_debug=1 this needs some time... */
    function test99DumpHtml() {
        global $request, $cur_dir;

        $request->setArg('directory', $cur_dir.'/.dumphtml');
        purge_dir($cur_dir."/.dumphtml");
        purge_dir($cur_dir."/.dumphtml/images");
        $request->setArg('pagename', _("PhpWikiAdministration"));
        $request->setArg('pages', '');
        //FIXME: LinkDatabase doesn't work for DumpHtmlToDir
        //$request->setArg('exclude','LinkDatabase');  // this does not work with format=text => exit
        DumpHtmlToDir($request);
        $this->assertTrue(file_exists($cur_dir."/.dumphtml/".HOME_PAGE.".html"));
    }

}

?>
