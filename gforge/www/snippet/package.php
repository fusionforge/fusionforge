<?php
/**
  *
  * SourceForge Code Snippets Repository
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/snippet/snippet_utils.php');

if (session_loggedin()) {

	if ($post_changes) {
		/*
			Create a new snippet entry, then create a new snippet version entry
		*/
		if ($name && $description && $language != 0 && $category != 0 && $version) {
			/*
				Create the new package
			*/
			$sql="INSERT INTO snippet_package (category,created_by,name,description,language) ".
				"VALUES ('$category','".user_getid()."','".htmlspecialchars($name)."','".htmlspecialchars($description)."','$language')";
			$result=db_query($sql);
			if (!$result) {
				//error in database
				$feedback .= ' ERROR DOING SNIPPET PACKAGE INSERT! ';
				snippet_header(array('title'=>'Submit A New Snippet Package','pagename'=>'snippet_package'));
				echo db_error();
				snippet_footer(array());
				exit;
			} else {
				$feedback .= ' Snippet Package Added Successfully. ';
				$snippet_package_id=db_insertid($result,'snippet_package','snippet_package_id');
				/*
					create the snippet package version
				*/
				$sql="INSERT INTO snippet_package_version ".
					"(snippet_package_id,changes,version,submitted_by,date) ".
					"VALUES ('$snippet_package_id','".htmlspecialchars($changes)."','".
						htmlspecialchars($version)."','".user_getid()."','".time()."')";
				$result=db_query($sql);
				if (!$result) {
					//error in database
					$feedback .= ' ERROR DOING SNIPPET PACKAGE VERSION INSERT! ';
					snippet_header(array('title'=>'Submit A New Snippet Package','pagename'=>'snippet_package'));
					echo db_error();
					snippet_footer(array());
					exit;
				} else {
					//so far so good - now add snippets to the package
					$feedback .= ' Snippet Pacakge Version Added Successfully. ';

					//id for this snippet_package_version
					$snippet_package_version_id=
						db_insertid($result,'snippet_package_version','snippet_package_version_id');
					snippet_header(array('title'=>'Add Snippets to Package','pagename'=>'snippet_package'));

/*
	This raw HTML allows the user to add snippets to the package
*/

					?>

<script type="text/javascript">
<!--
function show_add_snippet_box() {
	newWindow = open("","occursDialog","height=500,width=300,scrollbars=yes,resizable=yes");
	newWindow.location=('/snippet/add_snippet_to_package.php?suppress_nav=1&snippet_package_version_id=<?php 
			echo $snippet_package_version_id; ?>');
}
// -->
</script>
<body onload="show_add_snippet_box()">

<p>
<span style="color:red"><strong>IMPORTANT!</strong></span>
<p>If a new window opened, use it to add snippets to your package.
If a new window did not open, use the following link to add to your package BEFORE you leave this page.</p>

<p><a href="/snippet/add_snippet_to_package.php?snippet_package_version_id=<?php echo $snippet_package_version_id; ?>" target="_blank">Add Snippets To Package</a></p>

<p><strong>Browse the library</strong> to find the snippets you want to add,
then add them using the new window link shown above.
<p>

					<?php

					snippet_footer(array());
					exit;
				}
			}
		} else {
			exit_error('Error','Error - Go back and fill in all the information');
		}

	}
	snippet_header(array('title'=>'Submit A New Snippet Package','pagename'=>'snippet_package'));

	?>
	</p>
	<p>You can group together existing snippets into a package using this interface. Before
	creating your package, make sure all your snippets are in place and you have made a note
	of the snippet ID's.</p>
	<p>
	<ol>
	<li>Create the package using this form.</li>
	<li><strong>Then</strong> use the "Add Snippets to Package" link to add files to your package.</li>
	</ol></p>
	<p><span style="color:red"><strong>Note:</strong></span> You can submit a new version of an existing package by
	browsing the library and using the link on the existing package. You should only use this 
	page if you are submitting an entirely new package.</p>
	<p>
	<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="changes" value="First Posted Version" />

	<table>

	<tr><td colspan="2"><strong>Title:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="name" size="45" maxlength="60" />
	</td></tr>

	<tr><td colspan="2"><strong>Description:</strong><?php echo utils_requiredField(); ?><br />
		<textarea name="description" rows="5" cols="45" wrap="soft"></textarea>
	</td></tr>

	<tr>
	<td><strong>Language:</strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_LANGUAGE,'language'); ?>
		<br />
		<a href="/support/?func=addsupport&amp;group_id=1">Suggest a Language</a>
	</td>

	<td><strong>Category:</strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_CATEGORY,'category'); ?>
		<br />
		<a href="/support/?func=addsupport&amp;group_id=1">Suggest a Category</a>
	</td>
	</tr>
 
	<tr><td colspan="2"><strong>Version:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="version" size="10" maxlength="15" />
	</td></tr>

	<tr><td colspan="2" align="center">
		<strong>Make sure all info is complete and accurate</strong>
		<br />
		<input type="submit" name="submit" value="SUBMIT" />
	</td></tr>

	</table></form></p>
	<?php
	snippet_footer(array());

} else {

	exit_not_logged_in();

}

?>
