<?php
/**
 * Copyright (C) 2006 Alain Peyrat, Alcatel-Lucent
 * Copyright (C) 2010 Alain Peyrat <aljeux@free.fr>
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014,2016, Franck Villaume - TrivialDev
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The provided file ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

class blocksPlugin extends Plugin {

	function __construct() {
		parent::__construct();
		$this->name = "blocks";
		$this->text = _("Blocks"); // To show in the tabs, use...
		$this->pkg_desc =
_("This plugin contains the Blocks subsystem of FusionForge. It allows each
FusionForge project to have its own Blocks, and gives some
control over it to the project's administrator.");
		$this->hooks[] = "groupisactivecheckbox"; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost"; //
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page fro group
		$this->hooks[] = "blocks"; // to show up in the admin page fro group
		$this->hooks[] = 'widget_instance';
		$this->hooks[] = 'widgets';
	}

	function project_admin_plugins($params) {
		// this displays the link in the project admin options page to it's blocks administration
		$group = group_get_object($params['group_id']);
		if ($group && $group->usesPlugin ( $this->name )) {
			echo html_e('p', array(), util_make_link('/plugins/blocks/index.php?id='.$group->getID().'&type=admin&pluginname='.$this->name, _('Blocks Admin')));
		}
	}

	function blocks($params) {
		// Check if block is active and if yes, display the block.
		// Return true if plugin is active, false otherwise.
		$group = group_get_object($GLOBALS['group_id']);
		if ($group && $group->usesPlugin ( $this->name )) {
			$content = $this->renderBlock($params);
			if ($content !== false) {
				echo $content;
				return true;
			}
		}
		return false;
	}

	function getTitleBlock($name) {
		$group_id = $GLOBALS['group_id'];
		$res = db_query_params('SELECT title
				FROM plugin_blocks
				WHERE group_id=$1
				AND name=$2
				AND status=1',
				array($group_id, $name)); // 1 is for active
		if (db_numrows($res)== 0) {
			return false;
		} else {
			return db_result($res,0,"title");
		}
	}

	function getContentBlock($name) {
		$group_id = $GLOBALS['group_id'];
		$res = db_query_params('SELECT content
				FROM plugin_blocks
				WHERE group_id=$1
				AND name=$2
				AND status=1',
				array($group_id, $name)); // 1 is for active
		if (db_numrows($res)== 0) {
			return false;
		} else {
			return db_result($res,0,"content");
		}
	}
	function renderBlock($name) {
		$content = $this->getContentBlock($name);
		if ($content === false) {
			return false;
		} elseif ($content) {
			return $this->parseContent($content);
		} else {
			return '<table class="fullwidth boxed">'.
					'<tr><td class="align-center">block: '.$name.'</td></tr></table><br />';
		}
	}

	function parseContent($text) {
		global $HTML;

		$text = preg_replace_callback('/<p>{boxTop (.*?)}<\/p>/i', function($m) { return $HTML->boxTop($m[1]); }, $text);
		$text = preg_replace_callback('/{boxTop (.*?)}/i', function($m) { $HTML->boxTop($m[1]); }, $text);
		$text = preg_replace_callback('/<p>{boxMiddle (.*?)}<\/p>/i', function($m) { $HTML->boxMiddle($m[1]); }, $text);
		$text = preg_replace_callback('/{boxMiddle (.*?)}/i', function($m) { $HTML->boxMiddle($m[1]); }, $text);
		$text = preg_replace('/<p>{boxBottom}<\/p>/i', $HTML->boxBottom(), $text);
		$text = preg_replace('/{boxBottom}/i', $HTML->boxBottom(), $text);

		$text = preg_replace('/<p>{boxHeader}/i', '<hr />', $text);
		$text = preg_replace('/{boxHeader}/i', '<hr />', $text);
		$text = preg_replace('/{boxFooter}<\/p>/i', '<hr />', $text);
		$text = preg_replace('/{boxFooter}/i', '<hr />', $text);

		return $text;
	}

	function widget_instance($params) {
		require_once 'common/widget/WidgetLayoutManager.class.php';

		// PROJECT
		if ($params['widget'] == 'plugin_blocks_project_summary') {
			require_once 'blocks_Widget_ProjectSummary.class.php';
			$params['instance'] = new blocks_Widget_ProjectSummary(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
		// FORGE HOMEPAGE
		if ($params['widget'] == 'plugin_blocks_home_summary') {
			require_once 'blocks_Widget_HomeSummary.class.php';
			$params['instance'] = new blocks_Widget_HomeSummary(WidgetLayoutManager::OWNER_TYPE_HOME, 0);
		}


	}
	function widgets($params) {
		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_GROUP) {
			$group = group_get_object($GLOBALS['group_id']);
			if ( !$group || !$group->usesPlugin ( $this->name ) ) {
				return false;
			}
			require_once 'common/widget/WidgetLayoutManager.class.php';
			$params['fusionforge_widgets'][] = 'plugin_blocks_project_summary';
			return true;
		} else if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_HOME) {
			$params['fusionforge_widgets'][] = 'plugin_blocks_home_summary';
			return true;
		}
		return false;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
