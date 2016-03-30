<?php
/**
 * Copyright 2010 (c) Mélanie Le Bail
 * Copyright 2014,2015 Franck Villaume - TrivialDev
 * Copyright 2015, Sieu Truc
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

$GLOBALS['mailman_bin_dir'] =  '/usr/lib/mailman/bin';
$GLOBALS['mailman_lib_dir'] = '/var/lib/mailman';
$GLOBALS['forumml_arch'] = '/var/lib/mailman/archives';
$GLOBALS['forumml_tmp'] = '/var/spool/forumml';
$GLOBALS['forumml_dir'] = forge_get_config('data_path').'/forumml';
$GLOBALS['sys_lf'] = "\n";

function isLogged(){
	return session_loggedin();
}


// DEPRECATED: use session_redirect
function htmlRedirect($url) {
	session_redirect($url);
}

function htmlIframe($url, $poub = array()) {
	global $HTML, $group_id;
	$project = group_get_object($group_id);
	if (isset($poub['id'])) {
		$id = $poub['id'];
	} else {
		$id = 'default_id_htmliframe';
	}
	if (isset($poub['absolute'])) {
		$absolute = $poub['absolute'];
	} else {
		$absolute = false;
	}
	if (!empty($url)) {
		if (! $absolute) {
			$url = util_make_uri($url);
		}
		echo html_e('iframe', array('src' => $url, 'id' => $id, 'width' => '100%', 'frameborder' =>0), '', false);
		html_use_jqueryautoheight();
		echo $HTML->getJavascripts();
		echo '<script type="text/javascript">//<![CDATA[
			jQuery(\'#'.$id.'\').iframeAutoHeight({heightOffset: 50});
			jQuery(\'#'.$id.'\').load(function (){
					if (this.contentWindow.location.href == "'.util_make_url('/projects/'.$project->getUnixName()).'/") {
						window.location.href = this.contentWindow.location.href;
					}
				});
			//]]></script>';
	}
}

function htmlIframeResizer($url, $poub = array()) {
	global $HTML, $group_id;
	$project = group_get_object($group_id);
	if (isset($poub['id'])) {
		$id = $poub['id'];
	} else {
		$id = 'default_id_htmliframe';
	}
	if (isset($poub['absolute'])) {
		$absolute = $poub['absolute'];
	} else {
		$absolute = false;
	}
	if (!empty($url)) {
		if (! $absolute) {
			$url = util_make_uri($url);
		}
		echo html_e('iframe', array('src' => $url, 'id' => $id, 'width' => '100%', 'frameborder' =>0), '', false);
		html_use_iframeresizer();
		echo $HTML->getJavascripts();
		echo '<script type="text/javascript">//<![CDATA[
			jQuery(\'#'.$id.'\').iFrameResize();
			function messageHandler (evt) {
				var matches = jQuery(\'#'.$id.'\')[0].src.match(/^(https?\:\/\/[^\/?#]+)(?:[\/?#]|$)/i);
				var domain = matches && matches[1];
				if (evt.origin === domain && evt.data.indexOf("http") === 0) {
					window.location.href = evt.data;
				}
			}
			if (window.addEventListener) {
				// For standards-compliant web browsers
				window.addEventListener("message", messageHandler, false);
			} else {
				window.attachEvent("onmessage", messageHandler);
			}
			//]]></script>';
	}
}

function helpButton($help) {
}

function getIcon($url, $w = 16, $h = 16, $args = array()) {
	echo html_image($url, $w, $h, $args);
}

function getImage($img) {
	echo util_make_url($GLOBALS['HTML']->imgroot.$img);
}

function get_server_url() {
	return util_make_url('');
}
