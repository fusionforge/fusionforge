<?php
#
# Copyright (c) STMicroelectronics, 2005. All Rights Reserved.

 # Originally written by Jean-Philippe Giola, 2005
 #
 # This file is a part of codendi.
 #
 # codendi is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #
 # codendi is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU General Public License for more details.
 #
 # You should have received a copy of the GNU General Public License
 # along with codendi; if not, write to the Free Software
 # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 #
 # $Id$
 #
require_once('env.inc.php');
require_once('pre.php');
require_once('preplugins.php');
require_once('mailman/include/MailmanList.class.php');
require_once('mailman/www/mailman_utils.php');
require_once(dirname(__FILE__).'/../include/ForumML_Attachment.class.php');
global $feedback ;
$plugin_manager = PluginManager::instance();
$p              = $plugin_manager->getPluginByName('forumml');
if ($p && $plugin_manager->isPluginAvailable($p) && $p->isAllowed()) {
    $request = HTTPRequest::instance();
	$current_user=UserManager::instance()->getCurrentUser();
    $groupId = $request->getValidated('group_id', 'UInt', 0);

    $vList = new Valid_UInt('list');
    $vList->required();
    // Checks 'list' parameter
    if (! $request->valid($vList)) {
        exit_error('error','No list specified');
    } else {
        $list_id = $request->get('list');
	$list = new MailmanList($groupId,$list_id);
	if (!isLogged() || ($list->isPublic()!=1 && !$current_user->isMember($groupId))) {
		exit_error(_('error'),_('You are not allowed to access this page'));
	}		
	if ($list->getStatus() !=3) {
		exit_error(_('error'),_('This list is not active'));
	}
    }

    // Topic
    $vTopic = new Valid_UInt('topic');
    $vTopic->required();
    if ($request->valid($vTopic)) {
	    $topic = $request->get('topic');
    } else {
	    $topic = 0;
    }
    $attchmentId = $request->getValidated('id', 'UInt', 0);
    if ($attchmentId) {
	    $fmlAttch = new ForumML_Attachment();
	    $attch = $fmlAttch->getById($attchmentId);
echo $attch['file_path'];
	    if ( file_exists($attch['file_path'])) {
//if (1==1) {
		    header('Content-disposition: filename="'.$attch['file_name'].'"');
		    header("Content-Type: ".$attch['type']);
		    header("Content-Transfer-Encoding: ".$attch['type']);
		    if ($attch['file_size'] > 0) {
			    header("Content-Length: ".$attch['file_size']);
		    }
		    header("Pragma: no-cache");
		    header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
		    header("Expires: 0");
		    readfile($attch['file_path']);
		    exit;
	    } else {
		    $feedback.= _('Error : Attachment not found');
	    }
    } else {
		    $feedback.= _('Error : Missing parameter ');
    }
    htmlRedirect('/plugins/forumml/message.php?group_id='.$groupId.'&list='.$list_id.'&topic='.$topic);
} else {
	header('Location: '.get_server_url());
}

?>
