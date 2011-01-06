<?php
/**
 * FusionForge navigation
 *
 * Copyright 2009 - 2010, Olaf Lenz
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfwww.'search/include/SearchManager.class.php';
 
/**
 * This class provides all the navigational elements to be used by the themes,
 * like the site menu, the project menus, and the user links.
 * Some of the methods return HTML code, some return abstract data
 * structures, and some methods give you the choice. The HTML code
 * always tries to be as generic as possible so that it can easily be
 * styled via CSS.
 */
class Navigation extends Error {
	/**
	 * Associative array of data for the project menus.
	 *
	 * @var	array	$project_menu_data.
	 */
	var $project_menu_data;

	/** Constructor */
	function Navigation() {
		$this->Error();
		return true;
	}

	/** Get the HTML code of the title of the page. If the array
	 *  $params contains a value for the key 'title', this title
	 *  is appended to the title generated here. If $asHTML is
	 *  set to false, it will return only the title in plain
	 *  text. */
	function getTitle($params, $asHTML = true) {
		if (!$asHTML) {
			// get the title
			if (!$params['title']) {
				return forge_get_config('forge_name');
			} else {
				return forge_get_config('forge_name') . ': ' . $params['title'];
			}
		} else {
			// return HTML code otherwise
			return '<title>' . $this->getTitle($params, false) . '</title>';
		}
	}

	/** Get the HTML code for the favicon links of the site (to be
	 *  put into the <head>. If $asHTML is false, it will return
	 *  the URL of the favicon.
	 * 
	 * @todo: Make favicon configurable
	 */
	function getFavIcon($asHTML=true) {
		if (!$asHTML) {
			return util_make_url('/images/icon.png');
		} else {
			return '<link rel="icon" type="image/png" href="' 
				. $this->getFavIcon(false) .'" />' 
				. '<link rel="shortcut icon" type="image/png" href="'
				. $this->getFavIcon(false) .'" />';
		}
	}

	/** Get the HTML code for the RSS feeds of the site (to be put
	 *  into the <head>. If $asHTML is false, it will return an
	 *  array with the following structure: $result['titles']:
	 *  list of titles of the feeds; $result['urls'] list of urls
	 *  of the feeds. */
	function getRSS($asHTML=true) {
                if (!$asHTML) {
                        $res = array() ;
                        $res['titles'] = array();
                        $res['urls'] = array();
                        
                        $res['titles'][] = forge_get_config ('forge_name').' - Project News Highlights RSS';
                        $res['urls'][] = util_make_url('/export/rss_sfnews.php'); 
                        
                        $res['titles'][] = forge_get_config ('forge_name').' - Project News Highlights RSS 2.0';
                        $res['urls'][] = util_make_url('/export/rss20_news.php'); 
                        
                        $res['titles'][] = forge_get_config ('forge_name').' - New Projects RSS';
                        $res['urls'][] = util_make_url('/export/rss_sfprojects.php'); 
                        
                        if (isset($GLOBALS['group_id'])) { 
                                $res['titles'][] = forge_get_config ('forge_name') . ' - New Activity RSS';
                                $res['urls'][] = util_make_url('/export/rss20_activity.php?group_id='.$GLOBALS['group_id']);
                        }
                        return $res;
                } else {
                        $feeds = $this->getRSS(false);
                        for ($j = 0; $j < count($feeds['urls']); $j++) {
                                echo '
                                <link rel="alternate" title="' . $feeds['titles'][$j] 
					. '" href="' . $feeds['urls'][$j] 
					. '" type="application/rss+xml"/>';
                        }
                }
        }

