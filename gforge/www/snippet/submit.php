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
		if ($name && $description && $language != 0 && $category != 0 && $type != 0 && $version && $code) {

			$sql="INSERT INTO snippet (category,created_by,name,description,type,language,license) ".
				"VALUES ('$category','". user_getid() ."','". htmlspecialchars($name)."','".
				htmlspecialchars($description)."','$type','$language','$license')";
			$result=db_query($sql);
			if (!$result) {
				$feedback .= ' ERROR DOING SNIPPET INSERT! ';
				echo db_error();
			} else {
				$feedback .= ' Snippet Added Successfully. ';
				$snippet_id=db_insertid($result,'snippet','snippet_id');
				/*
					create the snippet version
				*/
				$sql="INSERT INTO snippet_version (snippet_id,changes,version,submitted_by,date,code) ".
					"VALUES ('$snippet_id','".htmlspecialchars($changes)."','".
						htmlspecialchars($version)."','".user_getid()."','".
						time()."','".htmlspecialchars($code)."')";
				$result=db_query($sql);
				if (!$result) {
					$feedback .= ' ERROR DOING SNIPPET VERSION INSERT! ';
					echo db_error();
				} else {
					$feedback .= ' Snippet Version Added Successfully. ';
				}
			}
		} else {
			exit_error('Error','Error - Go back and fill in all the information');
		}

	}
	snippet_header(array('title'=>'Submit A New Snippet','pagename'=>'snippet_submit'));

	?>
	<p>You can post a new code snippet and share it with other people around the world.
	Just fill in this information. <strong>Give a good description</strong> and <strong>comment your code</strong>
	so others can read and understand it.</p>
	
	<p><span style="color:red"><strong>Note:</strong></span> You can submit a new version of an existing snippet by
	browsing the library. You should only use this page if you are submitting an
	entirely new script or function.</p>
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
	<td><strong>Type:</strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array($SCRIPT_TYPE,'type'); ?>
	</td>

	<td><strong>License:</strong><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_LICENSE,'license'); ?>
	</td>
	</tr>

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
  
	<tr><td colspan="2"><strong>Paste the Code Here:</strong><?php echo utils_requiredField(); ?><br />
		<textarea name="code" rows="30" cols="85" wrap="soft"></textarea>
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
