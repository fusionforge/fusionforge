<?php
/**
 * Code Snippets Repository
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'snippet/snippet_utils.php';

if (session_loggedin()) {

	if (getStringFromRequest('post_changes')) {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}
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
			$result = db_query_params ('INSERT INTO snippet_package (category,created_by,name,description,language) VALUES ($1,$2,$3,$4,$5)',
						   array ($category,
							  user_getid(),
							  htmlspecialchars($name),
							  htmlspecialchars($description),
							  $language));
			if (!$result) {
				//error in database
				form_release_key(getStringFromRequest("form_key"));
				$error_msg .= _('ERROR DOING SNIPPET PACKAGE INSERT!');
				snippet_header(array('title'=>_('Submit A New Snippet Package')));
				echo db_error();
				snippet_footer(array());
				exit;
			} else {
				$feedback .= _('Snippet Package Added Successfully.');
				$snippet_package_id=db_insertid($result,'snippet_package','snippet_package_id');
				/*
					create the snippet package version
				*/
				$result = db_query_params ('INSERT INTO snippet_package_version (snippet_package_id,changes,version,submitted_by,post_date) VALUES ($1,$2,$3,$4,$5)',
							   array ($snippet_package_id,
								  htmlspecialchars($changes),
								  htmlspecialchars($version),
								  user_getid(),
								  time()));
				if (!$result) {
					//error in database
					$error_msg .= _('ERROR DOING SNIPPET PACKAGE VERSION INSERT!');
					snippet_header(array('title'=>_('Submit A New Snippet Package')));
					echo db_error();
					snippet_footer(array());
					exit;
				} else {
					//so far so good - now add snippets to the package
					$feedback .= _('Snippet Pacakge Version Added Successfully.');

					//id for this snippet_package_version
					$snippet_package_version_id=
						db_insertid($result,'snippet_package_version','snippet_package_version_id');
					snippet_header(array('title'=>_('Add snippets to package')));

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
<span class="important"><?php echo _('IMPORTANT!'); ?></span>
<p>
<?php echo _('If a new window opened, use it to add snippets to your package. If a new window did not open, use the following link to add to your package BEFORE you leave this page.'); ?></p>

<p><?php echo util_make_link ('/snippet/add_snippet_to_package.php?snippet_package_version_id='.$snippet_package_version_id,_('Add snippets to package'),array('target'=>'_blank')); ?></p>

<p>
<?php echo _('<strong>Browse the library</strong> to find the snippets you want to add, then add them using the new window link shown above.'); ?>
<p>

					<?php

					snippet_footer(array());
					exit;
				}
			}
		} else {
			form_release_key(getStringFromRequest("form_key"));
			exit_error(_('Error - Go back and fill in all the information'));
		}

	}
	snippet_header(array('title'=>_('Submit A New Snippet Package')));

	?>
	</p>
    <p>
    <?php echo _('You can group together existing snippets into a package using this interface. Before creating your package, make sure all your snippets are in place and you have made a note of the snippet ID\'s.'); ?>
    </p>
	<ol>
      <li><?php echo _('Create the package using this form.'); ?></li>
	  <li><?php echo _('<strong>Then</strong> use the "Add Snippets to Package" link to add files to your package.'); ?></li>
	</ol>
	<p><?php echo _('<span class="important">Note:</span> You can submit a new version of an existing package by browsing the library and using the link on the existing package. You should only use this page if you are submitting an entirely new package.'); ?>
    </p>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
	<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="changes" value="First Posted Version" />

	<table>

	<tr><td colspan="2"><strong><?php echo _('Title:'); ?></strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="name" size="45" maxlength="60" />
	</td></tr>

	<tr><td colspan="2"><strong><?php echo _('Description:'); ?></strong><?php echo utils_requiredField(); ?><br />
		<textarea name="description" rows="5" cols="45"></textarea>
	</td></tr>

	<tr>
	<td><strong><?php echo _('Language') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_LANGUAGE,'language'); ?>
		<br />
		<!--<?php echo util_make_link ('/support/?func=addsupport&amp;group_id=1',_('Suggest a Language')); ?>-->
	</td>

	<td><strong><?php echo _('Category') ?></strong><?php echo utils_requiredField(); ?><br />
		<?php echo html_build_select_box_from_array ($SCRIPT_CATEGORY,'category'); ?>
		<br />
		<!--<?php echo util_make_link ('/support/?func=addsupport&amp;group_id=1',_('Suggest a Category')); ?>-->
	</td>
	</tr>
 
	<tr><td colspan="2"><strong><?php echo _('Version') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="version" size="10" maxlength="15" />
	</td></tr>

	<tr><td colspan="2" style="text-align:center">
		<strong><?php echo _('Make sure all info is complete and accurate'); ?></strong>
		<br />
		<input type="submit" name="submit" value="<?php echo _('SUBMIT'); ?>" />
	</td></tr>

	</table></form>
	<?php
	snippet_footer(array());

} else {

	exit_not_logged_in();

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
