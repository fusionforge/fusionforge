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


require_once('../env.inc.php');
require_once('pre.php');
require_once('www/snippet/snippet_utils.php');

if (session_loggedin()) {
	$type = getStringFromRequest('type');
	$id = getIntFromRequest('id');

	if ($type=='snippet') {
		/*
			See if the snippet exists first
		*/
		$result=db_query("SELECT * FROM snippet WHERE snippet_id='$id'");
		if (!$result || db_numrows($result) < 1) {
			exit_error(_('Error - snippet doesn\'t exist'));
		}

		/*
			handle inserting a new version of a snippet
		*/
		if (getStringFromRequest('post_changes')) {
			if (!form_key_is_valid(getStringFromRequest('form_key'))) {
				exit_form_double_submit();
			}

			$snippet_id = getStringFromRequest('snippet_id');
			$changes = getStringFromRequest('changes');
			$version = getStringFromRequest('version');
			$code = getStringFromRequest('code');

			/*
				Create a new snippet entry, then create a new snippet version entry
			*/
			if ($changes && $version && $code) {

				/*
					create the snippet version
				*/
				$sql="INSERT INTO snippet_version (snippet_id,changes,version,submitted_by,post_date,code) ".
					"VALUES ('$snippet_id','".htmlspecialchars($changes)."','".
						htmlspecialchars($version)."','".user_getid()."','".
						time()."','".htmlspecialchars($code)."')";
				$result=db_query($sql);
				if (!$result) {
					$feedback .= _('ERROR DOING SNIPPET VERSION INSERT!');
					echo db_error();
				} else {
					form_release_key(getStringFromRequest("form_key"));
					$feedback .= _('Snippet Version Added Successfully.');
				}
			} else {
				exit_error(_('Error'),_('Error - Go back and fill in all the information'));
			}

		}
		snippet_header(array('title'=>_('New snippet version')));

		?>
		<p><?php echo _('If you have modified a version of a snippet and you feel it is significant enough to share with others, please do so.'); ?></p>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
		<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>">
		<input type="hidden" name="post_changes" value="y" />
		<input type="hidden" name="type" value="snippet" />
		<input type="hidden" name="snippet_id" value="<?php echo $id; ?>" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />

		<table>
		<tr><td colspan="2"><strong><?php echo _('Version:'); ?></strong><br />
			<input type="text" name="version" size="10" maxlength="15" />
		</td></tr>

		<tr><td colspan="2"><strong><?php echo _('Changes:'); ?></strong><br />
			<textarea name="changes" rows="5" cols="45"></textarea>
		</td></tr>
  
		<tr><td colspan="2"><strong><?php echo _('Paste the Code Here:'); ?></strong><br />
			<textarea name="code" rows="30" cols="85" wrap="soft"></textarea>
		</td></tr>
 
		<tr><td colspan="2" style="text-align:center">
			<strong><?php echo _('Make sure all info is complete and accurate'); ?></strong>
			<br />
			<input type="submit" name="submit" value="<?php echo _('SUBMIT'); ?>" />
		</td></tr>
		</table></form></p>
		<?php

		snippet_footer(array());

	} else if ($type=='package') {
		/*
			Handle insertion of a new package version
		*/

		/*
			See if the package exists first
		*/
		$result=db_query("SELECT * FROM snippet_package WHERE snippet_package_id='$id'");
		if (!$result || db_numrows($result) < 1) {
			exit_error(_('Error - snippet_package doesn\'t exist'));
		}

		if (getStringFromRequest('post_changes')) {
			if (!form_key_is_valid(getStringFromRequest('form_key'))) {
				exit_form_double_submit();
			}

			$snippet_package_id = getIntFromRequest('snippet_package_id');
			$changes = getStringFromRequest('changes');
			$version = getStringFromRequest('version');

			/*
				Create a new snippet entry, then create a new snippet version entry
			*/
			if ($changes && $snippet_package_id) {
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
					$feedback .= _('ERROR DOING SNIPPET PACKAGE VERSION INSERT!');
					snippet_header(array('title'=>_('New snippet package')));
					echo db_error();
					snippet_footer(array());
					exit;
				} else {
					//so far so good - now add snippets to the package
					$feedback .= _('Snippet Package Version Added Successfully.');

					//id for this snippet_package_version
					$snippet_package_version_id=
						db_insertid($result,'snippet_package_version','snippet_package_version_id');
					snippet_header(array('title'=>_('Add snippet to package')));

/*
	This raw HTML allows the user to add snippets to the package
*/
					?>

<script type="text/javascript">
<!--
function show_add_snippet_box() {
	newWindow = open("","occursDialog","height=500,width=300,scrollbars=yes,resizable=yes");
	newWindow.location=('/snippet/add_snippet_to_package.php?snippet_package_version_id=<?php
			echo $snippet_package_version_id; ?>');
}
// -->
</script>
<body onLoad="show_add_snippet_box()">

<p><span class="important"><?php echo _('IMPORTANT!'); ?></span></p>
<p>
<?php echo _('If a new window opened, use it to add snippets to your package. If a new window did not open, use the following link to add to your package BEFORE you leave this page.'); ?>
</p>
<p>
<?php echo util_make_url ('/snippet/add_snippet_to_package.php?snippet_package_version_id='.$snippet_package_version_id,_('Add snippets to package'),array('target'=>'_blank')); ?></p>
<p><?php echo _('<strong>Browse the library</strong> to find the snippets you want to add, then add them using the new window link shown above.'); ?></p>
<p>

					<?php

					snippet_footer(array());
					exit;
				}

			} else {
				form_release_key(getStringFromRequest("form_key"));
				exit_error( _('Error - Go back and fill in all the information'));
			}

		}
		snippet_header(array('title'=>_('New snippet version')));

		?>
		</p>
		<p>
		<?php echo _('If you have modified a version of a package and you feel it is significant enough to share with others, please do so.'); ?></p>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
		<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>">
		<input type="hidden" name="post_changes" value="y" />
		<input type="hidden" name="type" value="package" />
		<input type="hidden" name="snippet_package_id" value="<?php echo $id; ?>" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />

		<table>
		<tr><td colspan="2"><strong><?php echo _('Version:'); ?></strong><br />
			<input type="text" name="version" size="10" maxlength="15" />
		</td></tr>

		<tr><td colspan="2"><strong><?php echo _('Changes:'); ?></strong><br />
			<textarea name="changes" rows="5" cols="45"></textarea>
		</td></tr>

		<tr><td colspan="2" style="text-align:center">
			<strong><?php echo _('Make sure all info is complete and accurate'); ?></strong>
			<br />
			<input type="submit" name="submit" value="<?php echo _('SUBMIT'); ?>" />
		</td></tr>
		</table></form></p>
		<?php

		snippet_footer(array());


	} else {
		exit_error(_('Error - was the URL or form mangled??'));
	}

} else {

	exit_not_logged_in();

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
