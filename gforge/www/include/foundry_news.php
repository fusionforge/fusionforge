<?php
/**
 * Foundry news page
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: foundry_news.php,v 1.35 2001/06/08 18:23:40 dbrogdon Exp $
 */

require_once('www/project/admin/project_admin_utils.php');
require_once('www/news/admin/news_admin_utils.php');
//we know $foundry is already set up from the root /foundry/ page


if (user_ismember($group_id,'A')) {
	/*
		This is a simple page that foundry admins
			can access. It shows all news for all projects in this foundry

		The admin can then check a box and add the news item to their foundry.

		The admin cannot edit the news item unfortunately - only the project
			admin can edit their news
	*/

	//comma separated list of project_id's in this foundry
	$list=$foundry->getProjectsCommaSep();
	
	if ($post_changes) {

		//echo $post_changes.'-'.$status.'-'.$news_id;

		if ($approve) {
			db_query("DELETE FROM foundry_news WHERE foundry_id='$group_id' AND news_id='$news_id'");
			/*
				Update the db so the item shows on the home page
			*/
			if ($status) {
				$sql="INSERT INTO foundry_news (foundry_id,news_id,is_approved,approve_date) ".
					"VALUES ('$group_id','$news_id','$status','". time() ."')";
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					echo db_error();
					$feedback .= ' ERROR doing update ';
				} else {
					$feedback .= ' NewsByte Updated. ';
				}
			}

			/*
				Show the list_queue
			*/
			$approve='';
			$list_queue='y';

		} else if ($mass_reject) {
			/*
				Move msgs to rejected status
			*/
			reset($news_id);
			while ( list($key,$val) = each($news_id) ) {
       				$sql = "
       					INSERT INTO foundry_news
	       				(foundry_id,news_id,is_approved,approve_date)
       					VALUES
       					('$group_id','$val','2','". time() ."')
       				";

				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					$feedback .= ' ERROR doing update ';
					$feedback .= db_error().'<br>';
				} else {
					$feedback .= ' NewsBytes Rejected<br>';
				}
			}
		}
	}

	project_admin_header (array('title'=>'NewsBytes','group'=>$group_id,'pagename'=>'foundry_news'));

	if ($approve) {
		/*
			Show the submit form
		*/

		$sql="SELECT groups.unix_group_name,groups.group_name,".
			"news_bytes.* ".
			"FROM news_bytes,groups WHERE id='$id' ".
			"AND news_bytes.group_id = groups.group_id ";
		$result=db_query($sql);
		if (db_numrows($result) < 1) {
			exit_error('Error','Error - not found');
		}

		echo '
		<H3>Approve a NewsByte</H3>
		<P>
		<FORM ACTION="'.$PHP_SELF.'" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="news_id" VALUE="'.db_result($result,0,'id').'">
		<B>Submitted for Project:</B> <a href="/projects/'.
		strtolower(db_result($result,0,'unix_group_name')).
		'/">'.db_result($result,0,'group_name').'</a><BR>
		<B>Submitted by:</B> '.user_getname(db_result($result,0,'submitted_by')).'<BR>
		<INPUT TYPE="HIDDEN" NAME="approve" VALUE="y">
		<INPUT TYPE="HIDDEN" NAME="post_changes" VALUE="y">
		<INPUT TYPE="RADIO" NAME="status" VALUE="1"> Approve For Foundry Page<BR>
		<INPUT TYPE="RADIO" NAME="status" VALUE="0"> Do not change<BR>
		<INPUT TYPE="RADIO" NAME="status" VALUE="2" CHECKED> Reject<BR>
		<B>Subject:</B><BR>
		'.db_result($result,0,'summary').'<BR>
		<B>Details:</B><BR>
		'. nl2br( db_result($result,0,'details') ) .'
		<P>
		<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="SUBMIT">
		</FORM>';

	} else {
		/*

			Show list of waiting news items

		*/

		// One month for pending
		$old_date = time()-60*60*24*30;
		$sql_pending = "
			SELECT *
			FROM news_bytes,groups
			WHERE date > '$old_date'
			AND news_bytes.group_id=groups.group_id
			AND EXISTS (SELECT project_id FROM foundry_projects 
				WHERE news_bytes.group_id=foundry_projects.project_id 
				AND foundry_projects.foundry_id='$group_id')
			AND NOT EXISTS (SELECT news_id FROM foundry_news 
				WHERE foundry_id='$group_id' 
				AND approve_date > '$old_date' 
				AND foundry_news.news_id=news_bytes.id)
			AND groups.is_public=1
			AND groups.status='A'
			ORDER BY date
		";

		// 3 days for rejected
		$old_date = time()-60*60*24*3;
		$sql_rejected = "
			SELECT *
			FROM news_bytes,groups
			WHERE news_bytes.group_id=groups.group_id
			AND EXISTS (SELECT news_id FROM foundry_news 
				WHERE is_approved=2 
				AND foundry_id='$group_id' 
				AND approve_date > '$old_date' 
				AND foundry_news.news_id=news_bytes.id)
			ORDER BY date
		";

		// One week for approved
		$old_date = time()-60*60*24*7;
		$sql_approved = "
			SELECT *
			FROM news_bytes,groups
			WHERE news_bytes.group_id=groups.group_id
			AND EXISTS (SELECT news_id FROM foundry_news 
				WHERE is_approved=1
				AND foundry_id='$group_id' 
				AND approve_date > '$old_date' 
				AND foundry_news.news_id=news_bytes.id)
			ORDER BY date
		";

		show_news_approve_form(
			$sql_pending,
			$sql_rejected,
			$sql_approved
		);
	}
	project_admin_footer(array());

} else {

	exit_error('Permission Denied.','Permission Denied. You have to be an admin on the project you are editing or a member of the '.$GLOBALS['sys_name'].' News team.');

}

?>
