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
<?
echo 'Click here to <a href="qrs.php?package_id=' . $package_id . '&group_id=' . $group_id . '">quick-release a file</a>.<br>';

$user_unix_name=user_getname();
?>
<H3>Packages</H3>
<P>
You can use packages to group different file releases together, or use them however you like. 
<P>
<H4>An example of packages:</h4>
<P>
<B>Mysql-win</B><BR>
<B>Mysql-unix</B><BR>
<B>Mysql-odbc</B>
<P>
<h4>Your Packages:</H4>
<P>
Start by defining your packages, then you can upload files with FTP to the <B>incoming</B> directory on 
<B><a href=ftp://$user_unix_name@$sys_upload_host/incoming/>$sys_upload_host</a></B>. Once you have the files uploaded, you can then <B>create releases</B> 
of your packages.
<P>
Once you have packages defined, you can start creating new <B>releases of packages.</B>
<P>
<H3>Releases of Packages</H3>
<P>
A release of a package can contain multiple files.
<P>
<H4>Examples of Releases</h4>
<P>
<B>3.22.1</B><BR>
<B>3.22.2</B><BR>
<B>3.22.3</B><BR>
<P>
You can create new releases of packages by clicking on <B>Add/Edit Releases</B> next to your package name.
<P>
<?
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
		<FORM ACTION="'. $PHP_SELF .'" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'.$group_id.'">
		<INPUT TYPE="HIDDEN" NAME="func" VALUE="update_package">
		<INPUT TYPE="HIDDEN" NAME="package_id" VALUE="'. db_result($res,$i,'package_id') .'">
		<TR '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
			<TD NOWRAP ALIGN="center">
				<FONT SIZE="-1">
					<A HREF="qrs.php?package_id='. 
						db_result($res,$i,'package_id') .'&group_id='. $group_id .'"><B>[Add Release]</B>
					</A>
				</FONT>
				<FONT SIZE="-1">
					<A HREF="showreleases.php?package_id='. 
						db_result($res,$i,'package_id') .'&group_id='. $group_id .'"><B>[Edit Releases]</B>
					</A>
				</FONT>

			</TD>
			<TD><FONT SIZE="-1"><INPUT TYPE="TEXT" NAME="package_name" VALUE="'.db_result($res,$i,'package_name') .'" SIZE="20" MAXLENGTH="30"></TD>
			<TD><FONT SIZE="-1">'.frs_show_status_popup ('status_id', db_result($res,$i,'status_id')).'</TD>
			<TD><FONT SIZE="-1"><INPUT TYPE="SUBMIT" NAME="submit" VALUE="Update"></TD>
			</TR></FORM>';
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

/*

	form to create a new package

*/

?>
<P>
<h3>New Package Name:</h3>
<P>
<FORM ACTION="<?php echo $PHP_SELF; ?>" METHOD="POST">
<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="<?php echo $group_id; ?>">
<INPUT TYPE="HIDDEN" NAME="func" VALUE="add_package">
<INPUT TYPE="TEXT" NAME="package_name" VALUE="" SIZE="20" MAXLENGTH="30">
<P>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Create This Package">
</FORM>

<?php

project_admin_footer(array());

?>
