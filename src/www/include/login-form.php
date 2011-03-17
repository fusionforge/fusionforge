<?php
/**
 * FusionForge login form functions
 *
 * Copyright 2011, Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

use_javascript('/tabber/tabber.js');

function validate_return_to(&$return_to='/') {
	$newrt = '/' ;

	if ($return_to) {
		$tmpreturn=explode('?',$return_to);
		$rtpath = $tmpreturn[0] ;
		
		if (@is_file(forge_get_config('url_root').$rtpath)
		    || @is_dir(forge_get_config('url_root').$rtpath)
		    || (strpos($rtpath,'/projects') == 0)
		    || (strpos($rtpath,'/plugins/mediawiki') == 0)) {
			$newrt = $return_to ;
		}
	}

	$return_to = $newrt;
}

function display_login_page($return_to='/', $triggered=false) {
	display_login_form($return_to, $triggered, true);
}

function display_login_form($return_to='/', $triggered=false, $full_page=false) {
	global $HTML;

	validate_return_to($return_to);

	$params = array();
	$params['return_to'] = $return_to;
	$params['html_snippets'] = array();
	$params['transparent_redirect_urls'] = array();
	plugin_hook_by_reference('display_auth_form', $params);

	if ($full_page) {
		if (count($params['html_snippets']) == 1
		    && count($params['transparent_redirect_urls']) == 1) {
			session_redirect($params['transparent_redirect_urls'][0]);
		}
	
		$HTML->header(array('title'=>'Login'));
	}

	if ($triggered) {
		echo '<p>';
		echo '<div class="warning">' ;
		echo _('You\'ve been redirected to this login page because you have tried accessing a page that was not available to you as an anonymous user.');
		echo '</div> ' ;
		echo '</p>';
	}

	
	if (count ($params['html_snippets']) > 1) {
		$use_tabber = true;
		echo '<div id="tabber" class="tabber">';
	} else {
		$use_tabber = false;
	}

	foreach ($params['html_snippets'] as $p => $s) {
		$plugin = plugin_get_object($p);
		if ($use_tabber) {
			echo '<div class="tabbertab" title="'.$plugin->text.'">';
		}
		echo $s;
		if ($use_tabber) {
			echo '</div>';
		}
	}
	
	if ($use_tabber) {
		echo '</div>';
	}

	if ($full_page) {
		$HTML->footer(array());
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
