<?php
/**
 * Site Mailings Subscription Maintenance page
 *
 * This page is used to maintain site mailings (currently, just
 * unsubscribe specific user).
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once('pre.php');
require_once('www/admin/admin_utils.php');
session_require(array('group'=>'1','admin_flags'=>'A'));

$user_name = getStringFromRequest('user_name');
if (getStringFromRequest('submit') && $user_name) {
	$type = getStringFromRequest('type');

	if (!$type) {

		/*
				Show form for unsubscription type selection
		*/

		site_admin_header(array('title'=>$Language->getText('admin_unsubscribe','title')));
		?>

		<h4><?php echo $Language->getText('admin_unsubscribe','unsubscribe_user'); ?> <?php echo $user_name; ?></h4>
		<p>
		<?php echo $Language->getText('admin_unsubscribe','you_can_unsubscribe'); ?>
		</p>
		<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
		<input type="hidden" name="user_name" value="<?php echo $user_name?>" />
		Unsubscription type: <?php echo html_build_select_box_from_arrays(
			array($Language->getText('admin_unsubscribe','mail'),$Language->getText('admin_unsubscribe','all')),
			array($Language->getText('admin_unsubscribe','admin_initiaded_mail'),$Language->getText('admin_unsubscribe','all_site_mailing')),
			'type',false,false
		); ?>
		<input type="submit" name="submit" value="<?php echo $Language->getText('admin_unsubscribe','unsubscribe'); ?>" />
		</form>

		<?php
		site_admin_footer(array());
		exit();
	} else {

		/*
			Perform unsubscription
		*/

		$u =& user_get_object_by_name($user_name);
		if (!$u || !is_object($u)) {
			exit_error('Error','Could Not Get User');
		} elseif ($u->isError()) {
			exit_error('Error',$u->getErrorMessage());
		}

		if (!$u->unsubscribeFromMailings($type=='ALL' ? 1 : 0)) {
			exit_error(
				$Language->getText('admin_unsubscribe','error_unsubscribe_user') .$u->getErrorMessage()
			);
		}

		$feedback .= $Language->getText('admin_unsubscribe','user_unsubscribed').'<br />';
	}
}

site_admin_header(array('title'=>$Language->getText('admin_unsubscribe','subscription_maintance')));

?>

<h4>
<?php echo $Language->getText('admin_unsubscribe','subscription_maintance'); ?>
</h4>

<p>
<?php echo $Language->getText('admin_unsubscribe','use_fields_bellow',array($GLOBALS['sys_name'])); ?>
</p>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
Pattern: <input type="text" name="pattern" value="<?php echo $pattern; ?>" />
<input type="submit" name="submit" value="<?php echo $Language->getText('admin_unsubscribe','show_user_matching'); ?>" />
</form>

<?php

$pattern = getStringFromRequest('pattern');
if ($pattern) {
	$res = db_query("
		SELECT *
		FROM users
		WHERE user_name LIKE '%$pattern%'
		OR realname ILIKE '%$pattern%'
		OR email ILIKE '%$pattern%'
	");

	$title=array();
	$title[]='&nbsp;';
	$title[]=$Language->getText('admin_unsubscribe','user_id');
	$title[]=$Language->getText('admin_unsubscribe','username');
	$title[]=$Language->getText('admin_unsubscribe','real_name');
	$title[]=$Language->getText('admin_unsubscribe','email');
	$title[]=$Language->getText('admin_unsubscribe','site_mail');
	$title[]=$Language->getText('admin_unsubscribe','comm_mail');

	echo $GLOBALS['HTML']->listTableTop($title);

	while ($row = db_fetch_array($res)) {
		echo '
		<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
		<td>&nbsp;</td>
		<td>'.$row['user_id'].'</td>
		<td><a href="unsubscribe.php?submit=1&amp;user_name='.$row['user_name'].'">'.$row['user_name'].'</a></td>
		<td>'.$row['realname'].'</td>
		<td> '.$row['email'].'</td>
		<td>'.$row['mail_siteupdates'].'</td>
		<td> '.$row['mail_va'].'</td>
		</tr>
		';
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

site_admin_footer(array());

?>
