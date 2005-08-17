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
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}

		$name = getStringFromRequest('name');
		$description = getStringFromRequest('description');
		$language = getIntFromRequest('language');
		$license = getStringFromRequest('license');
		$category = getIntFromRequest('category');
		$type = getStringFromRequest('type');
		$version = getStringFromRequest('version');
		$code = getStringFromRequest('code');
		$changes = getStringFromRequest('changes');

		/*
			Create a new snippet entry, then create a new snippet version entry
		*/
		if ($name && $description && $language != 0 && $category != 0 && $type != 0 && $version && $code) {

			$sql="INSERT INTO snippet (category,created_by,name,description,type,language,license) ".
				"VALUES ('$category','". user_getid() ."','". htmlspecialchars($name)."','".
				htmlspecialchars($description)."','$type','$language','$license')";
			$result=db_query($sql);
			if (!$result) {
				$feedback .= $Language->getText('snippet_submit','error_doing_snippet_insert');
				echo db_error();
			} else {
				$feedback .= ' Snippet Added Successfully. ';
				$snippet_id=db_insertid($result,'snippet','snippet_id');
				/*
					create the snippet version
				*/
				$sql="INSERT INTO snippet_version (snippet_id,changes,version,submitted_by,post_date,code) ".
					"VALUES ('$snippet_id','".htmlspecialchars($changes)."','".
						htmlspecialchars($version)."','".user_getid()."','".
						time()."','".htmlspecialchars($code)."')";
				$result=db_query($sql);
				if (!$result) {
					$feedback .= ' ERROR DOING SNIPPET VERSION INSERT! ';
					echo db_error();
				} else {
					$feedback .= $Language->getText('snippet_submit','snippet_added_successfull');
				}
			}
		} else {
			form_release_key($_POST['form_key']);
			exit_error('Error','Error - Go back and fill in all the information');
		}

	}
	snippet_header(array('title'=>$Language->getText('snippet_submit','title')));

	?>
	<p><?php echo $Language->getText('snippet_submit','you_can_post'); ?>
	</p>
	<p>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
	<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>">
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="changes" value="First Posted Version" />

	<table>

	<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_submit','snippet_title'); ?>:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="name" size="45" maxlength="60" />
	</td></tr>

	<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_submit','description'); ?>:</strong><?php echo utils_requiredField(); ?><br />
		<textarea name="description" rows="5" cols="45" wrap="soft"></textarea>
	</td></tr>

	<tr>
	<td><strong><?php echo $Language->getText('snippet_submit','type'); ?>:</strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array($SCRIPT_TYPE,'type'); ?>
	</td>

	<td><strong><?php echo $Language->getText('snippet_submit','license'); ?>:</strong><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_LICENSE,'license'); ?>
	</td>
	</tr>

	<tr>
	<td><strong><?php echo $Language->getText('snippet_submit','language'); ?>:</strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_LANGUAGE,'language'); ?>
		<br />
		<!-- FIXME: Where should this link go to? <a href="/support/?func=addsupport&amp;group_id=1"><?php echo $Language->getText('snippet_submit','suggest_a_language'); ?></a> -->
	</td>

	<td><strong><?php echo $Language->getText('snippet_submit','category'); ?>:</strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_CATEGORY,'category'); ?>
                <br />
                <!-- FIXME: Where should this link go to? <a href="/support/?func=addsupport&amp;group_id=1"><?php echo $Language->getText('snippet_submit','suggest_a_category'); ?></a> -->
	</td>
	</tr>

	<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_submit','version'); ?>:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="version" size="10" maxlength="15" />
	</td></tr>

	<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_submit','paste_the_code_here'); ?>:</strong><?php echo utils_requiredField(); ?><br />
		<textarea name="code" rows="30" cols="85" wrap="soft"></textarea>
	</td></tr>

	<tr><td colspan="2" align="center">
		<strong><?php echo $Language->getText('snippet_submit','make_sure_all_info'); ?></strong>
		<br />
		<input type="submit" name="submit" value="<?php echo $Language->getText('snippet_submit','submit'); ?>" />
	</td></tr>
	</table></form></p>
	<?php
	snippet_footer(array());

} else {

	exit_not_logged_in();

}

?>
