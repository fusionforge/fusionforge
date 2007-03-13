<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
	Message Forums
	By Tim Perdue, Sourceforge, 11/99

	Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

	Complete OO rewrite by Tim Perdue 12/2002

	Heavy RBAC changes 3/17/2004
*/

require_once('../../env.inc.php');
require_once('pre.php');
require_once('www/forum/include/ForumHTML.class');
require_once('common/forum/Forum.class');
require_once('www/forum/admin/ForumAdmin.class');
require_once('common/forum/ForumFactory.class');
require_once('common/forum/ForumMessageFactory.class');
require_once('common/forum/ForumMessage.class');
require_once('common/include/TextSanitizer.class'); // to make the HTML input by the user safe to store

$group_id = getIntFromRequest('group_id');
$group_forum_id = getIntFromRequest('group_forum_id');
$g=group_get_object($group_id);
$f = new Forum ($g,$group_forum_id);
if (!$f || !is_object($f)) {
	exit_error('Error','Could Not Get Forum Object');
} elseif ($f->isError()) {
	exit_error('Error',$f->getErrorMessage());
} elseif (!$f->userIsAdmin()) {
	exit_permission_denied();
}

forum_header(array('title'=>_('MISSINGTEXT:forum_admin_addforum/title:TEXTMISSING')));

$sql="select users.user_id,users.user_name, users.email, users.realname from
users,forum_monitored_forums fmf where fmf.user_id=users.user_id and
fmf.forum_id ='$group_forum_id' order by users.user_id;";

$res=db_query($sql);

$head=array();
$head[]='User';
$head[]='Email';
$head[]='Realname';

echo $HTML->listTableTop($head);

while ($arr=db_fetch_array($res)) {

echo '<tr '. $HTML->boxGetAltRowStyle($j) . '><td>'.$arr['user_name'].'</td>
	<td>'.$arr['email'].'</td>
	<td>'.$arr['realname'].'</td></tr>';

}
echo $HTML->listTableBottom();

forum_footer(array());

?>
