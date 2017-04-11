<?php
/**
 * FusionForge login form functions
 *
 * Copyright 2011, Roland Mas
 * Copyright 2014-2015, Franck Villaume - TrivialDev
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

function validate_return_to(&$return_to = '/') {
	$newrt = '/';

	if ($return_to) {
		$tmpreturn=explode('?',$return_to);
		$rtpath = $tmpreturn[0] ;

		if (@is_file(forge_get_config('url_root').$rtpath)
		    || @is_dir(forge_get_config('url_root').$rtpath)
		    || (strpos($rtpath,'/projects') == 0)
		    || (strpos($rtpath,'/plugins/mediawiki') == 0)) {
			$newrt = $return_to;
		}
	}

	$return_to = $newrt;
}

function display_login_page($return_to = '/', $triggered = false, $attempts = 1, $previousLogin = null) {
	display_login_form($return_to, $triggered, true, $attempts, $previousLogin);
}

function display_login_form($return_to = '/', $triggered = false, $full_page = false, $attempts = 1, $previousLogin = null) {
	global $HTML;

	validate_return_to($return_to);

	$params = array();
	$params['return_to'] = $return_to;
	$params['html_snippets'] = array();
	$params['transparent_redirect_urls'] = array();
	$params['attempts'] = $attempts;
	$params['previousLogin'] = $previousLogin;
	plugin_hook_by_reference('display_auth_form', $params);

	if ($full_page) {
		if (count($params['html_snippets']) == 1
		    && count($params['transparent_redirect_urls']) == 1) {
			$urls = array_values($params['transparent_redirect_urls']);
			session_redirect_external($urls[0]);
		}

		$HTML->header(array('title'=>_('Login')));
	}

	if ($triggered) {
		echo $HTML->warning_msg(_("You've been redirected to this login page because you have tried accessing a page that was not available to you as an anonymous user."));
	}

	if (count ($params['html_snippets']) > 1) {
		$use_tabber = true;
		echo '<div id="tabber">';
	} else {
		$use_tabber = false;
	}

	$htmlCodeUl = '<ul>';
	$htmlCode = '';
	foreach ($params['html_snippets'] as $p => $s) {
		$plugin = plugin_get_object($p);
		if ($use_tabber) {
			$htmlCodeUl .= '<li><a href="#tabber-'.$plugin->name.'">'.$plugin->text.'</a></li>';
			$htmlCode .= '<div id="tabber-'.$plugin->name.'" class="tabbertab" title="'.$plugin->text.'">';
		}
		$htmlCode .= $s;
		if ($use_tabber) {
			$htmlCode .= '</div>';
		}
	}
	$htmlCodeUl .= '</ul>';
	if ($use_tabber) {
		echo $htmlCodeUl;
	}

	echo $htmlCode;

	if ($use_tabber) {
		echo '</div>';
	}

	if ($full_page) {
		$HTML->footer();
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
