<?php

/**
 * WikiPlugin Class
 *
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

require_once ('WikiSearchEngine.class.php') ;

class GforgeWikiPlugin extends Plugin {
	function GforgeWikiPlugin () {
		$this->Plugin() ;
		$this->name = "wiki" ;
		$this->text = "Wiki" ; // To show in the tabs, use...
		$this->installdir = 'wiki';
		$this->hooks[] = "user_personal_links";//to make a link to the user's personal wiki
		$this->hooks[] = "usermenu" ;
		$this->hooks[] = "groupmenu";
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; // 
		$this->hooks[] = "userisactivecheckbox" ; // The "use ..." checkbox in user account
		$this->hooks[] = "userisactivecheckboxpost" ; // 
		$this->hooks[] = 'search_engines';
		$this->hooks[] = 'full_search_engines';
		$this->hooks[] = 'cssfile';
	}

	function CallHook ($hookname, & $params) {
		global $G_SESSION,$HTML;
		if ($hookname == "usermenu") {
			$text = $this->text;
			if ( ($G_SESSION) && ($G_SESSION->usesPlugin("wiki")) ) {
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
					array ('/wiki/u/'. urlencode($G_SESSION->getUnixName()).'/HomePage' ));
			} else {
				$this->hooks["usermenu"] = "" ;
				//$param = "?off=true";
			}
			
		} elseif ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$project = &group_get_object($group_id);
			if (!$project || !is_object($project))
				return;
			if ($project->isError())
				return;
			if (!$project->isProject())
				return;
			if ( $project->usesPlugin ( $this->name ) ) {
				$params['TITLES'][]=$this->text;
				$params['DIRS'][]='/wiki/g/'.$project->getUnixName().'/HomePage';
			} else {
				$this->hooks["groupmenu"] = "";
				//$params['TITLES'][]=$this->text." [Off]";
				//$params['DIRS'][]='/plugins/wiki/index.php?off=true';
			}
							
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		} elseif ($hookname == "groupisactivecheckbox") {
                        //Check if the group is active
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_wikiplugin" value="1" ';
			// checked or unchecked?
			if ( $group->usesPlugin ( $this->name ) ) {
				echo "checked=\"checked\"";
                            }
			echo " /><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>Use ".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "groupisactivecheckboxpost") {
			$group_id=$params['group'];
		        $group = &group_get_object($group_id);
			if ( getIntFromRequest('use_wikiplugin') == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "userisactivecheckbox") {
			//check if user is active
			$user = $params['user'];
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_wikiplugin" value="1" ';
			// checked or unchecked?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "checked=\"checked\"";
                            }

			echo " />    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "userisactivecheckboxpost") {
			$user = $params['user'];
			if ( getIntFromRequest('use_wikiplugin') == 1 ) {
				$user->setPluginUse ( $this->name );
			} else {
				$user->setPluginUse ( $this->name, false );
			}
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_wikiplugin" value="1" ';
			// checked or unchecked?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "checked=\"checked\"";
                            }

			echo " />    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "user_personal_links") {
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			$user_name = $user->getUnixName();
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>
					<a href="/wiki/u/'.urlencode($user_name).'/HomePage">' . _('View personal wiki') .'</a></p>';
			}
		} elseif ($hookname == 'search_engines') {
			// FIXME: when the hook is called, the group_id is not set.
			// So I use the global variable instead.
			$group_id = $GLOBALS['group_id'];
			if ($group_id) {
				$group = group_get_object($group_id);
				if ($group->usesPlugin('wiki')) {
					$params->addSearchEngine(
						SEARCH__TYPE_IS_WIKI,
						new WikiSearchEngine(SEARCH__TYPE_IS_WIKI, 
								     'WikiHtmlSearchRenderer', 
								     _("This project's wiki"), $group_id)
						);
				}
			}
		} elseif ($hookname == 'full_search_engines') {
			// FIXME: when the hook is called, the group_id is not set.
			// So I use the global variable instead.
			$group_id = $GLOBALS['group_id'];
			$group = &group_get_object($group_id);
			if ($group->usesPlugin ( $this->name)) {
				require_once('plugins/wiki/include/WikiHtmlSearchRenderer.class.php');
				$wikiRenderer = new WikiHtmlSearchRenderer($params->words, $params->offset, $params->isExact, $params->groupId);
				$validLength = (strlen($params->words) >= 3);
				if ($validLength || (is_numeric($params->words) && $wikiRenderer->searchQuery->implementsSearchById())) {
					$html = $params->getPartResult($wikiRenderer, 'short_wiki', 'Wiki');
					return $html;
				}
			}
		} elseif ($hookname == 'cssfile') {
			if (strncmp($_SERVER['REQUEST_URI'], '/wiki/', 6) == 0) {
				echo '<link rel="stylesheet" type="text/css" href="/wiki/themes/gforge/phpwiki.css" media="screen" />';
				echo "\n".'    <link rel="alternate stylesheet" type="text/css" href="/wiki/themes/gforge/phpwiki-fullscreen.css" media="screen" title="Fullscreen" />';
				echo "\n".'    <link rel="stylesheet" type="text/css" href="/wiki/themes/gforge/phpwiki-print.css" media="print" />';
				echo "\n".'    <base href="'.PHPWIKI_BASE_URL.'" />';
			}
		}		
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
