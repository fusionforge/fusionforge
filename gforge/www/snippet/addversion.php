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
	if ($type=='snippet') {
		/*
			See if the snippet exists first
		*/
		$result=db_query("SELECT * FROM snippet WHERE snippet_id='$id'");
		if (!$result || db_numrows($result) < 1) {
			exit_error($Language->getText('snippet_addversion','error_snippet_doesnt_exist'));
		}

		/*
			handle inserting a new version of a snippet
		*/
		if ($post_changes) {
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
					$feedback .= $Language->getText('snippet_addversion','error_doing_snippet_version_insert');
					echo db_error();
				} else {
					$feedback .= $Language->getText('snippet_addversion','snippet_version_added_successfully');
				}
			} else {
				exit_error($Language->getText('general','error'),$Language->getText('snippet_addversion','error_go_back_and_fill_in_all'));
			}

		}
		snippet_header(array('title'=>$Language->getText('snippet_addversion','submit_a_new_snippet_version'),'pagename'=>'snippet_addversion'));

		?>
		<p><?php echo $Language->getText('snippet_addversion','if_you_have_modified_a_version'); ?></p>
		<p>
		<form action="<?php echo $PHP_SELF; ?>" method="post">
		<input type="hidden" name="post_changes" value="y" />
		<input type="hidden" name="type" value="snippet" />
		<input type="hidden" name="snippet_id" value="<?php echo $id; ?>" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />

		<table>
		<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_addversion','version'); ?></strong><br />
			<input type="text" name="version" size="10" maxlength="15" />
		</td></tr>

		<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_addversion','changes'); ?></strong><br />
			<textarea name="changes" rows="5" cols="45"></textarea>
		</td></tr>
  
		<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_addversion','paste_the_code_here'); ?></strong><br />
			<textarea name="code" rows="30" cols="85" wrap="soft"></textarea>
		</td></tr>
 
		<tr><td colspan="2" align="center">
			<strong><?php echo $Language->getText('snippet_addversion','make_sure_all_info_is_complete'); ?></strong>
			<br />
			<input type="submit" name="submit" value="<?php echo $Language->getText('snippet_addversion','submit'); ?>" />
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
			exit_error($Language->getText('snippet_addversion','error_snippet_package_doesnt_exist'));
		}

		if ($post_changes) {
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
					$feedback .= $Language->getText('snippet_addversion','error_doing_snippet_package_version_insert');
					snippet_header(array('title'=>$Language->getText('snippet_addversion','title_submit_a_new_snippet_package'),'pagename'=>'snippet_addversion'));
					echo db_error();
					snippet_footer(array());
					exit;
				} else {
					//so far so good - now add snippets to the package
					$feedback .= $Language->getText('snippet_addversion','snippet_package_version_added_successfully');

					//id for this snippet_package_version
					$snippet_package_version_id=
						db_insertid($result,'snippet_package_version','snippet_package_version_id');
					snippet_header(array('title'=>$Language->getText('snippet_addversion','title_add_snippet_to_package'),'pagename'=>'snippet_addversion'));

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

<p><span style="color:red"><strong><?php echo $Language->getText('snippet_addversion','important'); ?></strong></span></p>
<p>
<?php echo $Language->getText('snippet_addversion','if_new_window_opened'); ?>
</p>
<p>
<a href="/snippet/add_snippet_to_package.php?snippet_package_version_id=<?php echo $snippet_package_version_id; ?>" target="_blank"><?php echo $Language->getText('snippet_addversion','link_add_snippets_to_package'); ?></a></p>
<p><?php echo $Language->getText('snippet_addversion','browse_the_library'); ?></p>
<p>

					<?php

					snippet_footer(array());
					exit;
				}

			} else {
				exit_error( $Language->getText('snippet_addversion','error_go_back_and_fill_in_all'));
			}

		}
		snippet_header(array('title'=>$Language->getText('snippet_addverion','title_submit_a_new_snippet_version'),'pagename'=>'snippet_addversion'));

		?>
		</p>
		<p>
		<?php echo $Language->getText('snippet_addversion','if_you_have_modified'); ?></p>
		<p>
		<form action="<?php echo $PHP_SELF; ?>" method="post">
		<input type="hidden" name="post_changes" value="y" />
		<input type="hidden" name="type" value="package" />
		<input type="hidden" name="snippet_package_id" value="<?php echo $id; ?>" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />

		<table>
		<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_addversion','version'); ?></strong><br />
			<input type="text" name="version" size="10" maxlength="15" />
		</td></tr>

		<tr><td colspan="2"><strong><?php echo $Language->getText('snippet_addversion','changes'); ?></strong><br />
			<textarea name="changes" rows="5" cols="45"></textarea>
		</td></tr>

		<tr><td colspan="2" align="center">
			<strong><?php echo $Language->getText('snippet_addversion','make_sure_all_info_is_complete'); ?></strong>
			<br />
			<input type="submit" name="submit" value="<?php echo $Language->getText('snippet_addversion','submit'); ?>" />
		</td></tr>
		</table></form></p>
		<?php

		snippet_footer(array());


	} else {
		exit_error($Language->getText('snippet_addversion','error_url_or_form'));
	}

} else {

	exit_not_logged_in();

}

?>
