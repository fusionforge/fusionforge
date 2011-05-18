<?php
 /**
 * Copyright (c) STMicroelectronics, 2005. All Rights Reserved.
 *
 * Originally written by Jean-Philippe Giola, 2005
 *
 * This file is a part of codendi.
 *
 * codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * $Id$
 */

/*
 * ForumML New Thread submission form
 * 
 */ 
require_once('env.inc.php'); 
require_once('pre.php');
require_once('preplugins.php');
require_once('forumml_utils.php');
require_once('mailman/www/mailman_utils.php');
require_once('mailman/include/MailmanList.class.php');
//require_once('common/plugin/PluginManager.class.php');
require_once(dirname(__FILE__).'/../include/ForumML_FileStorage.class.php');
require_once(dirname(__FILE__).'/../include/ForumML_HTMLPurifier.class.php');

$plugin_manager =& PluginManager::instance();
$p =& $plugin_manager->getPluginByName('forumml');
if ($p && $plugin_manager->isPluginAvailable($p) && $p->isAllowed()) {

	$request =& HTTPRequest::instance();
	
	if ($request->valid(new Valid_UInt('group_id'))) {
		$group_id = $request->get('group_id');
	} else {
		$group_id = "";
	}
	
	// Checks 'list' parameter
	if (! $request->valid(new Valid_UInt('list'))) {
		exit_error(_('Error'),_('No list specified'));
	} else {
		$list_id = $request->get('list');
$list = new MailmanList($group_id,$list_id);
		if (!isLogged() || ($list->isPublic()!=1 && !$current_user->isMember($group_id))) {
			exit_error(_('error'),_('You are not allowed to access this page'));
		}		
		if ($list->getStatus() !=3) {
			exit_error(_('Error'),_('The mailing  list does not exist or is inactive'));
		}
	}

	// If message is posted, send a mail
	if ($request->isPost() && $request->get('post')) {
		// Checks if mail subject is empty
		$vSub = new Valid_String('subject');
		$vSub->required();
		if (! $request->valid($vSub)) {		
			$feedback .=_('Submit failed you must specify the mail subject.');
		} else {
			// process the mail
			$return = plugin_forumml_process_mail($p);
			if ($return) {
				$feedback .=_('There can be some delay before to see the message in the archives.')._(' Redirecting to archive page, please wait ...');
				//htmlRedirect('/plugins/forumml/message.php?'. http_build_query(array(
				//    'group_id' => $group_id,
				//    'list'     => $list_id,
				    //'topic'    => 0
             //   )));
			}
		}
	}

	$params['title'] = 'ForumML';
	$params['group'] = $group_id;
	$params['toptab'] = 'mail';
	$params['help'] = "CommunicationServices.html#MailingLists";
	mailman_header($params);
		
	if ($request->isPost() && $request->get('post') && $request->valid($vSub)) {
		if (isset($return) && $return) {
			// wait few seconds before redirecting to archives page
			echo "<script> setTimeout('window.location=\"/plugins/forumml/message.php?group_id=".$group_id."&list=".$list_id."\"',3000) </script>";
		}
	}

	$list_link = '<a href="/plugins/forumml/message.php?group_id='.$group_id.'&list='.$list_id.'">'.$list->getName().'</a>';
	echo '<H2><b>'._('Mailing List ').$list_link._(' - New Thread').'</b></H2>
	<a href="/plugins/forumml/message.php?group_id='.$group_id.'&list='.$list_id.'">['._('Browse Archives').']</a><br><br>
	<H3><b>'._('Submit a new thread').'</b></H3>';

	// New thread form
	echo '<script type="text/javascript" src="scripts/cc_attach_js.php"></script>';
	echo "<form name='form' method='post' enctype='multipart/form-data'>
	<table>
    <tr>
		<td valign='top' align='left'><b> "._('Subject').":&nbsp;</b></td>
		<td align='left'><input type=text name='subject' size='80'></td>
	</tr></table>";
	echo '<table>
    <tr>
		<td align="left">
			<p><a href="javascript:;" onclick="addHeader(\'\',\'\',1);">['.('Add cc').']</a>
			- <a href="javascript:;" onclick="addHeader(\'\',\'\',2);">['._('Attach :').']</a></p>
			<input type="hidden" value="0" id="header_val" />
			<div id="mail_header"></div></td></tr></table>';
	echo "<table><tr>
			<td valign='top' align='left'><b>"._('Message :')."&nbsp;</b></td>
			<td align='left'><textarea rows='20' cols='100' name='message'></textarea></td>
		</tr>
		<tr>
			<td></td>
			<td><input type='submit' name='post' value='"._('Submit')."'>
				<input type='reset' value='"._('Erase')."'></td>
		</tr>
	</table></form>";

	mail_footer($params);

} else {
	header('Location: '.get_server_url());
}

?>
