<?php
/**
 * GForge Plugin SVNHudson Class
 *
 * Copyright 2009 (c) Alain Peyrat <alain.peyrat@alcatel-lucent.com>
 *
 * This file is part of GForge-plugin-svnhudson
 *
 * GForge-plugin-svnhudson is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge-plugin-svnhudson is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge-plugin-svnhudson; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 *
 */
/**
 * The svnhudsonPlugin class. It implements the Hooks for the presentation
 *  of table in hudson and task in detailed mode.
 *
 */

class messagePlugin extends Plugin {

	function messagePlugin () {
		$this->Plugin() ;
		$this->name = "message" ;
		$this->text = _('Message');
		$this->hooks[] = 'message';
		$this->hooks[] = 'htmlhead';
		$this->hooks[] = 'site_admin_option_hook';
	}

	function htmlhead() {
		use_javascript('/scripts/jquery/jquery-1.4.2.min.js');
		use_javascript('/plugins/message/js/message.js');
	}
	
	function site_admin_option_hook() {
		echo '<li>' . util_make_link ('/plugins/message/index.php', _('Configure Global Message')) . '</li>';
	}

	function message() {
		$res = db_query_params('SELECT message FROM plugin_message', array());
		if ($res && db_numrows($res)>0) {
			echo '<div id="message_box">';
			echo html_image('ic/close.png','','', array('id' => 'message_close', 'style' => 'float:right;cursor:pointer'));
			echo db_result($res, 0, 'message');
			echo '</div>';
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
