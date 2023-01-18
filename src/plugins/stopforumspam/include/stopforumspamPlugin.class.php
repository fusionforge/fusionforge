<?php
/**
 * stopforumspamPlugin Class
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class stopforumspamPlugin extends Plugin {
	function __construct($id=0) {
		parent::__construct($id) ;
		$this->name = "stopforumspam";
		$this->text = "StopForumSpam"; // To show in the tabs, use...
	}

	function CallHook($hookname, &$params) {
		global $use_stopforumspamplugin,$G_SESSION,$HTML;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("stopforumspam")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . '&pluginname=' . $this->name; // we indicate the part we're calling is the user one
				echo $HTML->PrintSubMenu (array ($text),
						  array ('/plugins/stopforumspam/index.php' . $param ));

			}
		elseif ($hookname == "blahblahblah") {
			// ...
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
