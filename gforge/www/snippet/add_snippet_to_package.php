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

$suppress_nav = getStringFromRequest('suppress_nav');

function handle_add_exit() {
	global $suppress_nav;
        if ($suppress_nav) {
                echo '
                </body></html>';
        } else {
                snippet_footer(array());
        }
	exit;
}

if (session_loggedin()) {
	$snippet_package_version_id = getIntFromRequest('snippet_package_version_id');
	$snippet_version_id = getIntFromRequest('snippet_version_id');

	if ($suppress_nav) {
		echo '
		<html>
		<body>';
	} else {
		snippet_header(array('title'=>_('Submit A New Snippet')));
	}

	if (!$snippet_package_version_id) {
		//make sure the package id was passed in
		echo '<h1>' ._('Error - snippet_package_version_id missing') .'</h1>';
		handle_add_exit();
	}

	if (getStringFromRequest('post_changes')) {
		/*
			Create a new snippet entry, then create a new snippet version entry
		*/
		if ($snippet_package_version_id && $snippet_version_id) {
			/*
				check to see if they are the creator of this version
			*/
			$result=db_query("SELECT * FROM snippet_package_version ".
				"WHERE submitted_by='".user_getid()."' AND ".
				"snippet_package_version_id='$snippet_package_version_id'");
			if (!$result || db_numrows($result) < 1) {
				echo '<h1>' ._('Error - Only the creator of a package version can add snippets to it.').'</h1>';
				handle_add_exit();
			}

			/*
				make sure the snippet_version_id exists
			*/
			$result=db_query("SELECT * FROM snippet_version WHERE snippet_version_id='$snippet_version_id'");
			if (!$result || db_numrows($result) < 1) {
				echo '<h1>' ._('Error - That snippet doesn\'t exist.').'</h1>';
				echo '<a href="'.$GLOBALS['sys_urlprefix'].'/snippet/add_snippet_to_package.php?snippet_package_version_id='.$snippet_package_version_id.'">' ._('Back To Add Page').'</a>';
				handle_add_exit();
			}

			/*
				make sure the snippet_version_id isn't already in this package
			*/
			$result=db_query("SELECT * FROM snippet_package_item ".
				"WHERE snippet_package_version_id='$snippet_package_version_id' ".
				"AND snippet_version_id='$snippet_version_id'");
			if ($result && db_numrows($result) > 0) {
				echo '<h1>'._('Error - That snippet was already added to this package.').'</h1>';
				echo '<a href="'.$GLOBALS['sys_urlprefix'].'/snippet/add_snippet_to_package.php?snippet_package_version_id='.$snippet_package_version_id.'">'._('Back To Add Page').'</a>';
				handle_add_exit();
			}

			/*
				create the snippet version
			*/
			$sql="INSERT INTO snippet_package_item (snippet_package_version_id,snippet_version_id) ".
				"VALUES ('$snippet_package_version_id','$snippet_version_id')";
			$result=db_query($sql);

			if (!$result) {
				$feedback .= _('ERROR DOING SNIPPET VERSION INSERT!');
				echo db_error();
			} else {
				$feedback .= _('Snippet Version Added Successfully.');
			}
		} else {
			echo '<h1>' ._('Error - Go back and fill in all the information').'</h1>';
			echo '<a href="'.$GLOBALS['sys_urlprefix'].'/snippet/add_snippet_to_package.php?snippet_package_version_id='.$snippet_package_version_id.'">'._('Back To Add Page').'</a>';
			handle_add_exit();
		}

	}

	$result=db_query("SELECT snippet_package.name,snippet_package_version.version ".
			"FROM snippet_package,snippet_package_version ".
			"WHERE snippet_package.snippet_package_id=snippet_package_version.snippet_package_id ".
			"AND snippet_package_version.snippet_package_version_id='$snippet_package_version_id'");

	?>
	<p>
	<strong><?php echo _('Package:'); ?></strong><br />
	<?php echo db_result($result,0,'name') . ' -  ' . db_result($result,0,'version'); ?></p>
	<p><?php echo _('You can use this form repeatedly to keep adding snippets to your package.</p><p>The "Snippet Version ID" is the unique ID number that is shown next to a specific version of a snippet on the browse pages.'); ?></p>
	<p>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="snippet_package_version_id" value="<?php echo $snippet_package_version_id; ?>" />
	<input type="hidden" name="suppress_nav" value="<?php echo $suppress_nav; ?>" />

	<table>
	<tr><td colspan="2" align="center">
		<strong><?php echo _('Add This Snippet Version ID:'); ?></strong><br />
 <select name="snippet_version_id">
<?php

$combolistresult=db_query
("SELECT myname,snippet_version.snippet_version_id
FROM ( SELECT MAX(post_date) AS
mydate,name AS myname,snippet.snippet_id AS myid
FROM
snippet,snippet_version
WHERE
snippet.snippet_id=snippet_version.snippet_id
GROUP BY
name,snippet.snippet_id ) AS foo,snippet_version
WHERE
snippet_version.post_date=mydate;");
$combolistrows=db_numrows($combolistresult);
for ($i=0; $i<$combolistrows; $i++)
{
	print '<option value='.db_result($combolistresult,$i,'snippet_version_id').'>'.db_result($combolistresult,$i,'myname').'</option>';
}
?>
</select>
	</td></tr>

	<tr><td colspan="2" align="center">
		<strong><?php echo _('Make sure all info is complete and accurate'); ?></strong>
		<br />
		<input type="submit" name="submit" value="<?php echo _('SUBMIT'); ?>" />
	</td></tr>
	</table></form></p>
	<?php
	/*
		Show the snippets in this package
	*/
	$result=db_query("SELECT snippet_package_item.snippet_version_id, snippet_version.version, snippet.name ".
		"FROM snippet,snippet_version,snippet_package_item ".
		"WHERE snippet.snippet_id=snippet_version.snippet_id ".
		"AND snippet_version.snippet_version_id=snippet_package_item.snippet_version_id ".
		"AND snippet_package_item.snippet_package_version_id='$snippet_package_version_id'");
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo db_error();
		echo '
		<p>' ._('No Snippets Are In This Package Yet').'</p>';
	} else {
		echo $HTML->boxTop(_('Snippets In This Package'));
		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td align="center">
				<a href="'.$GLOBALS['sys_urlprefix'].'/snippet/delete.php?type=frompackage&snippet_version_id='.
				db_result($result,$i,'snippet_version_id').
				'&snippet_package_version_id='.$snippet_package_version_id.
				'">' . html_image("ic/trash.png","16","16",array("border"=>"0")) . '</a></td><td width="99%">'.
				db_result($result,$i,'name').' '.db_result($result,$i,'version')."</td></tr>";

			$last_group=db_result($result,$i,'group_id');
		}
		echo $HTML->boxBottom();
	}

	handle_add_exit();

} else {

	exit_not_logged_in();

}

?>
