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

require_once('../../env.inc.php');
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
			$feedback .= _('Project Inserted');
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
				$feedback .= _('Category Inserted');
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
				$feedback .= _('Category Updated');
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
			$feedback .= _('Updated successfully');
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
			$feedback .= _('Successfully Deleted');
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
	pm_header(array ('title'=>_('Add Categories')));
	echo "<h2>"._('Add Categories To').": ". $pg->getName() ."</h2>";

	/*
		List of possible categories for this ArtifactType
	*/
	$result=$pg->getCategories();
	echo "<p />";
	$rows=db_numrows($result);
	if ($result && $rows > 0) {
		$title_arr=array();
		$title_arr[]=_('Id');
		$title_arr[]=_('Title');

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
		echo "\n<h3>"._('No categories defined')."</h3>";
	}

	?>
	<p />
	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="add_cat" value="y" />
	<input type="hidden" name="group_project_id" value="<?php echo $pg->getID(); ?>" />
	<strong><?php echo _('Category Name') ?>:</strong><br />
	<input type="text" name="name" value="" size="15" maxlength="30" /><br />
	<p />
	<span class="important"><?php echo _('Once you add a category, it cannot be deleted') ?></span>
	<p />
	<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
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
	pm_header(array ('title'=>_('Add Categories')));

	echo '<h2>'._('Modify an Category in').': '. $pg->getName() .'</h2>';

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
		<strong><?php echo _('Category Name')?>:</strong><br />
		<input type="text" name="name" value="<?php echo $ac->getName(); ?>" />
		<p />
		<span class="important"><?php echo _('It is not recommended that you change the category name because other things are dependent upon it. When you change the category name, all related items will be changed to the new name.')?></span>
		<p />
		<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
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

	pm_header(array ('title'=>_('MISSINGTEXT:pm_admin_projects/add_subprojects_title:TEXTMISSING')));

	?>
	<p><?php echo _('Add a new project to the Project/Task Manager. <strong>This is different than adding a task to a project.</strong>') ?></p>

	<p />
	<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id"; ?>" method="post">
	<input type="hidden" name="addproject" value="y" />
	<input type="hidden" name="post_changes" value="y" />
	<p />
	<strong><?php echo _('Is Public?')?></strong><br />
	<input type="radio" name="is_public" value="1" checked="checked" /><?php echo _('Yes') ?><br />
	<input type="radio" name="is_public" value="0" /><?php echo _('No') ?><p />
	<p />
	<h3><?php echo _('New Project Name')?></h3>
	<p />
	<input type="text" name="project_name" value="" size="15" maxlength="30" />
	<p />
	<strong><?php echo _('Description')?></strong><br />
	<input type="text" name="description" value="" size="40" maxlength="80" />
	<p />
	<strong><?php echo _('Send All Updates To')?>:</strong><br />
	<input type="text" name="send_all_posts_to" value="" size="40" maxlength="80" /><br />
	<p />
	<input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
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

	pm_header(array('title'=>_('Change Project/Task Manager Status')));

	?>
	<p><?php echo _('You can modify an existing Project using this form. Please note that private projects can still be viewed by members of your project, but not the general public.') ?></p>
	<p />

	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="update_pg" value="y" />
	<input type="hidden" name="group_project_id" value="<?php echo $pg->getID(); ?>" />
	<table border="0">
<!--	<tr>
		<td>
			<strong><?php echo _('Is Public?')?></strong><br />
			<input type="radio" name="is_public" value="1"<?php echo (($pg->isPublic()=='1')?' checked="checked"':''); ?> /> <?php echo _('Yes') ?><br />
			<input type="radio" name="is_public" value="0"<?php echo (($pg->isPublic()=='0')?' checked="checked"':''); ?> /> <?php echo _('No') ?><br />
			<input type="radio" name="is_public" value="9"<?php echo (($pg->isPublic()=='9')?' checked="checked"':''); ?> /> <?php echo _('Deleted')?><br />
		</td>
	</tr> -->
	<tr>
		<td><strong><?php echo _('New Project Name')?>:</strong><br />
			<input type="text" name="project_name" value="<?php echo $pg->getName() ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo _('Description')?>:</strong><br />
			<input type="text" name="description" value="<?php echo $pg->getDescription(); ?>" size="40" maxlength="80" /><br />
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo _('Send All Updates To')?>:</strong><br />
			<input type="text" name="send_all_posts_to" value="<?php echo $pg->getSendAllPostsTo(); ?>" size="40" maxlength="80" /><br />
		</td>
	</tr>
	<tr>
		<td>
			<strong><a href="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;add_cat=1&amp;group_project_id=".$pg->getID(); ?>"><?php echo _('Add/Edit Categories')?></a></strong><br />
			<strong><a href="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;delete=1&amp;group_project_id=".$pg->getID(); ?>"><?php echo _('Permanently delete this subproject and all its data.')?></a></strong><br />
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" name="submit" value="<?php echo _('Update') ?>" />
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

	pm_header(array('title'=>_('Delete')));

	?>
	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_project_id='.$group_project_id; ?>" method="post">
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="delete" value="y" /><br />
	<?php echo _('You are about to permanently and irretrievably delete this subproject and all its related data!'); ?>
	<p>
	<input type="checkbox" name="sure" value="1"><?php echo _('I\'m Sure') ?><br />
	<input type="checkbox" name="really_sure" value="1"><?php echo _('I\'m Really Sure'); ?>
	<p>
	<input type="submit" name="post_changes" value="<?php echo _('Delete') ?>" />
	</form>
	<?php

	pm_footer(array());

} else {

	/*
		Show main page
	*/
	pm_header(array('title'=>_('Project/Task Manager Administration')));

	//
	//	Show link to create new subproject
	//
	if ($perm->isPMAdmin()) {
		?>
		<p />
		<a href="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>&amp;addproject=1"><?php echo _('Add A Project') ?></a><br />
		<?php echo _('Add a project, which can contain a set of tasks. This is different than creating a new task.') ?>
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
		echo _('<h2>No Projects Found</h2><p>None found for this group. You may add new Projects using the "Add A Project" link above.</p>');
		echo db_error();
	} else {
		for ($i=0; $i<count($pg_arr); $i++) {
			echo '<a href="'. getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_project_id='.$pg_arr[$i]->getID().'&amp;update_pg=1">'._('Edit/Update Project').': <strong>'.$pg_arr[$i]->getName().'</strong></a><p />';
		}

	}

	pm_footer(array());
}

?>
