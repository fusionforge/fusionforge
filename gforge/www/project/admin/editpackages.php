<?php
/**
  *
  * Project Admin: Edit Packages
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: editpackages.php.patched,v 1.1.2.1 2002/11/30 09:57:58 cbayle Exp $
  *
  */

require_once('pre.php');	
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id));
$project =& group_get_object($group_id);

exit_assert_object($project,'Project');

$perm =& $project->getPermission(session_get_user());

if (!$perm->isReleaseTechnician()) {
	exit_permission_denied();
}

/*


	Relatively simple form to edit/add packages of releases


*/

// only admin can modify packages (vs modifying releases of packages)
if ($submit) {
	/*

		make updates to the database

	*/
	if ($func=='add_package' && $package_name) {

		//create a new package
		db_query("INSERT INTO frs_package (group_id,name,status_id) ".
			"VALUES ('$group_id','". htmlspecialchars($package_name)  ."','1')");
		$feedback .= ' Added Package ';

	} else if ($func=='update_package' && $package_id && $package_name && $status_id) {
		if ($status_id != 1) {
			//if hiding a package, refuse if it has releases under it
			$res=db_query("SELECT * FROM frs_release WHERE package_id='$package_id' AND status_id=1");
			if (db_numrows($res) > 0) {
				$feedback .= ' Sorry - you cannot hide a package that contains active releases ';
				$status_id=1;
			}
		}
		//update an existing package
		db_query("UPDATE frs_package SET name='". htmlspecialchars($package_name)  ."', status_id='$status_id' ".
			"WHERE package_id='$package_id' AND group_id='$group_id'");
		$feedback .= ' Updated Package ';

	}

}


project_admin_header(array('title'=>'Release/Edit File Releases','group'=>$group_id,'pagename'=>'project_admin_editpackages','sectionvals'=>array(group_getname($group_id))));

?>
<h3>QRS:</h3>
<?php
echo 'Click here to <a href="qrs.php?package_id=' . $package_id . '&group_id=' . $group_id . '">quick-release a file</a>.<br />';
?>
<h3>Packages</h3>
<p>
You can use packages to group different file releases together, or use them however you like. 
<p>
<H4>An example of packages:</h4>
<p>
<strong>Mysql-win</strong><br />
<strong>Mysql-unix</strong><br />
<strong>Mysql-odbc</strong>
<p>
<h4>Your Packages:</H4>
<p>
<ol>
<li>Define your packages</li>
<li>Create new releases of packages</li>
</ol>
<p>
<h3>Releases of Packages</h3>
<p>
A release of a package can contain multiple files.
<p>
<H4>Examples of Releases</h4>
<p>
<strong>3.22.1</strong><br />
<strong>3.22.2</strong><br />
<strong>3.22.3</strong><br />
<p>
You can create new releases of packages by clicking on <strong>Add/Edit Releases</strong> next to your package name.
<p>
<?php
/*

	Show a list of existing packages
	for this project so they can
	be edited

*/

$res=db_query("SELECT status_id,package_id,name AS package_name FROM frs_package WHERE group_id='$group_id'");
$rows=db_numrows($res);
if (!$res || $rows < 1) {
	echo '<h4>You Have No Packages Defined</h4>';
} else {
	$title_arr=array();
	$title_arr[]='Releases';
	$title_arr[]='Package Name';
	$title_arr[]='Status';

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	for ($i=0; $i<$rows; $i++) {
		echo '
		<form action="'. $PHP_SELF .'" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<input type="hidden" name="func" value="update_package" />
		<input type="hidden" name="package_id" value="'. db_result($res,$i,'package_id') .'" />
		<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
			<td nowrap="nowrap" align="center">
				<span style="font-size:smaller">
					<a href="qrs.php?package_id='. 
						db_result($res,$i,'package_id') .'&amp;group_id='. $group_id .'"><strong>[Add Release]</strong>
					</a>
				</span>
				<span style="font-size:smaller">
					<a href="showreleases.php?package_id='. 
						db_result($res,$i,'package_id') .'&amp;group_id='. $group_id .'"><strong>[Edit Releases]</strong>
					</a>
				</span>

			</td>
			<td><span style="font-size:smaller"><input type="text" name="package_name" value="'.db_result($res,$i,'package_name') .'" size="20" maxlength="30" /></span></td>
			<td><span style="font-size:smaller">'.frs_show_status_popup ('status_id', db_result($res,$i,'status_id')).'</span></td>
			<td><input type="submit" name="submit" value="Update" /></td>
			</tr></form>';
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

/*

	form to create a new package

*/

?>
</p>
<h3>New Package Name:</h3>
<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="func" value="add_package" />
<input type="text" name="package_name" value="" size="20" maxlength="30" />
<p><input type="submit" name="submit" value="Create This Package" /></p>
</form></p>

<?php

project_admin_footer(array());

?>
