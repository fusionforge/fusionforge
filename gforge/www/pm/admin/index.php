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
if (!$perm->isPMAdmin()) {
	exit_permission_denied();
}

if ($post_changes) {
	/*
		Update the database
	*/

	if ($addproject) {

		$pg = new ProjectGroup($g,$group_project_id);
		if (!$pg || !is_object($pg)) {
			exit_error('Error','Unable to create ProjectCategory Object');
		} elseif ($pg->isError()) {
			exit_error('Error',$pg->getErrorMessage());
		}
		if (!$pg->create($project_name,$description,$is_public,$send_all_posts_to)) {
			exit_error('Error',$pg->getErrorMessage());
		} else {
			$feedback .= " project Inserted ";
		}
	} else if ($add_cat) {
		/*
			Add a project_category
		*/
		$pg = new ProjectGroup($g,$group_project_id);
		if (!$pg || !is_object($pg)) {
			exit_error('Error','Unable to create ProjectCategory Object');
		} elseif ($pg->isError()) {
			exit_error('Error',$pg->getErrorMessage());
		}

		$pc = new ProjectCategory($pg);
		if (!$pc || !is_object($pc)) {
			exit_error('Error','Unable to create ProjectCategory Object');
		} else {
			if (!$pc->create($name)) {
				exit_error('Error','Error inserting: '.$pc->getErrorMessage());
			} else {
				$feedback .= ' Category Inserted ';
			}
		}

	} else if ($update_cat) {
		/*
			Update a project_category
		*/
		$pg = new ProjectGroup($g,$group_project_id);
		if (!$pg || !is_object($pg)) {
			exit_error('Error','Unable to create ProjectCategory Object');
		} elseif ($pg->isError()) {
			exit_error('Error',$pg->getErrorMessage());
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
				$feedback .= ' Category Updated ';
				$update_cat=false;
				$add_cat=true;
			}
		}

	} else if ($update_pg) {
		$pg = new ProjectGroup($g,$group_project_id);
		if (!$pg || !is_object($pg)) {
			exit_error('Error','Unable to create ProjectCategory Object');
		} elseif ($pg->isError()) {
			exit_error('Error',$pg->getErrorMessage());
		}
		if (!$pg->update($project_name,$description,$is_public,$send_all_posts_to)) {
			exit_error('Error',$pg->getErrorMessage());
		} else {
			$feedback .= " Updated Successfully ";
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
	pm_header(array ('title'=>'Add Categories','pagename'=>'pm_admin_projects','sectionvals'=>$g->getPublicName()));
	echo "<h1>Add Categories to: ". $pg->getName() ."</h1>";

	/*
		List of possible categories for this ArtifactType
	*/
	$result=$pg->getCategories();
	echo "<p>";
	$rows=db_numrows($result);
	if ($result && $rows > 0) {
		$title_arr=array();
		$title_arr[]='ID';
		$title_arr[]='Title';

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		for ($i=0; $i < $rows; $i++) {
			echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
				'<td>'.db_result($result, $i, 'category_id').'</td>'.
				'<td><a href="'.$PHP_SELF.'?update_cat=1&id='.
					db_result($result, $i, 'category_id').'&group_id='.$group_id.'&group_project_id='. $pg->getID() .'">'.
					db_result($result, $i, 'category_name').'</a></td></tr>';
		}

		echo $GLOBALS['HTML']->listTableBottom();

	} else {
		echo "\n<h1>No categories defined</h1>";
	}

	?>
	<p>
	<form action="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="add_cat" value="y">
	<input type="hidden" name="group_project_id" value="<?php echo $pg->getID(); ?>">
	<strong>New Category Name:</strong><br />
	<input type="text" name="name" value="" size="15" maxlength="30"><br />
	<p>
	<strong><font COLOR="RED">Once you add a category, it cannot be deleted</font></strong>
	<p>
	<input type="SUBMIT" name="post_changes" value="SUBMIT">
	</form>
	<?php

	pm_footer(array());

} elseif ($update_cat && $group_project_id && $id) {

//
//  FORM TO UPDATE CATEGORIES
//
	/*
		Allow modification of a artifact category
	*/

	$pg = new ProjectGroup($g,$group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error('Error','Unable to create ProjectCategory Object');
	} elseif ($pg->isError()) {
		exit_error('Error',$pg->getErrorMessage());
	}
	pm_header(array ('title'=>'Add Categories','pagename'=>'pm_admin_projects','sectionvals'=>$g->getPublicName()));

	echo '
		<h1>Modify an Category in: '. $pg->getName() .'</h1>';

	$ac = new ProjectCategory($pg,$id);
	if (!$ac || !is_object($ac)) {
		$feedback .= 'Unable to create ProjectCategory Object';
	} elseif ($ac->isError()) {
		$feedback .= $ac->getErrorMessage();
	} else {
		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>" method="post">
		<input type="hidden" name="update_cat" value="y">
		<input type="hidden" name="id" value="<?php echo $ac->getID(); ?>">
		<input type="hidden" name="group_project_id" value="<?php echo $pg->getID(); ?>">
		<p>
		<strong>Category Name:</strong><br />
		<input type="text" name="name" value="<?php echo $ac->getName(); ?>">
		<p>
		<strong><font COLOR="RED">It is not recommended that you change the artifact
			category name because other things are dependent upon it. When you change
			the category name, all related items will be changed to the new name.</font></strong>
		<p>
		<input type="SUBMIT" name="post_changes" value="SUBMIT">
		</form>
		<?php
	}

	pm_footer(array());

} elseif ($addproject) {
	/*
		Show categories and blank row
	*/

	pm_header(array ('title'=>'Add Projects','pagename'=>'pm_admin_projects','sectionvals'=>group_getname($group_id)));

	/*
		List of possible categories for this group
	*/
	$sql="SELECT group_project_id,project_name FROM project_group_list WHERE group_id='$group_id'";
	$result=db_query($sql);
	echo "<p>";
	if ($result && db_numrows($result) > 0) {
		ShowResultSet($result,"Existing Subprojects");
	} else {
		echo "\n<h1>No Subprojects in this group</h1>";
	}
	?>
	<p>
	Add a new project to the Project/Task Manager. <strong>This is different than
	 adding a task to a project.</strong>
	<p>
	<form action="<?php echo $PHP_SELF."?group_id=$group_id"; ?>" method="post">
	<input type="hidden" name="addproject" value="y">
	<input type="hidden" name="post_changes" value="y">
	<p>
	<strong>Is Public?</strong><br />
	<input type="radio" name="is_public" value="1" checked> Yes<br />
	<input type="radio" name="is_public" value="0"> No<p>
	<p>
	<h3>New Project Name:</h3>
	<p>
	<input type="text" name="project_name" value="" size="15" maxlength="30">
	<p>
	<strong>Description:</strong><br />
	<input type="text" name="description" value="" size="40" maxlength="80">
	<p>
	<strong>Send All Updates To:</strong><br />
	<input type="text" name="send_all_posts_to" value="" size="40" maxlength="80"><br />
	<p>
	<input type="SUBMIT" name="SUBMIT" value="SUBMIT">
	</form>
	<?php
	pm_footer(array());

} else if ($update_pg && $group_project_id) {


	$pg = new ProjectGroup($g,$group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error('Error','Could Not Get ProjectGroup');
	} elseif ($pg->isError()) {
		exit_error('Error',$pg->getErrorMessage());
	}

	pm_header(array('title'=>'Change Project/Task Manager Status','pagename'=>'pm_admin_update_pg','sectionvals'=>$g->getPublicName()));

	?>
	<p>
	You can modify an existing Project using this form. Please note that private projects
	can still be viewed by members of your project, but not the general public.<p>

	<form action="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="post_changes" value="y">
	<input type="hidden" name="update_pg" value="y">
	<input type="hidden" name="group_project_id" value="<?php echo $pg->getID(); ?>">
	<table border="0">
	<tr>
		<td>
			<strong>Is Public?</strong><br />
			<input type="radio" name="is_public" value="1"<?php echo (($pg->isPublic()=='1')?' checked':''); ?>> Yes<br />
			<input type="radio" name="is_public" value="0"<?php echo (($pg->isPublic()=='0')?' checked':''); ?>> No<br />
			<input type="radio" name="is_public" value="9"<?php echo (($pg->isPublic()=='9')?' checked':''); ?>> Deleted<br />
		</td>
	</tr>
	<tr>
		<td><strong>Project Name:</strong><br />
			<input type="text" name="project_name" value="<?php echo $pg->getName() ?>">
		</td>
	</tr>
	<tr>
		<td>
			<strong>Description:</strong><br />
			<input type="text" name="description" value="<?php echo $pg->getDescription(); ?>" size="40" maxlength="80"><br />
		</td>
	</tr>
	<tr>
		<td>
			<strong>Send All Updates To:</strong><br />
			<input type="text" name="send_all_posts_to" value="<?php echo $pg->getSendAllPostsTo(); ?>" size="40" maxlength="80"><br />
		</td>
	</tr>
	<tr>
		<td>
			<strong><a href="<?php echo $PHP_SELF."?group_id=$group_id&add_cat=1&group_project_id=".$pg->getID(); ?>">Add/Edit Categories</a></strong><br />
		</td>
	</tr>
	<tr>
		<td>
			<input type="SUBMIT" name="SUBMIT" value="Update">
		</td>
	</tr>
	</table>
	</form>
	<?php

	pm_footer(array());

} else {

	/*
		Show main page
	*/
	pm_header(array('title'=>'Project/Task Manager Administration','pagename'=>'pm_admin','sectionvals'=>group_getname($group_id)));

	?>
	<p>
	<a href="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>&addproject=1">Add A Project</a><br />
	Add a project, which can contain a set of tasks. This is different than creating a new task.
	<p>
	<?php
    $pgf = new ProjectGroupFactory($g);
    if (!$pgf || !is_object($pgf)) {
        exit_error('Error','Could Not Get Factory');
    } elseif ($pgf->isError()) {
        exit_error('Error',$pgf->getErrorMessage());
    }

    $pg_arr =& $pgf->getProjectGroups();

    if (count($pg_arr) < 1 || $pg_arr == false) {
        echo '
            <h2>No Projects Found</h2>
            <p>
            None found for this group. You may add new Projects using the "Add A Project" link above.';
        echo db_error();
    } else {
		for ($i=0; $i<count($pg_arr); $i++) {
			echo '<a href="'. $PHP_SELF.'?group_id='.$group_id.'&group_project_id='.$pg_arr[$i]->getID().'&update_pg=1">Edit/Update Project: <strong>'.$pg_arr[$i]->getName().'</strong></a><p>';
		}

	}

	pm_footer(array());
}

?>
