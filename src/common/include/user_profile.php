<?php
/**
 * Developer Profile Info
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2018, Franck Villaume - TrivialDev
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

// This code was extracted from user_home.php and factorized in a function

/**
* Display user's profile / personal info either in compact or extensive way
*
* @param	object		$user
* @param	bool		$compact
* @param	string|bool	$title
* @return	string		HTML
*/
function user_personal_information($user, $compact = false, $title = false) {
	global $HTML;
	$user_id = $user->getID();

	$user_logo = false;
	$params = array('user_id' => $user_id, 'size' => 'l', 'content' => '');
	plugin_hook_by_reference('user_logo', $params);
	if ($params['content']) {
		$user_logo = $params['content'];
	}
	$html = $HTML->listTableTop(array(), array(), 'full');
	if ($compact && $title) {
		$cells[] = array($title, 'colspan' => 2);
		$html .= $HTML->multiTableRow(array(), $cells);
	}

	$cells = array();
	if ($user_logo) {
		$cells[] = array($user_logo, 'style' => 'width: 150px');
	} else {
		$cells[][] = '';
	}
	$subtable = $HTML->listTableTop(array(), array(), 'my-layout-table', 'user-profile-personal-info');
	$subcells[][] = _('User Id')._(':');

	//print '<div property ="foaf:member" content="fusionforge:ForgeCommunity">';
	//echo '</div>';
	// description as a FusionForge Community member
	//print '<div property ="dc:Identifier" content="'.$user_id.'">';
	if (session_loggedin() && forge_check_global_perm('forge_admin')) {
		$user_id_html = util_make_link('/admin/useredit.php?user_id='.$user_id, $user_id);
	} else {
		$user_id_html = $user_id;
		//echo '</div>';
	}
	$subcellcontent = '<strong>'. $user_id_html .'</strong>';
	if(!$compact && forge_get_config('use_people')) {
		$subcellcontent .= '(' . util_make_link ('/people/viewprofile.php?user_id='.$user_id,'<strong>'._('Skills Profile').'</strong>') . ')';
	}
	$subcells[][] = $subcellcontent;
	$subtable .= $HTML->multiTableRow(array(), $subcells);
	$subcells = array();
	$subcells[][] = _('Login Name') . _(': ');
	$subcells[][] = '<strong><span property="sioc:name">' . $user->getUnixName() . '</span></strong>';
	$subcells = array();
	$subcells[][] = _('Real Name') . _(': ');
	$user_title = $user->getTitle();
	$user_title_name = ($user_title ? ($user_title . ' ') : '') . $user->getRealName();
	$subcells[][] = '<div rev="foaf:account">
					<div about="#me" typeof="foaf:Person">
						<strong><span property="foaf:name">'. $user_title_name .'</span></strong>
					</div>
				</div>';
	$subtable .= $HTML->multiTableRow(array(), $subcells);
	if (!$compact && forge_get_config('user_display_contact_info')) {
		$user_mail=$user->getEmail();
		$user_mailsha1=$user->getSha1Email();
		$subcells = array();
		$subcells[][] = _('Email Address') . _(': ');
		$subcells[][] = '<strong>'.

		// Removed for privacy reasons
		//print '<span property="sioc:email" content="'. $user_mail .'">';
				'<span property="sioc:email_sha1" content="'. $user_mailsha1 .'">' .
		util_make_link('/sendmessage.php?touser='.$user_id, str_replace('@',' @nospam@ ',$user_mail)) .
				'</span>
				</strong>';
		$subtable .= $HTML->multiTableRow(array(), $subcells);

		if ($user->getAddress() || $user->getAddress2()) {
			$subcells = array();
			$subcells[][] = _('Address')._(':');
			$subcells[][] = $user->getAddress() .'<br/>'. $user->getAddress2();
			$subtable .= $HTML->multiTableRow(array(), $subcells);
		}

		if ($user->getPhone()) {
			$subcells = array();
			$subcells[][] = _('Phone')._(':');
			//print '<div property="foaf:phone" content="'.$user->getPhone().'">';
			$subcells[][] = $user->getPhone();
			//echo '</div>';
			$subtable .= $HTML->multiTableRow(array(), $subcells);
		}

		if ($user->getFax()) {
			$subcells = array();
			$subcells[][] = _('Fax')._(':');
			$subcells[][] = $user->getFax();
			$subtable .= $HTML->multiTableRow(array(), $subcells);
		}
	}
	$subcells = array();
	$subcells[][] = _('Site Member Since')._(':');
	$subcells[][] = '<strong>'. relative_date($user->getAddDate()). '</strong>';
	$subtable .= $HTML->multiTableRow(array(), $subcells);
	if ($compact) {
		$user_uri = util_make_url('/users/'. $user->getUnixName() . '/');
		$subcells = array();
		$subcells[][] = '<small>'. _('URI')._(':') .'</small>';
		$subcells[][] = '<small>'.util_make_link_u($user->getUnixName(), $user->getID(), util_make_url_u($user->getUnixName(), $user->getID())).'</small>';
		$subtable .= $HTML->multiTableRow(array(), $subcells);
	}
	$subcells = array();
	$subcells[][] = _('Following').'/'._('Followers')._(':');
	$subcells[][] = count($user->getMonitorIds()).'/'.count($user->getMonitorByIds());
	$subtable .= $HTML->multiTableRow(array(), $subcells);
	if (forge_get_config('use_diary') && session_loggedin() && ($user->getID() != user_getid())) {
		if ($user->isMonitoredBy(user_getid())) {
			$option = 'stop';
			$titleMonitor = _('Stop monitoring this user');
			$image = $HTML->getStopMonitoringPic($titleMonitor, '');
		} else {
			$option = 'start';
			$titleMonitor = _('Start monitoring this user');
			$image = $HTML->getStartMonitoringPic($titleMonitor, '');
		}
		$subcells = array();
		$subcells[][] = $titleMonitor;
		$subcells[][] = util_make_link('/developer/monitor.php?diary_user='.$user->getID(), $image, array('title' => $titleMonitor));
		$subtable .= $HTML->multiTableRow(array(), $subcells);
	}
	$subtable .= $HTML->listTableBottom();
	$cells[][] = $subtable;
	$html .= $HTML->multiTableRow(array(), $cells);
	$html .= $HTML->listTableBottom();

	return $html;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