	/** Get the searchBox HTML code. */
	function getSearchBox() {
		global $words,$forum_id,$group_id,$group_project_id,$atid,$exact,$type_of_search;

		$res = "";
		if (get_magic_quotes_gpc()) {
			$defaultWords = stripslashes($words);
		} else {
			$defaultWords = $words;
		}

		$defaultWords = htmlspecialchars($defaultWords);
		
		// if there is no search currently, set the default
		if (!isset($type_of_search) ) {
			$exact = 1;
		}

		$res .= '<form id="searchBox" action="'.util_make_uri('/search/').'" method="get">
			<div>';
		$parameters = array(
			SEARCH__PARAMETER_GROUP_ID => $group_id,
			SEARCH__PARAMETER_ARTIFACT_ID => $atid,
			SEARCH__PARAMETER_FORUM_ID => $forum_id,
			SEARCH__PARAMETER_GROUP_PROJECT_ID => $group_project_id
			);

		$searchManager =& getSearchManager();
		$searchManager->setParametersValues($parameters);
		$searchEngines =& $searchManager->getAvailableSearchEngines();

		$res .= '<label for="searchBox-words">
			<select name="type_of_search">';
		for($i = 0, $max = count($searchEngines); $i < $max; $i++) {
			$searchEngine =& $searchEngines[$i];
			$res .= '<option value="' . $searchEngine->getType() . '"' 
				. ( $type_of_search == $searchEngine->getType() ? ' selected="selected"' : '' )
				. '>' . $searchEngine->getLabel($parameters) . '</option>' . "\n";
		}
		$res .= '</select></label>';

		$parameters = $searchManager->getParameters();
		foreach($parameters AS $name => $value) {
			$res .= '<input type="hidden" value="'.$value.'" name="'.$name.'" />' . "\n";
		}
		$res .= '<input type="text" size="12" id="searchBox-words" name="words" value="' 
			. $defaultWords . '" />' . "\n";
		$res .= '<input type="submit" name="Search" value="'._('Search').'" />' . "\n";

		if (isset($group_id) && $group_id) {
			$res .= util_make_link('/search/advanced_search.php?group_id=' . 
					       $group_id, _('Advanced search'));
		}
		$res .= '</div>';
		$res .= '</form>';

		return $res;
	}

	/** Get an array of the user links (Login/Logout/My
	 Account/Register) with the following structure:
	 $result['titles']: list of the titles. $result['urls']: list
	 of the urls.
	 */
	function getUserLinks() {
                $res = array();
                if (session_loggedin()) {
                        $u =& user_get_object(user_getid());
                        $res['titles'][] = sprintf("%s (%s)", _('Log Out'), $u->getRealName());
                        $res['urls'][] = util_make_uri('/account/logout.php');

                        $res['titles'][] = _('My Account');
                        $res['urls'][] = util_make_uri('/account/');
                } else {
                        $url = '/account/login.php';
                        if(getStringFromServer('REQUEST_METHOD') != 'POST') {
                                $url .= '?return_to=';
                                $url .= urlencode(getStringFromServer('REQUEST_URI'));
                        }
                        $res['titles'][] = _('Log In');
                        $res['urls'][] = util_make_url($url);
                        
                        if (!forge_get_config ('user_registration_restricted')) {
                                $res['titles'][] = _('New Account');
                                $res['urls'][] = util_make_url('/account/register.php');
                        }
                }
                return $res;
	}

