<?php
/**
  *
  * SourceForge Jobs (aka Help Wanted) Board 
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/people/people_utils.php');

if (user_ismember(1,'A')) {

	if ($post_changes) {
		/*
			Update the database
		*/

		if ($people_cat) {

			$sql="INSERT INTO people_job_category (name) VALUES ('$cat_name')";
			$result=db_query($sql);
			if (!$result) {
				echo db_error();
				$feedback .= ' Error inserting value ';
			}

			$feedback .= ' Category Inserted ';

		} else if ($people_skills) {

			$sql="INSERT INTO people_skill (name) VALUES ('$skill_name')";
			$result=db_query($sql);
			if (!$result) {
				echo db_error();
				$feedback .= ' Error inserting value ';
			}

			$feedback .= ' Skill Inserted ';
/*
		} else if ($people_cat_mod) {

			$sql="UPDATE people_category SET category_name='$cat_name' WHERE people_category_id='$people_cat_id' AND group_id='$group_id'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= ' Error modifying bug category ';
				echo db_error();
			} else {
				$feedback .= ' Bug Category Modified ';
			}

		} else if ($people_group_mod) {

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

	if ($people_cat) {
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
		<form action="<?php echo $PHP_SELF; ?>" method="post">
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

	} else if ($people_skills) {
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
		<form action="<?php echo $PHP_SELF; ?>" method="post">
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
			<a href="'.$PHP_SELF.'?people_cat=1">Add Job Categories</a><br />';
	//	echo "\nAdd categories of bugs like, 'mail module','gant chart module','interface', etc<p>";

		echo "\n<a href=\"$PHP_SELF?people_skills=1\">Add Job Skills</a><br />";
	//	echo "\nAdd Groups of bugs like 'future requests','unreproducible', etc<p>";

		people_footer(array());
	}

} else {
	exit_permission_denied();
}
?>
