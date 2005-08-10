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

	if (getStringFromRequest('post_changes')) {
		$name = getStringFromRequest('name');
		$description = getStringFromRequest('description');
		$language = getIntFromRequest('language');
		$category = getIntFromRequest('category');
		$changes = getStringFromRequest('changes');
		$version = getStringFromRequest('version');

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
				$feedback .= $Language->getText('snippet_package','error_doing_snippet_package_insert');
				snippet_header(array('title'=>$Language->getText('snippet_package','title'),'pagename'=>'snippet_package'));
				echo db_error();
				snippet_footer(array());
				exit;
			} else {
				$feedback .= $Language->getText('snippet_package','snippet_package_added_successfull');
				$snippet_package_id=db_insertid($result,'snippet_package','snippet_package_id');
				/*
					create the snippet package version
				*/
				$sql="INSERT INTO snippet_package_version ".
					"(snippet_package_id,changes,version,submitted_by,post_date) ".
					"VALUES ('$snippet_package_id','".htmlspecialchars($changes)."','".
						htmlspecialchars($version)."','".user_getid()."','".time()."')";
				$result=db_query($sql);
				if (!$result) {
					//error in database
					$feedback .= $Language->getText('snippet_package','error_doing_snippet_package_version');
					snippet_header(array('title'=>$Language->getText('snippet_package','title_new_snippet_package'),'pagename'=>'snippet_package'));
					echo db_error();
					snippet_footer(array());
					exit;
				} else {
					//so far so good - now add snippets to the package
					$feedback .= $Language->getText('snippet_package','snippet_version_added_successfull');

					//id for this snippet_package_version
					$snippet_package_version_id=
						db_insertid($result,'snippet_package_version','snippet_package_version_id');
					snippet_header(array('title'=>$Language->getText('snippet_package','add_snippet_to_package'),'pagename'=>'snippet_package'));

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
<span style="color:red"><strong><?php echo $Language->getText('snippet_package','important'); ?></strong></span>
<p>
<?php echo $Language->getText('snippet_package','if_a_new_window'); ?></p>

<p><a href="/snippet/add_snippet_to_package.php?snippet_package_version_id=<?php echo $snippet_package_version_id; ?>" target="_blank"><?php echo $Language->getText('snippet_package','add_snippet_to'); ?></a></p>

<p>
<?php echo $Language->getText('snippet_package','browse_the_libary'); ?>
<p>

					<?php

					snippet_footer(array());
					exit;
				}
			}
		} else {
			exit_error($Language->getText('general','error'),$Language->getText('snippet_package','error_go_back_and_fill'));
		}

	}
	snippet_header(array('title'=>$Language->getText('snippet_package','title'),'pagename'=>'snippet_package'));

	?>
	</p><?php echo $Language->getText('snippet_package','you_can_group'); ?></p>
	<p>
	<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="changes" value="First Posted Version" />

	<table>

	<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_package','snippet_title'); ?></strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="name" size="45" maxlength="60" />
	</td></tr>

	<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_package','description'); ?></strong><?php echo utils_requiredField(); ?><br />
		<textarea name="description" rows="5" cols="45" wrap="soft"></textarea>
	</td></tr>

	<tr>
	<td><strong><?php echo $Language->getText('snippet_package','language') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_LANGUAGE,'language'); ?>
		<br />
		<!--<a href="/support/?func=addsupport&amp;group_id=1"><?php echo $Language->getText('snippet_package','suggest_a_language'); ?></a>-->
	</td>

	<td><strong><?php echo $Language->getText('snippet_package','category') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_CATEGORY,'category'); ?>
		<br />
		<!-- <a href="/support/?func=addsupport&amp;group_id=1"><?php echo $Language->getText('snippet_package','suggest_a_category'); ?></a>-->
	</td>
	</tr>
 
	<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_package','version') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="version" size="10" maxlength="15" />
	</td></tr>

	<tr><td colspan="2" align="center">
		<strong><?php echo $Language->getText('snippet_package','make_sure_all_info'); ?></strong>
		<br />
		<input type="submit" name="submit" value="<?php echo $Language->getText('snippet_package','submit'); ?>" />
	</td></tr>

	</table></form></p>
	<?php
	snippet_footer(array());

} else {

	exit_not_logged_in();

}

?>