	/** Get an array of the menu of the site with the following
	 *  structure: $result['titles']: list of titles of the
	 *  links. $result['urls']: list of urls. $result['selected']:
	 *  number of the selected menu entry.
	 */
        function getSiteMenu() {
                $request_uri = getStringFromServer('REQUEST_URI');
                
                $menu = array();
                $menu['titles'] = array();
                $menu['urls'] = array();
                $selected = 0;
                
                // Home
                $menu['titles'][] = _('Home');
                $menu['urls'][] = util_make_uri('/'); 
                
                // My Page
                $menu['titles'][] = _('My&nbsp;Page');
                $menu['urls'][] = util_make_uri('/my/'); 
                if (strstr($request_uri, util_make_uri('/my/'))
                    || strstr($request_uri, util_make_uri('/account/'))
                    || strstr($request_uri, util_make_uri('/register/'))
                    || strstr($request_uri, util_make_uri('/themes/'))
			) 
                {
                        $selected=count($menu['urls'])-1;
                }
                
		if (forge_get_config('use_trove') || forge_get_config('use_project_tags') || forge_get_config('use_project_full_list')) {
			$menu['titles'][] = _('Projects');
			$menu['urls'][] = util_make_uri('/softwaremap/') ;
			if (strstr($request_uri, util_make_uri('/softwaremap/'))) {
				$selected=count($menu['urls'])-1;
			}
		}

		if (forge_get_config('use_snippet')) {
			$menu['titles'][] = _('Code&nbsp;Snippets');
			$menu['urls'][] = util_make_uri('/snippet/') ;
			if (strstr($request_uri, util_make_uri('/snippet/'))) {
				$selected=count($menu['urls'])-1;
			}
		}

		if (forge_get_config('use_people')) {
			$menu['titles'][] = _('Project&nbsp;Openings');
			$menu['urls'][] = util_make_uri('/people/') ;
			if (strstr($request_uri, util_make_uri('/people/'))) {
				$selected=count($menu['urls'])-1;
			}
		}

		// Outermenu hook
		$before = count($menu['urls']);
		$plugin_urls = array();
		$hookParams['DIRS'] = &$menu['urls'];
		$hookParams['TITLES'] = &$menu['titles'];
		plugin_hook ("outermenu", $hookParams);

		// try to find selected entry
		for ($j = $before; $j < count($plugin_urls); $j++) {
			$url = $menu['urls'][$j];
			if (strstr($request_uri, parse_url ($url, PHP_URL_PATH))) {
				$selected = $j;
				break;
			}
		}

		// Admin and Reporting 
		if (forge_check_global_perm ('forge_admin')) {
			$user_is_super = true;
			$menu['titles'][] = _('Site Admin');
			$menu['urls'][] = util_make_url('/admin/') ;
			if (strstr($request_uri, util_make_uri('/admin/'))) {
				$selected=count($menu['urls'])-1;
			}
		}
		if (forge_check_global_perm ('forge_stats', 'read')) {
			$menu['titles'][] = _('Reporting');
			$menu['urls'][] = util_make_uri('/reporting/') ;
			if (strstr($request_uri, util_make_uri('/reporting/'))) {
				$selected=count($menu['urls'])-1;
			}
		}

		// Project
		if (isset($GLOBALS['group_id'])) { 
			// get group info using the common result set
			$project =& group_get_object($GLOBALS['group_id']);
			if ($project && is_object($project)) {
				if ($project->isError()) {
				} elseif (!$project->isProject()) {
				} else {
					$menu['titles'][] = $project->getPublicName();
					if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
						$menu['urls'][]=util_make_uri('/project/?group_id') .$project->getId();
					} else {
						$menu['urls'][]=util_make_uri('/projects/') .$project->getUnixName().'/';
					}
					$selected=count($menu['urls'])-1;
				}
			}
		}

                $menu['selected'] = $selected;
                          
