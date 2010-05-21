<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 */


/*
	Message Forums
	By Tim Perdue, Sourceforge, 11/99

	Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

	Complete OO rewrite by Tim Perdue 12/2002

	Heavy RBAC changes 3/17/2004
*/

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'forum/include/ForumHTML.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfwww.'forum/admin/ForumAdmin.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';
require_once $gfcommon.'include/TextSanitizer.class.php'; // to make the HTML input by the user safe to store

$group_id = getIntFromRequest('group_id');
$group_forum_id = getIntFromRequest('group_forum_id');
$g=group_get_object($group_id);
$f = new Forum ($g,$group_forum_id);
if (!$f || !is_object($f)) {
	exit_error('Error','Could Not Get Forum Object');
} elseif ($f->isError()) {
	exit_error('Error',$f->getErrorMessage());
}

session_require_perm ('forum_admin', $f->Group->getID()) ;

forum_header(array('title'=>_('Add forum')));

$res = db_query_params ('select users.user_id,users.user_name, users.email, users.realname from
users,forum_monitored_forums fmf where fmf.user_id=users.user_id and
fmf.forum_id =$1 order by users.user_id',
			array ($group_forum_id));

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
