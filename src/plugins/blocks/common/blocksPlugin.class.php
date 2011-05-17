<?php

/*
 * Copyright (C) 2006 Alain Peyrat, Alcatel-Lucent
 * Copyright (C) 2010 Alain Peyrat <aljeux@free.fr>
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

forge_define_config_item('templates_file','blocks','$core/source_path/plugins/blocks/etc/templates.json');

class blocksPlugin extends Plugin {
	function blocksPlugin () {
		$this->Plugin() ;
		$this->name = "blocks" ;
		$this->text = "Blocks" ; // To show in the tabs, use...
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page fro group
		$this->hooks[] = "blocks"; // to show up in the admin page fro group
	}

	function CallHook ($hookname, &$params) {
		if ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it's  blocks administration
			$group_id = $params['group_id'];
			$group = group_get_object($group_id);
			if ( $group->usesPlugin ( $this->name ) ) {
				echo '<p><a href="/plugins/blocks/index.php?id=' . $group->getID() . '&amp;type=admin&amp;pluginname=' . $this->name . '">' . _("Blocks Admin") . '</a></p>';
			}
		} elseif ($hookname == "blocks") {
			return $this->blocks($params);
		} 
	}
	function blocks($params) {
		// Check if block is active and if yes, display the block.
		// Return true if plugin is active, false otherwise.
		$group = group_get_object($GLOBALS['group_id']);
		if ( $group && $group->usesPlugin ( $this->name ) ) {

			$c = $this->renderBlock($params);
			if ($c !== false) {
				echo $c;
				return true;
			}
		}
		return false;
	}

	function renderBlock($name) {
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
			$content = db_result($res,0,"content");
			if ($content) {
				return $this->parseContent($content).'<br />';
			} else {
				return "<table width=\"100%\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\">" .
						"<tr><td align=\"center\">block: $name</td></tr></table><br />";
			}
		}
	}
	
	function parseContent($t) {
		global $HTML;

		$t = preg_replace('/<p>{boxTop (.*?)}<\/p>/ie', '$HTML->boxTop(\'$1\')', $t);
		$t = preg_replace('/{boxTop (.*?)}/ie', '$HTML->boxTop(\'$1\')', $t);
		$t = preg_replace('/<p>{boxMiddle (.*?)}<\/p>/ie', '$HTML->boxMiddle(\'$1\')', $t);
		$t = preg_replace('/{boxMiddle (.*?)}/ie', '$HTML->boxMiddle(\'$1\')', $t);
		$t = preg_replace('/<p>{boxBottom}<\/p>/i', $HTML->boxBottom(), $t);
		$t = preg_replace('/{boxBottom}/i', $HTML->boxBottom(), $t);

		$t = preg_replace('/<p>{boxHeader}/i', '<hr />', $t);
		$t = preg_replace('/{boxHeader}/i', '<hr />', $t);
		$t = preg_replace('/{boxFooter}<\/p>/i', '<hr />', $t);
		$t = preg_replace('/{boxFooter}/i', '<hr />', $t);
		
		return $t;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
