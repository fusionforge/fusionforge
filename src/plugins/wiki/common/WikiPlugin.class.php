<?php
/**
 * WikiPlugin Class
 * Wiki Search Engine for Fusionforge
 *
 * Copyright 2006 (c) Alain Peyrat
 * Copyright 2016, Franck Villaume - TrivialDev
 *
 * This file is part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $gfplugins;
require_once $gfplugins.'wiki/common/WikiSearchEngine.class.php';

class FusionForgeWikiPlugin extends Plugin {
	function __construct() {
		parent::__construct();
		$this->name = "wiki";
		$this->text = _("Wiki"); // To show in the tabs, use...
		$this->pkg_desc =
_("PhpWiki plugin for FusionForge. Allows for one wiki per project, integrated search,
page edits displayed on activity tab, and multi-project wiki preferences.");
		$this->installdir = 'wiki';
		$this->hooks[] = "groupmenu";
		$this->hooks[] = "groupisactivecheckbox"; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost";
		$this->hooks[] = "project_admin_plugins"; // to show up in the project admin page
		$this->hooks[] = 'search_engines';
		$this->hooks[] = 'full_search_engines';
		$this->hooks[] = 'cssfile';
		$this->hooks[] = 'soap';
		$this->hooks[] = 'project_public_area';
		$this->hooks[] = 'activity';
		$this->hooks[] = 'site_admin_option_hook';
		$this->hooks[] = 'crossrefurl';
	}

	function CallHook($hookname, &$params) {
		if (is_array($params) && isset($params['group']))
			$group_id=$params['group'];
		if ($hookname == "groupmenu") {
			$project = group_get_object($group_id);
			if (!$project || !is_object($project))
				return;
			if ($project->isError())
				return;
			if ($project->usesPlugin($this->name)) {
				$params['TITLES'][]=$this->text;
				$params['DIRS'][]='/wiki/g/'.$project->getUnixName().'/HomePage';
				$params['TOOLTIPS'][] = null;
			} else {
				$this->hooks["groupmenu"] = "";
				//$params['TITLES'][]=$this->text." [Off]";
				//$params['DIRS'][]='/wiki/index.php?off=true';
			}

			if (isset($params['toptab'])) {
				(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
			}
		} elseif ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to its administration page.
			$group_id = $params['group_id'];
			$group = group_get_object($group_id);
			if ($group->usesPlugin($this->name)) {
				echo html_e('p', array(), util_make_link('/wiki/wikiadmin.php?group_id='.$group->getID().'&type=admin&pluginname='.$this->name, _('Wiki Admin')));
			}
		} elseif ($hookname == 'search_engines') {
			// FIXME: when the hook is called, the group_id is not set.
			// So I use the global variable instead.
			$group_id = $GLOBALS['group_id'];
			if ($group_id) {
				$group = group_get_object($group_id);
				if (!$group || !is_object($group)) {
					return;
				}
				if ($group->usesPlugin('wiki')) {
					$searchManager = $params['object'];
					$searchManager->addSearchEngine(
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
			$group = group_get_object($group_id);
			if ($group->usesPlugin ( $this->name)) {
				global $gfwww, $gfcommon, $gfplugins;
				require_once 'plugins/wiki/common/WikiHtmlSearchRenderer.class.php';
				$wikiRenderer = new WikiHtmlSearchRenderer($params->words, $params->offset, $params->isExact, $params->groupId);
				$validLength = (strlen($params->words) >= 3);
				if ($validLength || (is_numeric($params->words) && $wikiRenderer->searchQuery->implementsSearchById())) {
					$html = $params->getPartResult($wikiRenderer, 'short_wiki', 'Wiki');
					return $html;
				}
			}
		} elseif ($hookname == 'cssfile') {
			if (defined('PHPWIKI_BASE_URL')) {
				use_stylesheet('/wiki/themes/fusionforge/fusionforge.css');
				use_stylesheet('/wiki/themes/fusionforge/fusionforge-print.css', 'print');
				use_stylesheet('/wiki/highlight.js/styles/github.css');
				echo '    <link rel="alternate" type="application/x-wiki" title="Edit this page!" href="'.$_SERVER['PHP_SELF'].'?action=edit" />';
				echo "\n".'    <link rel="alternate stylesheet" type="text/css" href="/wiki/themes/fusionforge/fusionforge-fullscreen.css" media="screen" title="Fullscreen" />';
				echo "\n".'    <link rel="alternate stylesheet" type="text/css" href="/wiki/themes/fusionforge/fusionforge-autonumbering.css" title="Autonumbering" />';
				echo "\n".'    <link rel="alternate stylesheet" type="text/css" href="/wiki/themes/fusionforge/fusionforge-rereading.css" title="Rereading Mode" />';
				echo "\n".'    <base href="'.PHPWIKI_BASE_URL.'" />';
				echo "\n";
			}
		} elseif ($hookname == "soap") {
			$params['requires'][] = dirname(__FILE__).'/soap.php';
		} elseif ($hookname == "project_public_area") {
			$project = group_get_object($params['group_id']);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if ($project->usesPlugin($this->name)) {
				$params['result'] .= '<div class="public-area-box">';
				$params['result'] .= util_make_link('/wiki/g/'.$project->getUnixName().'/HomePage',
									html_image('ic/wiki20g.png', 20, 20, array('alt' => 'Wiki')).
									'&nbsp;'.'Wiki');
				$params['result'] .= '</div>';
			}
		} elseif ($hookname == 'crossrefurl') {
			$project = group_get_object($params['group_id']);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if ($project->usesPlugin($this->name) && isset($params['page'])) {
				$params['url'] = '/wiki/g/'.$prj.'/'.rawurlencode($params['page']);
				return true;
			} else {
				return;
			}
		} elseif ($hookname == 'activity') {
			$group = group_get_object($params['group_id']);
			if (!$group || !is_object($group)) {
				return;
			}
			if ($group->isError()) {
				return;
			}
			if ($group->usesPlugin($this->name)) {
				// Add activities from the wiki plugin if active.
				// Only major edits are included.
				$params['ids'][] = 'wiki';
				$params['texts'][] = $this->text;

				if (count($params['show']) < 1 || array_search('wiki',$params['show']) !== false) {

					$pat = '_g'.$group_id.'_';
					$len = strlen($pat)+1;
					$encoding = pg_client_encoding();
					// @ToDo: to remove after wiki tables convert
					pg_set_client_encoding("iso-8859-1");
					$wres = db_query_params ("SELECT plugin_wiki_page.id AS id,
							substring(plugin_wiki_page.pagename from $len) AS pagename,
							plugin_wiki_version.version AS version,
							plugin_wiki_version.mtime AS activity_date,
							plugin_wiki_version.minor_edit AS minor_edit,
							plugin_wiki_version.versiondata AS versiondata
						FROM plugin_wiki_page, plugin_wiki_version
						WHERE plugin_wiki_page.id=plugin_wiki_version.id
							AND mtime BETWEEN $1 AND $2
							AND minor_edit=0
							AND substring(plugin_wiki_page.pagename from 0 for $len) = $3
						ORDER BY mtime DESC",
                                                                 array ($params['begin'],
                                                                        $params['end'],
                                                                        $pat));
					// To remove after wiki tables convert
					pg_set_client_encoding($encoding);

					$cache = array();
					while ($arr = db_fetch_array($wres)) {
						$group_name = $group->getUnixName();
						$page_name = preg_replace('/%2f/i', '/', rawurlencode($arr['pagename']));
						$data = unserialize($arr['versiondata']);
						if (!isset($cache[$data['author']])) {
							$r = db_query_params ('SELECT user_name, user_id FROM users WHERE realname = $1',
										array ($data['author']));

							if ($a = db_fetch_array($r)) {
								$cache[$data['author']]['user_name'] = $a['user_name'];
								$cache[$data['author']]['user_id'] = $a['user_id'];
							} else {
								$cache[$data['author']]['user_name'] = '';
								$cache[$data['author']]['user_id'] = '';
							}
						}
						$arr['user_name'] = $cache[$data['author']]['user_name'];
						$arr['user_id'] = $cache[$data['author']]['user_id'];
						$arr['realname'] = $data['author'];
						$arr['icon']=html_image("ic/wiki20g.png", 20, 20, array('alt'=>'Wiki'));
						$arr['title'] = 'Wiki Page '.$arr['pagename'];
						$arr['link'] = '/wiki/g/'.$group_name.'/'.$page_name;
						$arr['description']= $arr['title'];
						$params['results'][] = $arr;
					}
				}
			}
		}
	}

	function site_admin_option_hook($params) {
		echo '<li>'.util_make_link('/wiki/wikilist.php', _('List of active wikis in Forge')).'</li>';
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
