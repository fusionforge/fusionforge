<?php
/**
  *
  * Site Mailings Subscription Maintenance page
  *
  * This page is used to maintain site mailings (currently, just
  * unsubscribe specific user).
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/admin/admin_utils.php');
session_require(array('group'=>'1','admin_flags'=>'A'));

if ($submit && $user_name) {

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
		<form action="<?php echo $PHP_SELF; ?>" method="post">
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
		exit_assert_object($u, 'User');
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

<form action="<?php echo $PHP_SELF; ?>" method="post">
Pattern: <input type="text" name="pattern" value="<?php echo $pattern; ?>" />
<input type="submit" name="submit" value="<?php echo $Language->getText('admin_unsubscribe','show_user_matching'); ?>" />
</form>

<?php

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
