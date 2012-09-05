<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'include/pre.php';
require_once $gfwww.'search/include/renderers/HtmlGroupSearchRenderer.class.php';

class AdvancedSearchHtmlSearchRenderer extends HtmlGroupSearchRenderer {

	/**
	 * group id
	 *
	 * @var int $groupId
	 */
	var $groupId;

	/**
	 * the words to search for
	 *
	 * @var string $words
	 */
	var $words;

	/**
	 * flag to define whether the result must contain all words or only one of them
	 *
	 * @var boolean $isExact
	 */
	var $isExact;

	/**
	 * selected parents sections
	 *
	 * @var array $selectedParentSections
	 */
	var $selectedParentSections = array();

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 *
	 */
	function AdvancedSearchHtmlSearchRenderer($words, $offset, $isExact, $groupId) {
		$this->groupId = $groupId;
		$this->words = $words;
		$this->isExact = $isExact;
		$searchQuery =& $this->searchQuery;

		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_ADVANCED, $words, $isExact, $searchQuery, $groupId);
	}

	/**
	 * flush - overwrites the flush method from htmlrenderer
	 */
	function flush() {
		$this->writeHeader();
		$this->writeBody();
		$this->writeFooter();
	}

	/**
	 * writeHeader - write the header of the output
	 */
	function writeHeader() {
		site_project_header(array('title' => _('Advanced search'), 'group' => $this->groupId, 'toptab' => 'none'));
		$sectionarray = $this->getSectionArray();
		$this->handleTransferInformation($sectionarray);

		echo $this->getAdvancedSearchBox($sectionarray, $this->groupId, $this->words, $this->isExact);
	}

	/**
	 * writeBody - write the Body of the output
	 */
	function writeBody() {
		if (strlen($this->words) < 3) {
			echo '<p class="error">'._('Error: Under min length search').'</p>';
		} else {
			echo $this->getResult();
		}
	}

	/**
	 * getResult - returns the Body of the output
	 *
  	 * @return string result of all selected searches
	 */
	function getResult() {
		$html = '';

		if (in_array('short_forum', $this->selectedParentSections)) {
			$renderer = new ForumsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId, $this->getSelectedChildSections('short_forum'));
			$html .= $this->getPartResult($renderer, 'short_forum', _('Forum Search Results'));
		}

		if (in_array('short_tracker', $this->selectedParentSections)) {
			$renderer = new TrackersHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId,  $this->getSelectedChildSections('short_tracker'));
			$html .= $this->getPartResult($renderer,  'short_tracker', _('Tracker Search Results'));
		}

		if (in_array('short_pm', $this->selectedParentSections)) {
			$renderer = new TasksHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId, $this->getSelectedChildSections('short_pm'));
			$html .= $this->getPartResult($renderer, 'short_pm', _('Task Search Results'));
		}

		if (in_array('short_docman', $this->selectedParentSections)) {
			$renderer = new DocsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId, $this->getSelectedChildSections('short_docman'));
			$html .= $this->getPartResult($renderer, 'short_docman', _('Documentation Search Results'));
		}

		if (in_array('short_files', $this->selectedParentSections)) {
			$renderer = new FrsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId, $this->getSelectedChildSections('short_files'));
			$html .= $this->getPartResult($renderer, 'short_files', _('Files Search Results'));
		}

		if (in_array('short_news', $this->selectedParentSections)) {
			$renderer = new NewsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
			$html .= $this->getPartResult($renderer, 'short_news', _('News Search Results'));
		}

		// This is quite complex but the goal is to extract all the
		// registered plugins to the hook 'search_engines' and call
		// them.
		$pluginManager = plugin_manager_get_object();
		$searchManager = getSearchManager();
		$engines = $searchManager->getAvailableSearchEngines();

		if (isset($pluginManager->hooks_to_plugins['search_engines'])) {
			$p_list = $pluginManager->hooks_to_plugins['search_engines'];
			foreach ($p_list as $p_name) {
				$p_obj = $pluginManager->GetPluginObject($p_name);
				$name = $p_obj->text;
				if (in_array($p_name, $this->selectedParentSections)) {
					reset($engines);
					foreach($engines as $e) {
						if ($e->type == $p_name) {
							$renderer = $e->getSearchRenderer($this->words,
								$this->offset, $this->isExact, $this->groupId);
							$html .= $this->getPartResult($renderer, 'short_'.$p_name, $name);
						}
					}
				}
			}
		}

		return $html.'<br />';
	}

	/**
	* getPartResult - returns the result of the given renderer
	*
  	* @return string result of the renderer
	*/
	function getPartResult($renderer, $section, $title='') {
		$result = '';
		$renderer->searchQuery->executeQuery();
		$query = NULL;

		if ($title === '')
			$title = $section;

		$result .= '<h2><a name="'.$section.'"></a>'.$title.'</h2>';

		if ($renderer->searchQuery->getRowsCount() > 0) {
			if ($renderer->searchQuery->getRowsTotalCount() >= $renderer->searchQuery->getRowsPerPage())
				$result .= '<i>' . sprintf(_('Note: only the first %d results for this category are displayed.'), $renderer->searchQuery->getRowsPerPage()) . '</i>';
			$result .= $GLOBALS['HTML']->listTabletop($renderer->tableHeaders);
			$result .= $renderer->getRows();
			$result .= $GLOBALS['HTML']->listTableBottom();
		} elseif(method_exists($renderer, 'getSections') && (count($renderer->getSections($this->groupId)) == 0)) {
			$result .= '<p>'.sprintf(_('No matches found for <em>%s</em> - No sections available (check your permissions)'), $this->words).'</p>';
		} else {
			$result .= '<p>'.sprintf(_('No matches found for <em>%s</em>'), $this->words).'</p>';
		}
		return $result;
	}

	/**
	* getSectionArray - creates an array of sections in which the user can search
	*
  	* @return array sections
	*/
	function getSectionArray() {
		global $gfwww, $gfcommon;

		$sections = array();
		$group = group_get_object($this->groupId);

		if ($group->usesForum()) {
			require_once $gfwww.'search/include/renderers/ForumsHtmlSearchRenderer.class.php';
			$undersections = ForumsHtmlSearchRenderer::getSections($this->groupId);
			if (count($undersections) > 0) {
				$sections['short_forum'] = $undersections;
			}
		}

		if ($group->usesTracker()) {
			require_once $gfwww.'search/include/renderers/TrackersHtmlSearchRenderer.class.php';
			$undersections = TrackersHtmlSearchRenderer::getSections($this->groupId);
			if (count($undersections) > 0) {
				$sections['short_tracker'] = $undersections;
			}
		}

		if ($group->usesPM()) {
			require_once $gfwww.'search/include/renderers/TasksHtmlSearchRenderer.class.php';
			$undersections = TasksHtmlSearchRenderer::getSections($this->groupId);
			if (count($undersections) > 0) {
				$sections['short_pm'] = $undersections;
			}

		}

		if ($group->usesDocman()) {
			require_once $gfwww.'search/include/renderers/DocsHtmlSearchRenderer.class.php';
			$undersections = DocsHtmlSearchRenderer::getSections($this->groupId);
			if (count($undersections) > 0) {
				$sections['short_docman'] = $undersections;
			}
		}

		if ($group->usesNews()) {
			require_once $gfwww.'search/include/renderers/NewsHtmlSearchRenderer.class.php';
			$sections['short_news'] = true;
		}

		if ($group->usesFRS()) {
			require_once $gfwww.'search/include/renderers/FrsHtmlSearchRenderer.class.php';
			$undersections = FrsHtmlSearchRenderer::getSections($this->groupId);
			if (count($undersections) > 0) {
				$sections['short_files'] = $undersections;
			}
		}

		$pluginManager = plugin_manager_get_object();
		if (isset($pluginManager->hooks_to_plugins['search_engines'])) {
			$p_list = $pluginManager->hooks_to_plugins['search_engines'];
			foreach ($p_list as $p_name) {
				if ($group->usesPlugin($p_name)) {
					$p_obj = $pluginManager->GetPluginObject($p_name);
					$sections[$p_obj->name] = true;
				}
			}
		}

		return $sections;
	}

	/**
	* handleTransferInformation - marks parentsections if child is marked and processes cookie information
	*
	*/
	function handleTransferInformation(&$sectionarray) {
		//get through all sections
		//if a childsection is marked to search in, mark the parent too

		$postArray = _getPostArray();

		foreach($sectionarray as $key => $section) {
			if(is_array($section)) {
				foreach ($postArray as $postkey => $postvalue) {
					if (substr($postkey, 0, strlen($key)) == $key) {
						$this->selectedParentSections[] = $key;
						break;
					}
				}
			} elseif(isset($postArray[$key])) {
					$this->selectedParentSections[] = $key;
			}
		}

	}

	/**
	* getSelectedChildSections - gets all selected child sections from the given parentsection
	*
	* @param string	$parentsection the parentsection
	*
  	* @return array all child sections from the parent section the user wants to search in
	*/
	function getSelectedChildSections($parentsection) {
		$sections = array();
		$postArray = _getPostArray();

		foreach($postArray as $key => $value) {
			if (substr($key, 0, strlen($parentsection)) == $parentsection) {
				if (strlen(substr($key, strlen($parentsection) , strlen($key))) > 0) {
					array_push($sections, urldecode(substr($key, strlen($parentsection), strlen($key))));
				}
			}
		}
		return $sections;
	}

	function getAdvancedSearchBox($sectionsArray, $group_id, $words, $isExact) {
		$res = '';
		// display the searchmask
		$res .= '
        <form class="ff" name="advancedsearch" action="'.getStringFromServer('PHP_SELF').'" method="post">
        <input class="ff" type="hidden" name="search" value="1"/>
        <input class="ff" type="hidden" name="group_id" value="'.$group_id.'"/>
        <div align="center"><br />
            <table>
                <tr class="ff">
                    <td class="ff" colspan ="2">
                        <input class="ff" type="text" size="60" name="words" value="'.stripslashes(htmlspecialchars($words)).'" />
                        <input class="ff" type="submit" name="submitbutton" value="'._('Search').'" />
                    </td>
                </tr>
                <tr class="ff">
                    <td class="ff top">
                        <input class="ff" type="radio" name="mode" value="'.SEARCH__MODE_AND.'" '.($isExact ? 'checked="checked"' : '').' />'._('with all words').'
                    </td>
                    <td class="ff">
                        <input class="ff" type="radio" name="mode" value="'.SEARCH__MODE_OR.'" '.(!$isExact ? 'checked="checked"' : '').' />'._('with one word').'
                    </td>
                </tr>
            </table><br />'
			. $this->createSubSections($sectionsArray) .'
        </div></form>
';

		// Add jquery javascript method for select none/all
		$res .= <<< EOS
<script type="text/javascript">
jQuery(function () {
	jQuery('.checkall').click(function () {
		jQuery(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	jQuery('.checkthemall').click(function () {
		jQuery('#advsearch').find(':checkbox').attr('checked', this.checked);
	});
});
</script>
EOS;
		return $res;
	}

	function createSubSections($sectionsArray) {
		$group_subsection_names = getSubSectionNames();

		$countLines = 0;
		foreach ($sectionsArray as $section) {
			if(is_array($section)) {
				$countLines += (3 + count ($section));
			} else {
				//2 lines one for section name and one for checkbox
				$countLines += 3;
			}
		}

 		$maxCol = 3;
 		$breakLimit = ceil($countLines/$maxCol);
		$break = $breakLimit;
		$countLines = 0;
 		$countCol = 1;

		$return = '
			<table cellspacing="10" cellpadding="1" id="advsearch">
							<tr class="tablecontent">
								<td colspan="3" class="align-center">
									<input type="checkbox" class="checkthemall">'._('Search All').'</a>
								</td>
							</tr>
							<tr class="top tablecontent">
								<td>';
		foreach($sectionsArray as $key => $section) {
			$oldcountlines = $countLines;
			if (is_array($section)) {
				$countLines += (3 + count ($section));
			} else {
				$countLines += 3;
			}

			if ($countLines >= $break) {
 				// if we are closer to the limit with this one included, then
 				// it's better to include it.
 				if (($countCol < $maxCol) && ($countLines - $break) >= ($break - $oldcountlines)) {
					$return .= '</td><td>';
 					$countCol++;
					$break += $breakLimit;
				}
			}

			$return .= '<fieldset>'."\n";
			$return .= '<legend>';
			$return .= '<input type="checkbox" name="'.$key.'_checkall"';
			if (getStringFromRequest($key.'_checkall')) $return .= ' checked="checked"';
			$return .= ' class="checkall" /><a href="#'.$key.'">'.$group_subsection_names[$key].'</a>';
			$return .= "\n";
			$return .= '</legend>';

			if (!is_array($section)) {
				$return .= '		<input type="checkbox" name="'.$key.'"';
				if (getStringFromRequest($key))	$return .= ' checked="checked"';
				$return .= ' class="childCheckBox" />'.$group_subsection_names[$key].'<br />'."\n";
			} else {
				foreach($section as $underkey => $undersection) {
					$return .= '	<input type="checkbox" name="'.$key.$underkey.'"';
					if (getStringFromRequest($key.$underkey)) $return .= ' checked="checked"';
					$return .= ' />'.$undersection.'<br />'."\n";
				}
			}

			$return .= '</fieldset>';

			if ($countLines >= $break) {
				if (($countLines - $break) < ($break - $countLines)) {
					$return .= '</td><td width="33%">';
					$break += $breakLimit;
				}
			}
		}

		return $return.'		</td>
							</tr>
						</table>';

	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
