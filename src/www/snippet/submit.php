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

			$result = db_query_params ('INSERT INTO snippet (category,created_by,name,description,type,language,license) VALUES ($1,$2,$3,$4,$5,$6,$7)',
						   array ($category,
							  user_getid() ,
							  htmlspecialchars($name),
							  htmlspecialchars($description),
							  $type,
							  $language,
							  $license));
			if (!$result) {
				$error_msg = _('ERROR DOING SNIPPET INSERT!');
				echo db_error();
			} else {
				$feedback = _('Snippet Added Successfully.');
				$snippet_id=db_insertid($result,'snippet','snippet_id');
				/*
					create the snippet version
				*/
				$result = db_query_params ('INSERT INTO snippet_version (snippet_id,changes,version,submitted_by,post_date,code) VALUES ($1,$2,$3,$4,$5,$6)',
							   array ($snippet_id,
								  htmlspecialchars($changes),
								  htmlspecialchars($version),
								  user_getid(),
								  time(),
								  htmlspecialchars($code)));
				if (!$result) {
					$feedback = _('ERROR DOING SNIPPET VERSION INSERT!');
					echo db_error();
				} else {
					$feedback = _('Snippet Added Successfully.');
				}
			}
		} else {
			form_release_key(getStringFromRequest("form_key"));
			exit_error(_('Error - Go back and fill in all the information'));
		}

	}
	snippet_header(array('title'=>_('Snippet submit')));

	?>
	<p>
    <?php echo _('You can post a new code snippet and share it with other people around the world. Just fill in this information. <strong>Give a good description</strong> and <strong>comment your code</strong> so others can read and understand it.'); ?>
	</p>
	<p>
    <?php echo _('<span class="important">Note:</span> You can submit a new version of an existing snippet by browsing the library. You should only use this page if you are submitting an entirely new script or function.'); ?>
	</p>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post" id="snippet_submit">
	<?php
	echo $HTML->html_input('form_key', '', '', 'hidden', form_generate_key());
	echo $HTML->html_input('post_changes', '', '', 'hidden', 'y');
	echo $HTML->html_input('changes', '', '', 'hidden', 'First Posted Version');
	?>

	<table>

	<tr><td colspan="2">
	    <?php echo $HTML->html_input('name', '', _('Title') . ' :' . utils_requiredField(), 'text', '', array('size' => '45', 'maxlength' => '60')); ?>
	</td></tr>

	<tr><td colspan="2">
        <?php echo $HTML->html_textarea('description', '', _('Description') . ' :' . utils_requiredField(), '', array('rows' => '5', 'cols' => '45')); ?>
	</td></tr>

	<tr>
	<td>
		<?php echo $HTML->html_select ($SCRIPT_TYPE, 'type', _('Script Type') . ' :' . utils_requiredField() ); ?>
	</td>

	<td>
		<?php echo $HTML->html_select ($SCRIPT_LICENSE, 'license', _('License') . ' :'); ?>
	</td>
	</tr>

	<tr>
	<td>
		<?php echo $HTML->html_select ($SCRIPT_LANGUAGE, 'language', _('Language') . ' :' . utils_requiredField()); ?>
		<br />
		<!-- FIXME: Where should this link go to? <?php echo util_make_link ('/support/?func=addsupport&amp;group_id=1',_('Suggest a Language')); ?> -->
	</td>

	<td>
		<?php echo $HTML->html_select ($SCRIPT_CATEGORY, 'category', _('Category') . ' :' . utils_requiredField()); ?>
                <br />
                <!-- FIXME: Where should this link go to? <?php echo util_make_link ('/support/?func=addsupport&amp;group_id=1',_('Suggest a Category')); ?> -->
	</td>
	</tr>

	<tr><td colspan="2">
        <?php echo $HTML->html_input('version', '', _('Version') . ' :' . utils_requiredField(), 'text', '', array('size' => '10', 'maxlength' => '15')); ?>
	</td></tr>

	<tr><td colspan="2">
	    <?php echo $HTML->html_textarea('code', '', _('Paste the Code Here') . ' :' . utils_requiredField(), '', array('rows' => '30', 'cols' => '85')); ?>
	</td></tr>

	<tr><td colspan="2" class="align-center">
        <?php echo $HTML->html_input('submit', '', _('Make sure all info is complete and accurate'), 'submit', _('SUBMIT')); ?>
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
