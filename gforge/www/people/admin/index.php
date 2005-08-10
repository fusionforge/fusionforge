<?php
/**
 * GForge Help Wanted 
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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

require_once('pre.php');
require_once('www/people/people_utils.php');

if (!$sys_use_people) {
	exit_disabled();
}

$group_id = getIntFromRequest('group_id');
$job_id = getIntFromRequest('job_id');

if (user_ismember(1,'A')) {

	if (getStringFromRequest('post_changes')) {
		/*
			Update the database
		*/

		if (getStringFromRequest('people_cat')) {
			$cat_name = getStringFromRequest('cat_name');

			$sql="INSERT INTO people_job_category (name) VALUES ('$cat_name')";
			$result=db_query($sql);
			if (!$result) {
				echo db_error();
				$feedback .= ' Error inserting value ';
			}

			$feedback .= ' Category Inserted ';

		} else if (getStringFromRequest('people_skills')) {
			$skill_name = getStringFromRequest('skill_name');

			$sql="INSERT INTO people_skill (name) VALUES ('$skill_name')";
			$result=db_query($sql);
			if (!$result) {
				echo db_error();
				$feedback .= ' Error inserting value ';
			}

			$feedback .= ' Skill Inserted ';
/*
		} else if (getStringFromRequest('people_cat_mod')) {
			$cat_name = getStringFromRequest('cat_name');
			$people_cat_id = getIntFromRequest('people_cat_id');

			$sql="UPDATE people_category SET category_name='$cat_name' WHERE people_category_id='$people_cat_id' AND group_id='$group_id'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= ' Error modifying bug category ';
				echo db_error();
			} else {
				$feedback .= ' Bug Category Modified ';
			}

		} else if (getStringFromRequest('people_group_mod')) {
			$group_name = getStringFromRequest('group_name');
			$people_group_id = getIntFromRequest('people_group_id');
			$group_id = getIntFromRequest('group_id');

			$sql="UPDATE people_group SET group_name = '$group_name' WHERE people_group_id='$people_group_id' AND group_id='$group_id'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= ' Error modifying bug cateogry ';
				echo db_error();
			} else {
				$feedback .= ' Bug Category Modified ';
			}
*/
		}

	} 
	/*
		Show UI forms
	*/

	if (getStringFromRequest('people_cat')) {
		/*
			Show categories and blank row
		*/
		people_header(array ('title'=>'Add/Change Categories','pagename'=>'people_admin_people_cat'));

		/*
			List of possible categories for this group
		*/
		$sql="select category_id,name from people_job_category";
		$result=db_query($sql);
		echo "<p>";
		if ($result && db_numrows($result) > 0) {
			ShowResultSet($result,'Existing Categories','people_cat');
		} else {
			echo '
				<h1>No job categories</h1>';
			echo db_error();
		}
		?>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
		<input type="hidden" name="people_cat" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		<h4>New Category Name:</h4>
		<input type="text" name="cat_name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong><span style="color:red">Once you add a category, it cannot be deleted</span></strong></p>
		<p>
		<input type="submit" name="submit" value="SUBMIT"></p>
		</form></p>
		<?php

		people_footer(array());

	} else if (getStringFromRequest('people_skills')) {
		/*
			Show people_groups and blank row
		*/
		people_header(array ('title'=>'Add/Change People Skills','pagename'=>'people_admin_people_skills'));

		/*
			List of possible people_groups for this group
		*/
		$sql="select skill_id,name from people_skill";
		$result=db_query($sql);
		echo "<p>";
		if ($result && db_numrows($result) > 0) {
			ShowResultSet($result,"Existing Skills","people_skills");
		} else {
			echo db_error();
			echo "\n<h2>No Skills Found</h2>";
		}
		?>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
		<input type="hidden" name="people_skills" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		<h4>New Skill Name:</h4>
		<input type="text" name="skill_name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong><span style="color:red">Once you add a skill, it cannot be deleted</span></strong></p>
		<p>
		<input type="submit" name="submit" value="SUBMIT"></p>
		</form></p>
		<?php

		people_footer(array());

	} else {
		/*
			Show main page
		*/

		people_header(array ('title'=>'People Administration','pagename'=>'people_admin'));

		echo '<p>
			<a href="'.getStringFromServer('PHP_SELF').'?people_cat=1">Add Job Categories</a><br />';
	//	echo "\nAdd categories of bugs like, 'mail module','gant chart module','interface', etc<p>";

		echo "\n<a href=\"".getStringFromServer('PHP_SELF')."?people_skills=1\">Add Job Skills</a><br />";
	//	echo "\nAdd Groups of bugs like 'future requests','unreproducible', etc<p>";

		people_footer(array());
	}

} else {
	exit_permission_denied();
}
?>
