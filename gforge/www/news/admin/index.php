<?php
/**
  *
  * SourceForge News Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('news_admin_utils.php');
require_once('www/news/news_utils.php');
//common forum tools which are used during the creation/editing of news items
require_once('common/forum/Forum.class');

if ($group_id && $group_id != $sys_news_group && user_ismember($group_id,'A')) {
	/*

		Per-project admin pages.

		Shows their own news items so they can edit/update.

		If their news is on the homepage, and they edit, it is removed from
			sf.net homepage.

	*/
	if ($post_changes) {
		if ($approve) {
			/*
				Update the db so the item shows on the home page
			*/
			if ($status != 0 && $status != 4) {
				//may have tampered with HTML to get their item on the home page
				$status=0;
			}

			//foundry stuff - remove this news from the foundry so it has to be re-approved by the admin
			db_query("DELETE FROM foundry_news WHERE news_id='$id'");

			if (!$summary) {
				$summary='(none)';
			}
			if (!$details) {
				$details='(none)';
			}

			$sql="UPDATE news_bytes SET is_approved='$status', summary='".htmlspecialchars($summary)."', ".
				"details='".htmlspecialchars($details)."' WHERE id='$id' AND group_id='$group_id'";
			$result=db_query($sql);

			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= ' ERROR doing group update ';
			} else {
				$feedback .= ' Project NewsByte Updated. ';
			}
			/*
				Show the list_queue
			*/
			$approve='';
			$list_queue='y';
		}
	}

	news_header(array('title'=>'NewsBytes','pagename'=>'news_admin'));

	if ($approve) {
		/*
			Show the submit form
		*/

		$sql="SELECT * FROM news_bytes WHERE id='$id' AND group_id='$group_id'";
		$result=db_query($sql);
		if (db_numrows($result) < 1) {
			exit_error('Error','Error - none found');
		}

		echo '
		<h3>Approve a NewsByte For Project: '.group_getname($group_id).'</h3>
		<p />
		<form action="'.$PHP_SELF.'" method="post">
		<input type="hidden" name="group_id" value="'.db_result($result,0,'group_id').'" />
		<input type="hidden" name="id" value="'.db_result($result,0,'id').'" />

		<strong>Submitted by:</strong> '.user_getname(db_result($result,0,'submitted_by')).'<br />
		<input type="hidden" name="approve" value="y" />
		<input type="hidden" name="post_changes" value="y" />

		<strong>Status:</strong><br />
		<input type="radio" name="status" value="0" checked="checked" /> Displayed<br />
		<input type="radio" name="status" value="4" /> Delete<br />

		<strong>Subject:</strong><br />
		<input type="text" name="summary" value="'.db_result($result,0,'summary').'" size="30" maxlength="60"><br />
		<strong>Details:</strong><br />
		<textarea name="details" rows="5" cols="50">'.db_result($result,0,'details').'</textarea><p>
		<strong>If this item is on the '.$GLOBALS['sys_name'].' home page and you edit it, it will be removed from the home page.</strong><br /></p>
		<input type="submit" name="submit" value="SUBMIT">
		</form>';

	} else {
		/*
			Show list of waiting news items
		*/

		$sql="SELECT * FROM news_bytes WHERE is_approved <> 4 AND group_id='$group_id'";
		$result=db_query($sql);
		$rows=db_numrows($result);
		if ($rows < 1) {
			echo '
				<h4>'.$Language->getText('news_admin','noqueued').': '.group_getname($group_id).'</h4>';
		} else {
			echo '
				<h4>'.$Language->getText('news_admin','queued').': '.group_getname($group_id).'</h4>
				<p>';
			for ($i=0; $i<$rows; $i++) {
				echo '
				<a href="/news/admin/?approve=1&id='.db_result($result,$i,'id').'&amp;group_id='.
					db_result($result,$i,'group_id').'">'.
					db_result($result,$i,'summary').'</a><br /></p>';
			}
		}

	}
	news_footer(array());

} else if (user_ismember($sys_news_group,'A')) {
	/*

		News uber-user admin pages

		Show all waiting news items except those already rejected.

		Admin members of $sys_news_group (news project) can edit/change/approve news items

	*/
	if ($post_changes) {
		if ($approve) {
			if ($status==1) {
				/*
					Update the db so the item shows on the home page
				*/
				$sql="UPDATE news_bytes SET is_approved='1', post_date='".time()."', ".
					"summary='".htmlspecialchars($summary)."', details='".htmlspecialchars($details)."' WHERE id='$id'";
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					$feedback .= ' ERROR doing update ';
				} else {
					$feedback .= ' NewsByte Updated. ';
				}
			} else if ($status==2) {
				/*
					Move msg to deleted status
				*/
				$sql="UPDATE news_bytes SET is_approved='2' WHERE id='$id'";
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					$feedback .= ' ERROR doing update ';
					$feedback .= db_error();
				} else {
					$feedback .= ' NewsByte Deleted. ';
				}
			}

			/*
				Show the list_queue
			*/
			$approve='';
			$list_queue='y';
		} else if ($mass_reject) {
			/*
				Move msg to rejected status
			*/
			$sql="UPDATE news_bytes "
			     ."SET is_approved='2' "
			     ."WHERE id IN ('".implode($news_id,"','")."')";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= ' ERROR doing update ';
				$feedback .= db_error();
			} else {
				$feedback .= ' NewsBytes Rejected. ';
			}
		}
	}

	news_header(array('title'=>'NewsBytes','pagename'=>'news_admin'));

	if ($approve) {
		/*
			Show the submit form
		*/

		$sql="SELECT groups.unix_group_name,news_bytes.* ".
			"FROM news_bytes,groups WHERE id='$id' ".
			"AND news_bytes.group_id=groups.group_id ";
		$result=db_query($sql);
		if (db_numrows($result) < 1) {
			exit_error('Error','Error - not found');
		}

		echo '
		<h3>Approve a NewsByte</h3>
		<p />
		<form action="'.$PHP_SELF.'" method="post">
		<input type="hidden" name="for_group" value="'.db_result($result,0,'group_id').'" />
		<input type="hidden" name="id" value="'.db_result($result,0,'id').'" />
		<strong>Submitted for group:</strong> <a href="/projects/'.strtolower(db_result($result,0,'unix_group_name')).'/">'.group_getname(db_result($result,0,'group_id')).'</a><br />
		<strong>Submitted by:</strong> '.user_getname(db_result($result,0,'submitted_by')).'<br />
		<input type="hidden" name="approve" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		<input type="radio" name="status" value="1" /> Approve For Front Page<br />
		<input type="radio" name="status" value="0" /> Do Nothing<br />
		<input type="radio" name="status" value="2" checked="checked" /> Delete<br />
		<strong>Subject:</strong><br />
		<input type="text" name="summary" value="'.db_result($result,0,'summary').'" size="30" maxlength="60" /><br />
		<strong>Details:</strong><br />
		<textarea name="details" rows="5" cols="50">'.db_result($result,0,'details').'</textarea><br />
		<input type="submit" name="submit" value="SUBMIT" />
		</form>';

	} else {

		/*
			Show list of waiting news items
		*/

	        $old_date = time()-60*60*24*30;
		$sql_pending= "
			SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=0
			AND news_bytes.group_id=groups.group_id
			AND post_date > '$old_date'
			AND groups.is_public=1
			AND groups.status='A'
			ORDER BY post_date
		";

		$old_date = time()-(60*60*24*7);
		$sql_rejected = "
			SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=2
			AND news_bytes.group_id=groups.group_id
			AND post_date > '$old_date'
			ORDER BY post_date
		";

		$sql_approved = "
			SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=1
			AND news_bytes.group_id=groups.group_id
			AND post_date > '$old_date'
			ORDER BY post_date
		";

		show_news_approve_form(
			$sql_pending,
			$sql_rejected,
			$sql_approved
		);

	}
	news_footer(array());

} else {

	exit_error($Language->getText('general','permdenied'),$Language->getText('news_admin','permdenied',$GLOBALS['sys_name']));

}
?>
