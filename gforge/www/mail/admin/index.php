<?php
/**
  *
  * SourceForge Mailing Lists Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('../mail_utils.php');

if ($group_id && user_ismember($group_id,'A')) {

	if ($post_changes) {
		/*
			Update the DB to reflect the changes
		*/

		if ($add_list) {
			$list_password = substr(md5($GLOBALS['session_hash'] . time() . rand(0,40000)),0,16);

			$list_name=stripslashes($list_name);

			if (!$list_name || strlen($list_name) < 4) {
				exit_error('Error','Must Provide List Name That Is 4 or More Characters Long');
			}
			$new_list_name=strtolower(group_getunixname($group_id).'-'.$list_name);

			//see if that's a valid email address
			if (validate_email($new_list_name.'@'.$GLOBALS['sys_lists_host'])) {

				$result=db_query("SELECT * FROM mail_group_list WHERE lower(list_name)='$new_list_name'");

				if (db_numrows($result) > 0) {

					$feedback .= " ERROR - List Already Exists ";

				} else {
					$sql = "INSERT INTO mail_group_list "
					. "(group_id,list_name,is_public,password,list_admin,status,description) VALUES ("
					. "$group_id,"
					. "'$new_list_name',"
					. "'$is_public',"
					. "'$list_password',"
					. "'".user_getid()."',"
					. "1,"
					. "'". htmlspecialchars($description) ."')";


					$result=db_query($sql);
					if (!$result) {
						$feedback .= " Error Adding List ";
						echo db_error();
					} else {
						$feedback .= " List Added ";
					}

					// get email addr
					$res_email = db_query("SELECT email FROM users WHERE user_id='".user_getid()."'");
					if (db_numrows($res_email) < 1) {
						exit_error("Invalid userid","Does not compute.");
					}
					$row_email = db_fetch_array($res_email);

					// mail password to admin
					$message = "A mailing list will be created on ".$GLOBALS['sys_name']." in 6-24 hours \n"
					. "and you are the list administrator.\n\n"
					. "This list is: $new_list_name@" .$GLOBALS['sys_lists_host'] ."\n\n"
					. "Your mailing list info is at:\n"
					. "http://".$GLOBALS['sys_lists_host']."/mailman/listinfo/$new_list_name\n\n"
					. "List administration can be found at:\n"
					. "https://".$GLOBALS['sys_lists_host']."/mailman/admin/$new_list_name\n\n"
					. "Your list password is: $list_password\n"
					. "You are encouraged to change this password as soon as possible.\n\n"
					. "Thank you for registering your project with ".$GLOBALS['sys_name'].".\n\n"
					. " -- the ".$GLOBALS['sys_name']." staff\n";

					util_send_message($row_email['email'],$GLOBALS['sys_name']." New Mailing List",$message,"From: admin@$GLOBALS[sys_default_domain]");

					$feedback .= " Email sent with details to: $row_email[email] ";
				}
			} else {

				$feedback .= " Invalid List Name ";

			}

		} else if ($change_status) {
			/*
				Change a list to public/private and description
			*/
			$sql="UPDATE mail_group_list SET is_public='$is_public', ".
				"description='". htmlspecialchars($description) ."' ".
				"WHERE group_list_id='$group_list_id' AND group_id='$group_id'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= " Error Updating Status ";
				echo db_error();
			} else {
				$feedback .= " Status Updated Successfully ";
			}
		}

	}

	if ($add_list) {
		/*
			Show the form for adding forums
		*/
		mail_header(array('title'=>'Add a Mailing List','pagename'=>'mail_admin_add_list'));

		echo '
			<p>Lists are named in this manner:
			<br /><strong>projectname-listname@'. $GLOBALS['sys_lists_host'] .'</strong></p>
			<p>It will take <strong><span style="color:red">6-24 Hours</span></strong> for your list
			to be created.</p>
			<p>&nbsp;</p>';
		$result=db_query("SELECT list_name FROM mail_group_list WHERE group_id='$group_id'");
		ShowResultSet($result,'Existing Mailing Lists');

		echo 	'<form method="post" action="'.$PHP_SELF.'">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="add_list" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<p><strong>Mailing List Name:</strong><br />
			<strong>'.group_getunixname($group_id).'-<input type="text" name="list_name" value="" size="10" maxlength="12" />@'.$GLOBALS['sys_lists_host'].'</strong><br /></p>
			<p>
			<strong>Is Public?</strong><br />
			<input type="radio" name="is_public" value="1" checked="checked" /> Yes<br />
			<input type="radio" name="is_public" value="0" /> No</p><p>
			<strong>Description:</strong><br />
			<input type="text" name="description" value="" size="40" maxlength="80" /><br /></p>
			<p>
			<strong><span style="color:red">Once created, this list will ALWAYS be attached to your project
			and cannot be deleted!</span></strong></p>
			<p>
			<input type="submit" name="submit" value="Add This List" /></p>
			</form>';

		mail_footer(array());

	} else if ($change_status) {
		/*
			Change a forum to public/private
		*/
		mail_header(array('title'=>'Update Mailing Lists','pagename'=>'mail_admin_change_status'));

		$sql="SELECT list_name,group_list_id,is_public,description ".
			"FROM mail_group_list ".
			"WHERE group_id='$group_id'".
			"ORDER BY list_name";
		$result=db_query($sql);
		$rows=db_numrows($result);

		if (!$result || $rows < 1) {
			echo '
				<p>No lists found for this project';
			echo db_error();
		} else {
			echo '
			<p>
			You can administrate lists from here. Please note that private lists
			can still be viewed by members of your project, but are not listed on '.$GLOBALS['sys_name'].'.</p>';

			$title_arr=array();
			$title_arr[]='List';
			$title_arr[]='Status';
			$title_arr[]='';
			$title_arr[]='';

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i<$rows; $i++) {
				echo '
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.db_result($result,$i,'list_name').'</td>';
				echo '<td colspan="3">
					<form action="'.$PHP_SELF.'" method="post">
					<input type="hidden" name="post_changes" value="y" />
					<input type="hidden" name="change_status" value="y" />
					<input type="hidden" name="group_list_id" value="'.db_result($result,$i,'group_list_id').'" />
					<input type="hidden" name="group_id" value="'.$group_id.'" />
					<table width="100%"><tr>
					<td>
						<div style="font-size:smaller">
						<strong>Is Public?</strong><br />
						<input type="radio" name="is_public" value="1"'.((db_result($result,$i,'is_public')=='1')?' checked="checked"':'').' /> Yes<br />
						<input type="radio" name="is_public" value="0"'.((db_result($result,$i,'is_public')=='0')?' checked="checked"':'').' /> No<br />
						<input type="radio" name="is_public" value="9"'.((db_result($result,$i,'is_public')=='9')?' checked="checked"':'').' /> Deleted<br />
					</div></td><td align="right">
						<div style="font-size:smaller">
						<input type="submit" name="submit" value="Update" /></div>
					</td>
					<td align="center"><a href="http://'. $GLOBALS['sys_lists_host'] .'/mailman/admin/'
					.db_result($result,$i,'list_name').'">[Administrate this list in GNU Mailman]</a>
				       </td></tr>
				       <tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="3">
				       		<strong>Description:</strong><br />
						<input type="text" name="description" value="'.
						db_result($result,$i,'description') .'" size="40" maxlength="80" /><br />
					</td></tr></table></form></td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		}

		mail_footer(array());


	} else {
		/*
			Show main page for choosing
			either moderotor or delete
		*/
		mail_header(array('title'=>'Mailing List Administration','pagename'=>'mail_admin'));

		echo '
			<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;add_list=1">Add Mailing List</a><br />
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;change_status=1">Administrate/Update Lists</a>
			</p>';
		mail_footer(array());
	}

} else {
	/*
		Not logged in or insufficient privileges
	*/
	if (!$group_id) {
		exit_no_group();
	} else {
		exit_permission_denied();
	}
}
?>
