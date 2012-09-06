<?php

/**
 * twitterPlugin Class
 *
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

class twitterPlugin extends ForgeAuthPlugin {
	public function __construct() {

		$this->ForgeAuthPlugin() ;

		$this->name = 'twitter';
		$this->text = 'Twitter'; // To show in the tabs, use...
		$this->_addHook("user_personal_links");//to make a link to the user's personal part of the plugin
		$this->_addHook("usermenu");
		$this->_addHook("userisactivecheckbox"); // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost"); //
		
	}

	function usermenu() {
		global $G_SESSION,$HTML;
		$text = $this->text; // this is what shows in the tab
		if ($G_SESSION->usesPlugin($this->name)) {
			echo  $HTML->PrintSubMenu (array ($text), array ('/plugins/twitter/index.php') );
		}
	}
	
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
