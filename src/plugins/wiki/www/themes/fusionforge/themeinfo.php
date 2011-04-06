<?php
// Avoid direct call to this file.
// PHPWIKI_VERSION is defined in lib/prepend.php
if (!defined('PHPWIKI_VERSION')) {
    header("Location: /");
    exit;
}

// rcs_id('$Id: themeinfo.php 7671 2010-09-02 20:07:31Z vargenau $');

require_once('lib/WikiTheme.php');
require_once('themes/wikilens/themeinfo.php');

class WikiTheme_fusionforge extends WikiTheme_Wikilens {

    function header() {
        global $HTML, $group_id, $group_public_name, $request, $project;

        $pagename = $request->getArg('pagename');

        $submenu = Template('navbar');

	session_require_perm ('project_read', $group_id);

        //for dead projects must be member of admin project
        if (!$project->isActive()) {
            //only SF group can view non-active, non-holding groups
            session_require_global_perm ('forge_admin');
        }

        $HTML->header(array('title'=> $group_public_name.': '.htmlspecialchars($pagename),
                            'group' => $group_id,
                            'toptab' => 'wiki',
                            'submenu' => $submenu->asXML()
                           )
                     );

        // Display a warning banner for internal users when the wiki is opened
        // to external users.
        if (method_exists($project, 'getIsExternal') && $project->getIsExternal()) {
        	$external_user = false;
        	if (session_loggedin()) {
        		$user = session_get_user();
        		$external_user = $user->getIsExternal();
        	}
        	if (!$external_user) {
	        	$page = $request->getPage();
	        	if ($page->get('external')) {
	    			$external_msg = _("This page is external.");
	    		}
	    		echo $HTML->warning_msg(_("This project is shared with third-party users (non Alcatel-Lucent users).") .
	    								(isset($external_msg) ? ' ' . $external_msg : ''));
			}
        }
    }

    function footer() {
        global $HTML;

        $HTML->footer(array());

    }

    function initGlobals() {
        global $request;
		static $already = 0;
        if (!$already) {
            $script_url = deduce_script_name();
            $script_url .= '/'. $GLOBALS['group_name'] ;
            if ((DEBUG & _DEBUG_REMOTE) and isset($_GET['start_debug']))
                $script_url .= ("?start_debug=".$_GET['start_debug']);
            $folderArrowPath = dirname($this->_findData('images/folderArrowLoading.gif'));
            $pagename = $request->getArg('pagename');
            $this->addMoreHeaders(JavaScript('', array('src' => $this->_findData("wikilens.js"))));
            $js = "var data_path = '". javascript_quote_string(DATA_PATH) ."';\n"
            // Temp remove pagename because of XSS warning
            //	."var pagename  = '". javascript_quote_string($pagename) ."';\n"
                ."var script_url= '". javascript_quote_string($script_url) ."';\n"
                ."var stylepath = data_path+'/".javascript_quote_string($this->_theme)."/';\n"
                ."var folderArrowPath = '".javascript_quote_string($folderArrowPath)."';\n"
                ."var use_path_info = " . (USE_PATH_INFO ? "true" : "false") .";\n";
            $this->addMoreHeaders(JavaScript($js));
	    $already = 1;
        }
    }
    function load() {

        $this->initGlobals();

        /**
         * The Signature image is shown after saving an edited page. If this
         * is set to false then the "Thank you for editing..." screen will
         * be omitted.
         */

        $this->addImageAlias('signature', WIKI_NAME . "Signature.png");
        // Uncomment this next line to disable the signature.
        $this->addImageAlias('signature', false);

        /*
         * Link icons.
         */
        $this->setLinkIcon('http');
        $this->setLinkIcon('https');
        $this->setLinkIcon('ftp');
        $this->setLinkIcon('mailto');

        $this->setButtonSeparator("");

        /**
         * WikiWords can automatically be split by inserting spaces between
         * the words. The default is to leave WordsSmashedTogetherLikeSo.
         */
        $this->setAutosplitWikiWords(false);

        /**
         * Layout improvement with dangling links for mostly closed wiki's:
         * If false, only users with edit permissions will be presented the
         * special wikiunknown class with "?" and Tooltip.
         * If true (default), any user will see the ?, but will be presented
         * the PrintLoginForm on a click.
         */
        $this->setAnonEditUnknownLinks(false);

        /*
         * You may adjust the formats used for formatting dates and times
         * below.  (These examples give the default formats.)
         * Formats are given as format strings to PHP strftime() function See
         * http://www.php.net/manual/en/function.strftime.php for details.
         * Do not include the server's zone (%Z), times are converted to the
         * user's time zone.
         */
        $this->setDateFormat("%d %B %Y");
        $this->setTimeFormat("%H:%M");
    }

    /* Callback when a new user creates or edits a page */
    function CbNewUserEdit (&$request, $userid) {
        $content = "{{Template/UserPage}}\n\n----\n[[CategoryWiki user]]";
        $dbi =& $request->_dbi;
        $page = $dbi->getPage($userid);
        $page->save($content, WIKIDB_FORCE_CREATE, array('author' => $userid));
        $dbi->touch();
    }
}

$WikiTheme = new WikiTheme_fusionforge('fusionforge');

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
