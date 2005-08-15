<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

require_once('pre.php');
require_once('www/pm/include/ProjectGroupHTML.class');
require_once('common/pm/ProjectGroupFactory.class');
require_once('common/pm/ProjectCategory.class');

if (!session_loggedin()) {
	exit_not_logged_in();
}

$group_id = getIntFromRequest('group_id');
$group_project_id = getIntFromRequest('group_project_id');

if (!$group_id) {
	exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error('Error',$g->getErrorMessage());
}

$perm =& $g->getPermission( session_get_user() );

$update_cat = getStringFromRequest('update_cat');
$add_cat = getStringFromRequest('add_cat');
$delete = getStringFromRequest('delete');
$id = getIntFromRequest('id');

if (getStringFromRequest('post_changes')) {
	/*
		Update the database
	*/
	$pg = new ProjectGroup($g,$group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error('Error','Unable to create ProjectCategory Object');
	} elseif ($pg->isError()) {
		exit_error('Error',$pg->getErrorMessage());
	}

	if (getStringFromRequest('addproject')) {
		$project_name = getStringFromRequest('project_name');
		$description = getStringFromRequest('description');
		$is_public = getStringFromRequest('is_public');
		$send_all_posts_to = getStringFromRequest('send_all_posts_to');

		/*
			Add new subproject
		*/
		if (!$perm->isPMAdmin()) {
			exit_permission_denied();
		}
		if (!$pg->create($project_name,$description,$is_public,$send_all_posts_to)) {
			exit_error('Error',$pg->getErrorMessage());
		} else {
			$feedback .= $Language->getText('pm_admin_projects','project_inserted');
		}

	} else if ($add_cat) {
		$name = getStringFromRequest('name');

		/*
			Add a project_category
		*/
		if (!$pg->userIsAdmin()) {
			exit_permission_denied();
		}

		$pc = new ProjectCategory($pg);
		if (!$pc || !is_object($pc)) {
			exit_error('Error','Unable to create ProjectCategory Object');
		} else {
			if (!$pc->create($name)) {
				exit_error('Error','Error inserting: '.$pc->getErrorMessage());
			} else {
				$feedback .= $Language->getText('pm_admin_projects','category_inserted');
			}
		}

	} else if ($update_cat) {
		$id = getIntFromRequest('id');
		$name = getStringFromRequest('name');

		/*
			Update a project_category
		*/
		if (!$pg->userIsAdmin()) {
			exit_permission_denied();
		}

		$pc = new ProjectCategory($pg,$id);
		if (!$pc || !is_object($pc)) {
			exit_error('Error','Unable to create ProjectCategory Object');
		} elseif ($pc->isError()) {
			exit_error('Error',$pc->getErrorMessage());
		} else {
			if (!$pc->update($name)) {
				exit_error('Error','Error updating: '.$pc->getErrorMessage());
			} else {
				$feedback .= $Language->getText('pm_admin_projects','category_updated');
				$update_cat=false;
				$add_cat=true;
			}
		}

	} else if (getStringFromRequest('update_pg')) {
		$project_name = getStringFromRequest('project_name');
		$description = getStringFromRequest('description');
		$send_all_posts_to = getStringFromRequest('send_all_posts_to');

		/*
			Update a subproject
		*/
		if (!$pg->userIsAdmin()) {
			exit_permission_denied();
		}

		if (!$pg->update($project_name,$description,$send_all_posts_to)) {
			exit_error('Error',$pg->getErrorMessage());
		} else {
			$feedback .= $Language->getText('general','update_successful');
		}

	} else if ($delete) {
		$sure = getStringFromRequest('sure');
		$really_sure = getStringFromRequest('really_sure');

		/*
			Delete a subproject
		*/
		if (!$pg->userIsAdmin()) {
			exit_permission_denied();
		}

		if (!$pg->delete(getStringFromRequest('sure'),getStringFromRequest('really_sure'))) {
			exit_error('Error',$pg->getErrorMessage());
		} else {
			$feedback .= $Language->getText('pm_admin_projects','deleted');
			$group_project_id=0;
			$delete=false;
		}
	}
}
/*
	Show UI forms
*/
if ($add_cat && $group_project_id) {
//
//  FORM TO ADD CATEGORIES
//

	$pg = new ProjectGroup($g,$group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error('Error','Unable to create ProjectCategory Object');
	} elseif ($pg->isError()) {
		exit_error('Error',$pg->getErrorMessage());
	}
	if (!$pg->userIsAdmin()) {
		exit_permission_denied();
	}
	pm_header(array ('title'=>$Language->getText('pm_admin_projects','add_categories_title')));
	echo "<h2>".$Language->getText('pm_admin_projects','add_categories_to').": ". $pg->getName() ."</h2>";

	/*
		List of possible categories for this ArtifactType
	*/
	$result=$pg->getCategories();
	echo "<p />";
	$rows=db_numrows($result);
	if ($result && $rows > 0) {
		$title_arr=array();
		$title_arr[]=$Language->getText('pm_admin_projects','id');
		$title_arr[]=$Language->getText('pm_admin_projects','project_title');

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		for ($i=0; $i < $rows; $i++) {
			echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
				'<td>'.db_result($result, $i, 'category_id').'</td>'.
				'<td><a href="'.getStringFromServer('PHP_SELF').'?update_cat=1&amp;id='.
					db_result($result, $i, 'category_id').'&amp;group_id='.$group_id.'&amp;group_project_id='. $pg->getID() .'">'.
					db_result($result, $i, 'category_name').'</a></td></tr>';
		}

		echo $GLOBALS['HTML']->listTableBottom();

	} else {
		echo "\n<h3>".$Language->getText('pm_admin_projects','no_categories')."</h3>";
	}

	?>
	<p />
	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="add_cat" value="y" />
	<input type="hidden" name="group_project_id" value="<?php echo $pg->getID(); ?>" />
	<strong><?php echo $Language->getText('pm_admin_projects','category_name') ?>:</strong><br />
	<input type="text" name="name" value="" size="15" maxlength="30" /><br />
	<p />
	<strong><font color="red"><?php echo $Language->getText('pm_admin_projects','category_note') ?></font></strong>
	<p />
	<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" />
	</form>
	<?php

	pm_footer(array());

} elseif ($update_cat && $group_project_id && $id) {

//
//  FORM TO UPDATE CATEGORIES
//
	/*
		Allow modification of a category
	*/

	$pg = new ProjectGroup($g,$group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error('Error','Unable to create ProjectCategory Object');
	} elseif ($pg->isError()) {
		exit_error('Error',$pg->getErrorMessage());
	}
	if (!$pg->userIsAdmin()) {
		exit_permission_denied();
	}
	pm_header(array ('title'=>$Language->getText('pm_admin_projects','add_categories')));

	echo '<h2>'.$Language->getText('pm_admin_projects','modify_category').': '. $pg->getName() .'</h2>';

	$ac = new ProjectCategory($pg,$id);
	if (!$ac || !is_object($ac)) {
		$feedback .= 'Unable to create ProjectCategory Object';
	} elseif ($ac->isError()) {
		$feedback .= $ac->getErrorMessage();
	} else {
		?>
		<p />
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>" method="post" />
		<input type="hidden" name="update_cat" value="y" />
		<input type="hidden" name="id" value="<?php echo $ac->getID(); ?>" />
		<input type="hidden" name="group_project_id" value="<?php echo $pg->getID(); ?>" />
		<p />
		<strong><?php echo $Language->getText('pm_admin_projects','category_name')?>:</strong><br />
		<input type="text" name="name" value="<?php echo $ac->getName(); ?>" />
		<p />
		<strong><font color="red"><?php echo $Language->getText('pm_admin_projects','category_note2')?></font></strong>
		<p />
		<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" />
		</form>
		<?php
	}

	pm_footer(array());

} elseif (getStringFromRequest('addproject')) {
	/*
		Create a new subproject
	*/
	if (!$perm->isPMAdmin()) {
		exit_permission_denied();
	}

	pm_header(array ('title'=>$Language->getText('pm_admin_projects','add_subprojects_title')));

	?>
	<p><?php echo $Language->getText('pm_admin_projects','projects_intro') ?></p>

	<p />
	<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id"; ?>" method="post">
	<input type="hidden" name="addproject" value="y" />
	<input type="hidden" name="post_changes" value="y" />
	<p />
	<strong><?php echo $Language->getText('pm_admin_projects','is_public')?></strong><br />
	<input type="radio" name="is_public" value="1" checked="checked" /><?php echo $Language->getText('general','yes') ?><br />
	<input type="radio" name="is_public" value="0" /><?php echo $Language->getText('general','no') ?><p />
	<p />
	<h3><?php echo $Language->getText('pm_admin_projects','project_name')?></h3>
	<p />
	<input type="text" name="project_name" value="" size="15" maxlength="30" />
	<p />
	<strong><?php echo $Language->getText('pm_admin_projects','description')?></strong><br />
	<input type="text" name="description" value="" size="40" maxlength="80" />
	<p />
	<strong><?php echo $Language->getText('pm_admin_projects','send_updates')?>:</strong><br />
	<input type="text" name="send_all_posts_to" value="" size="40" maxlength="80" /><br />
	<p />
	<input type="submit" name="submit" value="<?php echo $Language->getText('general','submit') ?>" />
	</form>
	<?php
	pm_footer(array());

} else if (getStringFromRequest('update_pg') && $group_project_id) {

	$pg = new ProjectGroup($g,$group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error('Error','Could Not Get ProjectGroup');
	} elseif ($pg->isError()) {
		exit_error('Error',$pg->getErrorMessage());
	}
	if (!$pg->userIsAdmin()) {
		exit_permission_denied();
	}

	pm_header(array('title'=>$Language->getText('pm_admin_projects','change_project_title')));

	?>
	<p><?php echo $Language->getText('pm_admin_projects','change_project_intro') ?></p>
	<p />

	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="update_pg" value="y" />
	<input type="hidden" name="group_project_id" value="<?php echo $pg->getID(); ?>" />
	<table border="0">
<!--	<tr>
		<td>
			<strong><?php echo $Language->getText('pm_admin_projects','is_public')?></strong><br />
			<input type="radio" name="is_public" value="1"<?php echo (($pg->isPublic()=='1')?' checked="checked"':''); ?> /> <?php echo $Language->getText('general','yes') ?><br />
			<input type="radio" name="is_public" value="0"<?php echo (($pg->isPublic()=='0')?' checked="checked"':''); ?> /> <?php echo $Language->getText('general','no') ?><br />
			<input type="radio" name="is_public" value="9"<?php echo (($pg->isPublic()=='9')?' checked="checked"':''); ?> /> <?php echo $Language->getText('general','deleted')?><br />
		</td>
	</tr> -->
	<tr>
		<td><strong><?php echo $Language->getText('pm_admin_projects','project_name')?>:</strong><br />
			<input type="text" name="project_name" value="<?php echo $pg->getName() ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo $Language->getText('pm_admin_projects','description')?>:</strong><br />
			<input type="text" name="description" value="<?php echo $pg->getDescription(); ?>" size="40" maxlength="80" /><br />
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo $Language->getText('pm_admin_projects','send_updates')?>:</strong><br />
			<input type="text" name="send_all_posts_to" value="<?php echo $pg->getSendAllPostsTo(); ?>" size="40" maxlength="80" /><br />
		</td>
	</tr>
	<tr>
		<td>
			<strong><a href="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;add_cat=1&amp;group_project_id=".$pg->getID(); ?>"><?php echo $Language->getText('pm_admin_projects','add_edit_categories')?></a></strong><br />
			<strong><a href="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;delete=1&amp;group_project_id=".$pg->getID(); ?>"><?php echo $Language->getText('pm_admin_projects','delete_info')?></a></strong><br />
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" name="submit" value="<?php echo $Language->getText('general','update') ?>" />
		</td>
	</tr>
	</table>
	</form>
	<?php

	pm_footer(array());

} else if ($delete && $group_project_id) {


	$pg = new ProjectGroup($g,$group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error('Error','Could Not Get ProjectGroup');
	} elseif ($pg->isError()) {
		exit_error('Error',$pg->getErrorMessage());
	}
	if (!$pg->userIsAdmin()) {
		exit_permission_denied();
	}

	pm_header(array('title'=>$Language->getText('pm_admin_projects','delete')));

	?>
	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_project_id='.$group_project_id; ?>" method="post">
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="delete" value="y" /><br />
	<?php echo $Language->getText('pm_admin_projects','delete_warning'); ?>
	<p>
	<input type="checkbox" name="sure" value="1"><?php echo $Language->getText('pm_admin_projects','sure') ?><br />
	<input type="checkbox" name="really_sure" value="1"><?php echo $Language->getText('pm_admin_projects','really_sure'); ?>
	<p>
	<input type="submit" name="post_changes" value="<?php echo $Language->getText('pm_admin_projects','delete') ?>" />
	</form>
	<?php

	pm_footer(array());

} else {

	/*
		Show main page
	*/
	pm_header(array('title'=>$Language->getText('pm_admin_projects','admin_title')));

	//
	//	Show link to create new subproject
	//
	if ($perm->isPMAdmin()) {
		?>
		<p />
		<a href="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>&amp;addproject=1"><?php echo $Language->getText('pm_admin_projects','add_project') ?></a><br />
		<?php echo $Language->getText('pm_admin_projects','add_project_intro') ?>
		<p />
		<?php
	}

	$pgf = new ProjectGroupFactory($g);
	if (!$pgf || !is_object($pgf)) {
		exit_error('Error','Could Not Get Factory');
	} elseif ($pgf->isError()) {
		exit_error('Error',$pgf->getErrorMessage());
	}

	$pg_arr =& $pgf->getProjectGroups();

	if (count($pg_arr) < 1 || $pg_arr == false) {
		echo $Language->getText('pm_admin_projects','no_projects_found');
		echo db_error();
	} else {
		for ($i=0; $i<count($pg_arr); $i++) {
			echo '<a href="'. getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_project_id='.$pg_arr[$i]->getID().'&amp;update_pg=1">'.$Language->getText('pm_admin_projects','edit_update').': <strong>'.$pg_arr[$i]->getName().'</strong></a><p />';
		}

	}

	pm_footer(array());
}

?>
