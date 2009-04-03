<?php
rcs_id('$Id: themeinfo.php 6409 2009-01-17 14:40:16Z rurban $');

/*
 * This file defines the Sidebar theme of PhpWiki,
 * which can be used as parent class for all sidebar themes. See MonoBook and blog.
 * It is now an extension of the MonoBook theme.
 *
 * This uses the dynamic jscalendar, which doesn't need extra requests per month/year change.
 *
 * Changes to MonoBook:
 *  folderArrow
 *  special login, search and tags
 *  CbNewUserEdit - when a new user creates or edits a page, a Userpage template is created
 *  CbUpload - uploads are virus checked
 */

if (!defined("CLAMDSCAN_PATH"))
    define("CLAMDSCAN_PATH","/usr/local/bin/clamdscan");
if (!defined("CLAMDSCAN_VIRUS"))
    define("CLAMDSCAN_VIRUS","/var/www/virus-found");

require_once('lib/WikiTheme.php');
require_once('themes/MonoBook/themeinfo.php');

class WikiTheme_Sidebar extends WikiTheme_MonoBook {

    function WikiTheme_Sidebar ($theme_name='Sidebar') {
        $this->WikiTheme($theme_name);
        //$this->calendarInit(true);
    }

    /* Display up/down button with persistent state */
    /* persistent state per block in cookie for 30 days */
    function folderArrow ($id, $init='Open') {
    	global $request;
    	if ($cookie = $request->cookies->get("folder_".$id)) {
    	    $init = $cookie;
	}
	$png = $this->_findData('images/folderArrow'.$init.'.png');
	return HTML::img(array('id' => $id.'-img',
	                       'src' => $png,
			       //'align' => 'right',
	                       'onClick' => "showHideFolder('$id')",
			       'title'  => _("Click to hide/show")));
    }

    /* Callback when a new user creates or edits a page */
    function CbNewUserEdit (&$request, $userid) {
    	$userid = strtoupper($userid);
	$content = "{{Template/UserPage}}";
        $dbi =& $request->_dbi;
        $page = $dbi->getPage($userid);
        $page->save($content, WIKIDB_FORCE_CREATE, array('author' => $userid));
        $dbi->touch();
    }

    /** CbUpload (&$request, $pathname) => true or false
     * Callback when a file is uploaded. virusscan, ...
     * @param string $str
     * @return bool true for success, false to abort gracefully.
     * In case of false, the file is deleted by the caller, but the callback must 
     * inform the user why the file was deleted.
     * Src:
     *   if (!$WikiTheme->CbUpload($request, $file_dir . $userfile_name))
     *      unlink($file_dir . $userfile_name);
     */
    function CbUpload (&$request, $pathname) {
        $cmdline = CLAMDSCAN_PATH . " --nosummary --move=" . CLAMDSCAN_VIRUS;
	$report = `$cmdline "$pathname"`;
	if (!$report) {
	    trigger_error("clamdscan failed", E_USER_WARNING);
	    return true;
	}
	if (!preg_match("/: OK$/", $report)) {
	    //preg_match("/: (.+)$/", $report, $m);
	    trigger_error("Upload failed. virus-scanner: $report", E_USER_WARNING);
	    return false;
	} else {
	    return true;
	}
    }

}

$WikiTheme = new WikiTheme_Sidebar('Sidebar');

/*if (ENABLE_RATEIT) {
    require_once("lib/wikilens/CustomPrefs.php");
    require_once("lib/wikilens/PageListColumns.php");
    $plugin = new WikiPlugin_RateIt;
    $plugin->head();
}*/


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// (c-file-style: "gnu")
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:   
?>
