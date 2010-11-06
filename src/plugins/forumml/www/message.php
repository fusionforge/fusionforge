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
 * You should have received a copy of the GNU General Public License
 * along with codendi; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * $Id$
 */

/*
 * ForumML Archives Browsing page
 *  
 */

require_once('env.inc.php');
require_once('pre.php');
require_once 'preplugins.php';
require_once('forumml_utils.php');
require_once('mailman/www/mailman_utils.php');
require_once('mailman/include/MailmanList.class.php');
//require_once('common/plugin/PluginManager.class.php');
require_once(dirname(__FILE__).'/../include/ForumML_FileStorage.class.php');
require_once(dirname(__FILE__).'/../include/ForumML_HTMLPurifier.class.php');
require_once(dirname(__FILE__).'/../include/ForumML_MessageManager.class.php');
global $feedback;


$pm = ProjectManager::instance();
$Group = $pm->getProject($group_id);
$plugin_manager =& PluginManager::instance();
$p =& $plugin_manager->getPluginByName('forumml');
if ($p && $plugin_manager->isPluginAvailable($p) && $p->isAllowed()) {

	$current_user=UserManager::instance()->getCurrentUser();
	$request =& HTTPRequest::instance();

	$vGrp = new Valid_UInt('group_id');
	$vGrp->required();
	if ($request->valid($vGrp)) {		
		$group_id = $request->get('group_id');
	} else {
		$group_id = "";
	}

	$vTopic = new Valid_UInt('topic');
	$vTopic->required();
	if ($request->valid($vTopic)) {
		$topic         = $request->get('topic');
		$fmlMessageMgr = new ForumML_MessageManager();
		$topicSubject  = $fmlMessageMgr->getHeaderValue($topic, FORUMML_SUBJECT);
	} else {
		$topic        = 0;
		$topicSubject = '';
	}

	$vOff = new Valid_UInt('offset');
	$vOff->required();
	if ($request->valid($vOff)) {
		$offset = $request->get('offset');
	} else {
		$offset = 0;
	}

	// Do we need to pure html cache
	$vPurge = new Valid_WhiteList('purge_cache', array('true'));
	$vPurge->required();
	if ($request->valid($vPurge)) {
		$purgeCache = true;
	} else {
		$purgeCache = false;
	}

	// Checks 'list' parameter
	$vList = new Valid_UInt('list');
	$vList->required();
	if (! $request->valid($vList)) {
		exit_error(_('Error'),_('No list specified'));
	} else {
		$list_id = $request->get('list');
		$list = new MailmanList($group_id,$list_id);
		if (!isLogged() || ($list->isPublic()!=1 && !$current_user->isMember($group_id))) {
			exit_error(_('error'),_('You are not allowed to access this page'));
		}		
		if ($list->getStatus() !=3) {
			exit_error(_('error'),_('This list is not active'));
		}
	}

	// If the list is private, search if the current user is a member of that list. If not, permission denied
	$list_name = $list->getName();
	if ($list->isPublic()==0) {
		exec("{$GLOBALS['mailman_bin_dir']}/list_members ".$list_name , $members);
		$user = $current_user->getEmail();
		if (! in_array($user,$members)) {
			exit_permission_denied();
		}
	}

	// Build the mail to be sent
	if ($request->get('send_reply')) {
		// process the mail
		$ret = plugin_forumml_process_mail($p,true);
		if ($ret) {
			$feedback .=_('Email succefully sent. It can take some time before being displayed');
			//htmlRedirect('/plugins/forumml/message.php?'. http_build_query(array(
			//    'group_id' => $group_id,
			//    'list'     => $list_id,
			//    'topic'    => $topic
			//)));
			echo "ok";
		}
		else { echo "erreur"; }
	}

	$params['title'] = $Group->getPublicName().' - ForumML - '.$list_name;
	if ($topicSubject) {
		$params['title'] .= ' - '.$topicSubject;   
	}
	$params['group'] = $group_id;
	$params['toptab']='mail';
	$params['help'] = "CommunicationServices.html#MailingLists";
	if ($request->valid(new Valid_Pv('pv'))) {
		$params['pv'] = $request->get('pv');
	}
	mailman_header($params);

	if ($request->get('send_reply') && $request->valid($vTopic)) {
		if (isset($ret) && $ret) {
			// wait few seconds before redirecting to archives page
			echo "<script> setTimeout('window.location=\"/plugins/forumml/message.php?group_id=".$group_id."&list=".$list_id."&topic=".$topic."\"',3000) </script>";
		}		
	}

	$list_link = '<a href="/plugins/forumml/message.php?group_id='.$group_id.'&list='.$list_id.'">'.$list_name.'</a>';
	$title     = _('Mailing List '.$list_link);
	if ($topic) {
		$fmlMessageMgr = new ForumML_MessageManager();
		$value = $fmlMessageMgr->getHeaderValue($topic, FORUMML_SUBJECT);
		if ($value) {
			$title = $value;
		}
	} else {
		$title .= _(' Archives');
	}
	echo '<h2>'.$title.'</h2>';

	if (! $request->exist('pv') || ($request->exist('pv') && $request->get('pv') == 0)) {
		echo "<table border=0 width=100%>
			<tr>";

		echo "<td align='left'>";
		if ($topic) {
			echo '[<a href="/plugins/forumml/message.php?group_id='.$group_id.'&list='.$list_id.'">'._('Back to the list').'</a>] ';
		} else {
			echo "		[<a href='/plugins/forumml/index.php?group_id=".$group_id."&list=".$list_id."'>
				"._('Post a new thread')."
				</a>]";
			if ($list->isPublic()==1) {
				echo ' [<A HREF="'.util_make_url('/pipermail/'.$list->getName()).'/">'._('Original Archives').'</A>]';
			} else {
				echo ' ['._('Original list archives').': <A HREF="http://'.forge_get_config('lists_host').'/pipermail/'.$list->getName().'/">'._('Public archives').'</A>/<A HREF="http://'.forge_get_config('lists_host').'/mailman/private/'.$list->getName().'/">'._('Private Archives').'</A>]';
			}
		}
		echo "</td>";

		echo "
			<td align='right'>
			(<a href='/plugins/forumml/message.php?group_id=".$group_id."&list=".$list_id."&topic=".$topic."&offset=".$offset."&search=".($request->exist('search') ? $request->get('search') : "")."&pv=1'>
			 <img src='".$p->getThemePath()."/images/ic/msg.png' border='0'>&nbsp;"._('Printer version')."
			 </a>)
			</td>
			</tr>
			</table><br>";
	}

	$vSrch = new Valid_String('search');
	$vSrch->required();
	if (! $request->valid($vSrch)) {
		// Check if there are archives to browse
		$res = getForumMLDao()->hasArchives($list_id);
		if ($res->rowCount() > 0) {
			// Call to show_thread() function to display the archives			
			if (isset($topic) && $topic != 0) {
				// specific thread
				plugin_forumml_show_thread($p, $list_id, $topic, $purgeCache);
			} else {
				plugin_forumml_show_all_threads($p,$list_id,$list_name,$offset);
			}	
		} else {
			echo "<H2>"._('Empty archives')."</H2>";
		}
	} else {
		// search archives		
		$pattern = "%".$request->get('search')."%";
		$result = getForumMLDao()->searchArchives($list_id,$pattern);
		echo "<H3>"._('Search result for ').$request->get('search')." (".$result->rowCount()." "._('Thread(s) found').")</H3>";
		if ($result->rowCount() > 0) {
			plugin_forumml_show_search_results($p,$result,$group_id,$list_id);
		}
	}

	mail_footer($params);

} else {
	header('Location: '.get_server_url());
}

?>
