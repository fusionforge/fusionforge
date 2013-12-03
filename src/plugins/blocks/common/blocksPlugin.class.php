<?php

/*
 * Copyright (C) 2006 Alain Peyrat, Alcatel-Lucent
 * Copyright (C) 2010 Alain Peyrat <aljeux@free.fr>
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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

/*
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
		$this->Plugin() ;
		$this->name = "blocks" ;
		$this->text = _("Blocks"); // To show in the tabs, use...
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page fro group
		$this->hooks[] = "blocks"; // to show up in the admin page fro group
		$this->hooks[] = 'widget_instance';
		$this->hooks[] = 'widgets';
	}

	function project_admin_plugins($params) {
		// this displays the link in the project admin options page to it's blocks administration
		$group = group_get_object($params['group_id']);
		if ($group && $group->usesPlugin ( $this->name )) {
			echo '<p><a href="/plugins/blocks/index.php?id=' . $group->getID() . '&amp;type=admin&amp;pluginname=' . $this->name . '">' . _("Blocks Admin") . '</a></p>';
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
			return $this->parseContent($content).'<br />';
		} else {
			return "<table width=\"100%\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\">" .
					"<tr><td align=\"center\">block: $name</td></tr></table><br />";
		}
	}

	function parseContent($text) {
		global $HTML;

		$text = preg_replace('/<p>{boxTop (.*?)}<\/p>/ie', '$HTML->boxTop(\'$1\')', $text);
		$text = preg_replace('/{boxTop (.*?)}/ie', '$HTML->boxTop(\'$1\')', $text);
		$text = preg_replace('/<p>{boxMiddle (.*?)}<\/p>/ie', '$HTML->boxMiddle(\'$1\')', $text);
		$text = preg_replace('/{boxMiddle (.*?)}/ie', '$HTML->boxMiddle(\'$1\')', $text);
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

		$user = UserManager::instance()->getCurrentUser();

		// MY
//		if ($params['widget'] == 'plugin_hudson_my_jobs') {
//			require_once('hudson_Widget_MyMonitoredJobs.class.php');
//			$params['instance'] = new hudson_Widget_MyMonitoredJobs($this);
//		}
//		if ($params['widget'] == 'plugin_hudson_my_joblastbuilds') {
//			require_once('hudson_Widget_JobLastBuilds.class.php');
//			$params['instance'] = new hudson_Widget_JobLastBuilds(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
//		}
//		if ($params['widget'] == 'plugin_hudson_my_jobtestresults') {
//			require_once('hudson_Widget_JobTestResults.class.php');
//			$params['instance'] = new hudson_Widget_JobTestResults(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
//		}
//		if ($params['widget'] == 'plugin_hudson_my_jobtesttrend') {
//			require_once('hudson_Widget_JobTestTrend.class.php');
//			$params['instance'] = new hudson_Widget_JobTestTrend(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
//		}
//		if ($params['widget'] == 'plugin_hudson_my_jobbuildhistory') {
//			require_once('hudson_Widget_JobBuildHistory.class.php');
//			$params['instance'] = new hudson_Widget_JobBuildHistory(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
//		}
//		if ($params['widget'] == 'plugin_hudson_my_joblastartifacts') {
//			require_once('hudson_Widget_JobLastArtifacts.class.php');
//			$params['instance'] = new hudson_Widget_JobLastArtifacts(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
//		}

		// PROJECT
		if ($params['widget'] == 'plugin_blocks_project_summary') {
			require_once 'blocks_Widget_ProjectSummary.class.php';
			$params['instance'] = new blocks_Widget_ProjectSummary(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
	}
	function widgets($params) {
		$group = group_get_object($GLOBALS['group_id']);
		if ( !$group || !$group->usesPlugin ( $this->name ) ) {
			return false;
		}

		require_once 'common/widget/WidgetLayoutManager.class.php';
//		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_USER) {
//			$params['codendi_widgets'][] = 'plugin_hudson_my_jobs';
//			$params['codendi_widgets'][] = 'plugin_hudson_my_joblastbuilds';
//			$params['codendi_widgets'][] = 'plugin_hudson_my_jobtestresults';
//			$params['codendi_widgets'][] = 'plugin_hudson_my_jobtesttrend';
//			$params['codendi_widgets'][] = 'plugin_hudson_my_jobbuildhistory';
//			$params['codendi_widgets'][] = 'plugin_hudson_my_joblastartifacts';
//		}
		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_GROUP) {
			$params['codendi_widgets'][] = 'plugin_blocks_project_summary';
		}
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