                return $menu;
        }

        /** Get a reference to an array of the projects menu for the
	 * project with the id $group_id with the following
	 * structure: $result['starturl']: URL of the
	 * projects starting page; $result['name']: public name of
	 * the project; $result['titles']: list of titles of the menu
	 * entries; $result['urls']: list of urls of the menu
	 * entries; $result['adminurls']: list of urls to the admin
	 * pages of the menu entries. If the user has no admin
	 * permissions, the correpsonding adminurl is
	 * false. $result['selected']: number of the menu entry that
	 * is currently selected.
	 */
	function getProjectMenu($group_id, $toptab="") {
		// rebuild menu if it has never been built before, or
		// if the toptab was set differently
		if (!isset($this->project_menu_data[$group_id])
			|| ($toptab != "") 
			|| ($toptab != $this->project_menu_data[$group_id]['last_toptab'])) {
			// get the group and permission objects
			$group = group_get_object($group_id);
			if (!$group || !is_object($group)) {
				return null;
			}
			if ($group->isError()) {
				//wasn't found or some other problem
				return null;
			}
                        if (!$group->isProject()) {
                                return;
                        }
                        
                        $selected = 0;
                        
                        $menu =& $this->project_menu_data[$group_id];
                        $menu['titles'] = array();
                        $menu['urls'] = array();
                        $menu['adminurls'] = array();

			$menu['name'] = $group->getPublicName();

                        // Summary
                        $menu['titles'][] = _('Summary');
                        if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
                                $url = util_make_uri('/project/?group_id=' . $group_id);
                        } else {
                                $url = util_make_uri('/projects/' . $group->getUnixName() .'/');
                        }
                        $menu['urls'][] = $url;
                        $menu['adminurls'][] = false;
                        if ($toptab == "home") {
                                $selected = (count($menu['urls'])-1);
                        }

                        // setting these allows to change the initial project page
                        $menu['starturl'] = $url;

                        // Project Admin
                        if (forge_check_perm ('project_admin', $group_id)) {
                                $menu['titles'][] = _('Admin');
                                $menu['urls'][] = util_make_uri('/project/admin/?group_id=' . $group_id);
                                $menu['adminurls'][] = false;
                                if ($toptab == "admin") {
                                        $selected = (count($menu['urls'])-1);
                                }
                        }

                        /* Homepage
			 // check for use_home_tab?
			 $TABS_DIRS[]='http://'. $this->getHomePage();
			 $TABS_TITLES[]=_('Home Page');
                        */

                        // Project Activity tab 
                        $menu['titles'][] = _('Activity');
                        $menu['urls'][] = util_make_uri('/activity/?group_id=' . $group_id);
                        $menu['adminurls'][] = false;
                        if ($toptab == "activity") {
                                $selected = (count($menu['urls'])-1);
                        }

                        // Forums
                        if ($group->usesForum()) {
                                $menu['titles'][] = _('Forums');
                                $menu['urls'][] = util_make_uri('/forum/?group_id=' . $group_id);
                                if (forge_check_perm ('forum_admin', $group_id)) {
                                        $menu['adminurls'][] = util_make_url('/forum/admin/?group_id='.$group_id);
                                } else {
                                        $menu['adminurls'][] = false;
                                }
                                if ($toptab == "forums") {
                                        $selected = (count($menu['urls'])-1);
                                }
                        }

                        // Artifact Tracking
                        if ($group->usesTracker()) {
                                $menu['titles'][] = _('Tracker');
                                $menu['urls'][] = util_make_uri('/tracker/?group_id=' . $group_id);
                                if (forge_check_perm ('tracker_admin', $group_id)) {
                                        $menu['adminurls'][] = util_make_url('/tracker/admin/?group_id='.$group_id);
                                } else {
                                        $menu['adminurls'][] = false;
                                }
                                if ($toptab == "tracker" || 
                                    $toptab == "bugs" || 
                                    $toptab == "support" || 
                                    $toptab == "patch") {
					$selected = (count($menu['urls'])-1);
				}
                        }

                        // Mailing Lists
                        if ($group->usesMail()) {
                                $menu['titles'][] = _('Lists');
                                $menu['urls'][] = util_make_uri('/mail/?group_id=' . $group_id);
                                if (forge_check_perm ('project_admin', $group_id)) {
                                        $menu['adminurls'][] = util_make_url('/mail/admin/?group_id='.$group_id);
                                } else {
                                        $menu['adminurls'][] = false;
                                }
                                if ($toptab == "mail") {
                                        $selected = (count($menu['urls'])-1);
                                }
                        }

                        // Project/Task Manager
                        if ($group->usesPm()) {
                                $menu['titles'][] = _('Tasks');
                                $menu['urls'][] = util_make_uri('/pm/?group_id=' . $group_id);
                                if (forge_check_perm ('pm_admin', $group_id)) {
                                        $menu['adminurls'][] = util_make_uri('/pm/admin/?group_id='.$group_id);
                                } else {
                                        $menu['adminurls'][] = false;
                                }
                                if ($toptab == "pm") {
                                        $selected = (count($menu['urls'])-1);
                                }
                        }

                        // Doc Manager
                        if ($group->usesDocman()) {
                                $menu['titles'][] = _('Docs');
                                $menu['urls'][] = util_make_uri('/docman/?group_id=' . $group_id);
                                if (forge_check_perm ('docman', $group_id, 'approve')) {
                                        $menu['adminurls'][] = util_make_uri('/docman/?group_id='.$group_id.'&amp;view=admin');
                                } else {
                                        $menu['adminurls'][] = false;
                                }
                                if ($toptab == "docman") {
                                        $selected = (count($menu['urls'])-1);
                                }
                        }

                        // Surveys
                        if ($group->usesSurvey()) {
                                $menu['titles'][] = _('Surveys');
                                $menu['urls'][] = util_make_uri('/survey/?group_id=' . $group_id);
                                if (forge_check_perm ('project_admin', $group_id)) {
                                        $menu['adminurls'][] = util_make_uri('/survey/admin/?group_id='.$group_id);
                                } else {
                                        $menu['adminurls'][] = false;
                                }
                                if ($toptab == "surveys") {
                                        $selected = (count($menu['urls'])-1);
                                }
                        }

                        // News
                        if ($group->usesNews()) {
                                $menu['titles'][] = _('News');
                                $menu['urls'][] = util_make_uri('/news/?group_id=' . $group_id);
                                if (forge_check_perm ('project_admin', $group_id)) {
                                        $menu['adminurls'][] = util_make_uri('/news/admin/?group_id='.$group_id);
                                } else {
                                        $menu['adminurls'][] = false;
                                }
                                if ($toptab == "news") {
                                        $selected = (count($menu['urls'])-1);
                                }
                        }

                        // SCM systems
                        if ($group->usesSCM()) {
                                $menu['titles'][] = _('SCM');
                                $menu['urls'][] = util_make_uri('/scm/?group_id=' . $group_id);
                                // eval cvs_flags?
                                if (forge_check_perm ('project_admin', $group_id)) {
                                        $menu['adminurls'][] = util_make_uri('/scm/admin/?group_id='.$group_id);
                                } else {
                                        $menu['adminurls'][] = false;
                                }
                                if ($toptab == "scm") {
                                        $selected = (count($menu['urls'])-1);
                                }
                        }

                        // groupmenu_after_scm hook
                        $hookParams = array();
                        $hookParams['group_id'] = $group_id ;
                        $hookParams['DIRS'] =& $menu['urls'];
                        $hookParams['TITLES'] =& $menu['titles'];
                        $hookParams['toptab'] =& $toptab;
                        $hookParams['selected'] =& $selected;
                        plugin_hook ("groupmenu_scm", $hookParams) ; 

                        // fill up adminurls
                        for ($i = 0; $i < count($menu['urls']) - count($menu['adminurls']); $i++) {
                                $menu['adminurls'][] = false;
                        }

			// Downloads
			if ($group->usesFRS()) {
				$menu['titles'][] = _('Files');
				$menu['urls'][] = util_make_uri('/frs/?group_id=' . $group_id);
				if (forge_check_perm ('frs', $group_id, 'write')) {
					$menu['adminurls'][] = util_make_uri('/frs/admin/?group_id='.$group_id);
				} else {
					$menu['adminurls'][] = false;
				}
				if ($toptab == "frs") {
					$selected = (count($menu['urls'])-1);
				}
			}

			// groupmenu hook
			$hookParams = array();
			$hookParams['group'] = $group_id ;
			$hookParams['DIRS'] =& $menu['urls'];
			$hookParams['TITLES'] =& $menu['titles'];
			$hookParams['toptab'] =& $toptab;
			$hookParams['selected'] =& $selected;
			plugin_hook ("groupmenu", $hookParams) ;

			// fill up adminurls
			for ($i = 0; $i < count($menu['urls']) - count($menu['adminurls']); $i++) {
				$menu['adminurls'][] = false;
			}

			// store selected menu item (if any)
			$menu['selected'] = $selected;
			if ($toptab != "") {
				$menu['last_toptab'] = $toptab;
			}
		}
		return $this->project_menu_data[$group_id] ;
	}

	/**
	 * Create the HTML code for the banner "Powered By
	 * FusionForge". If $asHTML is set to false, it will return an
	 * array with the following structure: $result['url']: URL for
	 * the link on the banner; $result['image']: URL of the banner
	 * image; $result['title']: HTML code that outputs the banner;
	 * $result['html']: HTML code that creates the banner and the link.
	 */
	function getPoweredBy($asHTML=true) {
		$res['url'] = 'http://fusionforge.org/';
		$res['image'] = util_make_uri('/images/pow-fusionforge.png');
		$res['title'] = '<img src="' 
			. $res['image'] 
			. '" alt="Powered By FusionForge" border="0" />';
		$res['html'] = util_make_link($res['url'], $res['title'], array(), true);
		if ($asHTML) {
			return $res['html'];
		} else {
			return $res;
		}
	}

	/** Create the HTML code for the "Show Source" link if
	 *  forge_get_config('show_source') is set, otherwise "". If $asHTML is set
	 *  to false, it returns NULL when forge_get_config('show_source') is not
	 *  set, otherwise an array with the following structure:
	 *  $result['url']: URL of the link to the source code viewer;
	 *  $result['title']: Title of the link.
	 */
	function getShowSource($asHTML=true) {
		if (forge_get_config('show_source')) {
                        $res['url'] = util_make_url('/source.php?file='.getStringFromServer('SCRIPT_NAME'));
                        $res['title'] = _('Show source');
		} else {
                        return ($asHTML ? "" : NULL); 
		}
                if (!$asHTML) {
                        return $res;
		} else {
                        return util_make_link($res['url'], $res['title'], 
                                              array('class' => 'showsource'), 
                                              true);
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
