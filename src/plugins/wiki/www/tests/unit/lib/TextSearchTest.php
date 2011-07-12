<?php // $Id: TextSearchTest.php 7181 2009-10-05 14:25:48Z vargenau $

require_once 'lib/TextSearchQuery.php';
require_once 'PHPUnit.php';

class TextSearchTest extends phpwiki_TestCase {

    function testTitleSearch() {
        global $request;
	// find subpages
	$pagename = "PgsrcTranslation";
        $query = new TextSearchQuery($pagename . SUBPAGE_SEPARATOR . '*', true, 'glob');
	$sortby = false; $limit = 20; $exclude = "";
        $dbi = $request->getDbh();
        $subpages = $dbi->titleSearch($query, $sortby, $limit, $exclude);

	$result = array();
	while ($page = $subpages->next())
	    $result[] = $page->getName();
        $this->assertTrue(count($result) > 0, "glob count > 0");

	// apply limit
	$sortby = false; $limit = 5; $exclude = "";
        $subpages = $dbi->titleSearch($query, $sortby, $limit, $exclude);

	// don't trust count() with limit
	$this->assertTrue($subpages->count() > 0 and $subpages->count() <= 7,
			  "0 < count() <= 7");
	$result = array();
	// but the iterator should limit
	while ($page = $subpages->next())
	    $result[] = $page->getName();
	$this->assertEquals(5, count($result), "limit 5");

    }

    function testFulltextSearch() {
        global $request;
        $dbi = $request->getDbh();
	$sortby = false; $limit = 2; $exclude = "";

        $query = new TextSearchQuery('WikiPlugin to let users attach comments', true); // auto
        $pages = $dbi->fullSearch($query, $sortby, $limit, $exclude);
        $result = array();
	while ($page = $pages->next())
	    $result[] = $page->getName();
        $this->assertTrue(in_array("AddCommentPlugin", $result), "found all, no regex");

        $query = new TextSearchQuery('WikiPlugin* to let users attach comments*', false); // auto
	/* => (LOWER(pagename) LIKE 'wikiplugin%' OR content LIKE 'wikiplugin%') AND (LOWER(pagename) LIKE '%to%') AND (LOWER(pagename) LIKE '%let%' OR content LIKE '%let%') AND (LOWER(pagename) LIKE '%users%' OR content LIKE '%users%') AND (LOWER(pagename) LIKE '%attach%' OR content LIKE '%attach%') AND (LOWER(pagename) LIKE 'comments%' OR content LIKE 'comments%')
	 SELECT page.id AS id, page.pagename AS pagename, page.hits AS hits,page.pagedata as pagedata,version.version AS version, version.mtime AS mtime, version.minor_edit AS minor_edit, version.content AS content, version.versiondata AS versiondata FROM nonempty, page, recent, version WHERE nonempty.id=page.id AND page.id=recent.id AND page.id=version.id AND latestversion=version AND ((LOWER(pagename) LIKE 'wikiplugin%' OR content LIKE 'wikiplugin%') AND (LOWER(pagename) LIKE '%to%') AND (LOWER(pagename) LIKE '%let%' OR content LIKE '%let%') AND (LOWER(pagename) LIKE '%users%' OR content LIKE '%users%') AND (LOWER(pagename) LIKE '%attach%' OR content LIKE '%attach%') AND (LOWER(pagename) LIKE 'comments%' OR content LIKE 'comments%'))
	 SELECT page.id AS id, page.pagename AS pagename, page.hits AS hits,page.pagedata as pagedata,version.version AS version, version.mtime AS mtime, version.minor_edit AS minor_edit, version.content AS content, version.versiondata AS versiondata FROM nonempty, page, recent, version WHERE nonempty.id=page.id AND page.id=recent.id AND page.id=version.id AND latestversion=version AND ((LOWER(pagename) LIKE 'wikiplugin%' OR content LIKE 'wikiplugin%') AND (1=1) AND (LOWER(pagename) LIKE '%let%' OR content LIKE '%let%') AND (LOWER(pagename) LIKE '%users%' OR content LIKE '%users%') AND (LOWER(pagename) LIKE '%attach%' OR content LIKE '%attach%') AND (LOWER(pagename) LIKE 'comments%' OR content LIKE 'comments%'))
	 */
        $pages = $dbi->fullSearch($query, $sortby, $limit, $exclude);
        $result = array();
	while ($page = $pages->next())
	    $result[] = $page->getName();
        $this->assertTrue(in_array("AddCommentPlugin", $result), "found regex all");

	$sortby = false; $limit = 2; $exclude = "";
        $query = new TextSearchQuery('"Indent the paragraph"', false); // case-insensitive, auto
        $pages = $dbi->fullSearch($query, $sortby, $limit, $exclude);
        $result = array();
	while ($page = $pages->next())
	    $result[] = $page->getName();
        $this->assertTrue(in_array("TextFormattingRules", $result), "found phrase");

        $query = new TextSearchQuery('"Indent the paragraph"', false); // case-insensitive, auto
        $pages = $dbi->fullSearch($query, $sortby, $limit, $exclude);
        $result = array();
	while ($page = $pages->next())
	    $result[] = $page->getName();
        $this->assertTrue(in_array("TextFormattingRules", $result), "found case phrase");

    }
}


?>
