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

                site_admin_header(array('title'=>'Site Mailings Subscription Maintenance'));
		?>

		<h4>Unsubscribe user: <?php echo $user_name; ?></h4>
		<p>
		You can unsubscribe user either from admin-initiated
		sitewide mailings or from all site mailings
		(admin-initiated and automated mailings, like forum
		and file release notifications).
		</p>
		<form action="<?php echo $PHP_SELF; ?>" method="POST">
		<input type="hidden" name="user_name" value="<?php echo $user_name?>">
		Unsubscription type: <?php echo html_build_select_box_from_arrays(
			array('MAIL','ALL'),
			array('Admin-initiated mailings','All site mailings'),
			'type',false,false
		); ?>
		<input type="submit" name="submit" value="Unsubscribe">
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
				'Error',
				'Could not unsubscribe user: '.$u->getErrorMessage()
			);
		}

		$feedback .= 'User unsubscribed<br>';
	}
}

site_admin_header(array('title'=>"Site Mailings Subscription Maintenance"));

?>

<h4>
Site Mailings Subscription Maintenance
</h4>

<p>
Use field below to find users which match given pattern with
the <?php echo $GLOBALS['sys_name']; ?> username, real name, or email address
(substring match is preformed, use '%' in the middle of pattern
to specify 0 or more arbitrary characters). Click on the username
to unsubscribe user from site mailings (new form will appear).
</p>

<form action="<?php echo $PHP_SELF; ?>" method="POST">
Pattern: <input type="text" name="pattern" value="<?php echo $pattern; ?>"> 
<input type="submit" name="submit" value="Show users matching pattern">
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
	$title[]='user_id';
	$title[]='Username';
	$title[]='Real Name';
	$title[]='Email';
	$title[]='Site Mail.';
	$title[]='Comm. Mail.';

	echo html_build_list_table_top($title);

	while ($row = db_fetch_array($res)) {
		echo '
		<tr bgcolor="'.html_get_alt_row_color($i++).'">
		<td>&nbsp;</td>
		<td>'.$row['user_id'].'</td>
		<td><a href="unsubscribe.php?submit=1&user_name='.$row['user_name'].'">'.$row['user_name'].'</a></td>
		<td>'.$row['realname'].'</td>
		<td> '.$row['email'].'</td>
		<td>'.$row['mail_siteupdates'].'</td>
		<td> '.$row['mail_va'].'</td>
		</tr>
		';
	}

	echo '</table>';
}

site_admin_footer(array());

?>
