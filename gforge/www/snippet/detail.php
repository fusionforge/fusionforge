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

/*

	Show a detail page for either a snippet or a package
	or a specific version of a package

*/

if ($type=='snippet') {
	/*


		View a snippet and show its versions
		Expand and show the code for the latest version


	*/

	snippet_header(array('title'=>$Language->getText('snippet_detail','title'),'pagename'=>'snippet_detail'));

	snippet_show_snippet_details($id);

	/*
		Get all the versions of this snippet
	*/
	$sql="SELECT users.realname,snippet_version.snippet_version_id,snippet_version.version,snippet_version.post_date,snippet_version.changes ".
		"FROM snippet_version,users ".
		"WHERE users.user_id=snippet_version.submitted_by AND snippet_id='$id' ".
		"ORDER BY snippet_version.snippet_version_id DESC";

	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<h3>' .$Language->getText('snippet_detail','error_no_version_found').'</h3>';
	} else {
		echo '
		<h3>' .$Language->getText('snippet_detail','version_of_this_snippet').':</h3>
		<p>';
		$title_arr=array();
		$title_arr[]= $Language->getText('snippet_detail','snippet_id');
		$title_arr[]= $Language->getText('snippet_detail','download_version');
		$title_arr[]= $Language->getText('snippet_detail','date_posted');
		$title_arr[]= $Language->getText('snippet_detail','author');
		$title_arr[]= $Language->getText('snippet_detail','delete');
		
		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		/*
			get the newest version of this snippet, so we can display its code
		*/
		$newest_version=db_result($result,0,'snippet_version_id');

		for ($i=0; $i<$rows; $i++) {
			echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.db_result($result,$i,'snippet_version_id').
				'</td><td><a href="/snippet/download.php?type=snippet&amp;id='.
				db_result($result,$i,'snippet_version_id').'"><strong>'.
				db_result($result,$i,'version').'</strong></a></td><td>'. 
				date($sys_datefmt,db_result($result,$i,'post_date')).'</td><td>'.
				db_result($result,$i,'realname').'</td><td align="center"><a href="/snippet/delete.php?type=snippet&amp;snippet_version_id='.
				db_result($result,$i,'snippet_version_id').
				'">' . html_image("ic/trash.png","16","16",array("border"=>"0")) . '</a></td></tr>';

				if ($i != ($rows - 1)) {
					echo '
					<tr'.$row_color.'><td colspan="5">' .$Language->getText('snippet_detail','changes_since_last_version').':<br />'.
					nl2br(db_result($result,$i,'changes')).'</td></tr>';
				}
		}

		echo $GLOBALS['HTML']->listTableBottom();

		echo '
		</p><p>'.$Language->getText('snippet_detail','download_a_raw_text').'
		</p>';
	}
	/*
		show the latest version of this snippet's code
	*/
	$result=db_query("SELECT code,version FROM snippet_version WHERE snippet_version_id='$newest_version'");	

	echo '
		<p>&nbsp;</p>
		<hr />
		<h2>'.$Language->getText('snippet_detail','latest_snippet_version').' :'.db_result($result,0,'version').'</h2>
		<p>
		<pre><span style="font-size:smaller">'. db_result($result,0,'code') .'
		</span></pre>
		</p>';
	/*
		Show a link so you can add a new version of this snippet
	*/
	echo '
	<h3><a href="/snippet/addversion.php?type=snippet&amp;id='.$id.'"><span style="color:red">'.$Language->getText('snippet_detail','submit_a_new_snippet').'</span></a></h3>
	<p>' .$Language->getText('snippet_detail','you_can_submit_a_new').'.</p>';

	snippet_footer(array());

} else if ($type=='package') {
	/*


		View a package and show its versions
		Expand and show the snippets for the latest version


	*/

	snippet_header(array('title'=>$Language->getText('snippet_detail','title'),'pagename'=>'snippet_detail'));

	snippet_show_package_details($id);

	/*
		Get all the versions of this package
	*/
	$sql="SELECT users.realname,snippet_package_version.snippet_package_version_id,".
		"snippet_package_version.version,snippet_package_version.post_date ".
		"FROM snippet_package_version,users ".
		"WHERE users.user_id=snippet_package_version.submitted_by AND snippet_package_id='$id' ".
		"ORDER BY snippet_package_version.snippet_package_version_id DESC";

	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<h3>' .$Language->getText('snippet_detail','error_no_version_found').'</h3>';
	} else {
		echo '
		<h3>' .$Language->getText('snippet_detail','version_of_this_package').':</h3>
		<p>';
		$title_arr=array();
		$title_arr[]= $Language->getText('snippet_detail','package_version');
		$title_arr[]= $Language->getText('snippet_detail','date_posted');
		$title_arr[]= $Language->getText('snippet_detail','author');
		$title_arr[]= $Language->getText('snippet_detail','edit_del');

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		/*
			determine the newest version of this package, 
			so we can display the snippets that it contains
		*/
		$newest_version=db_result($result,0,'snippet_package_version_id');

		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><a href="/snippet/detail.php?type=packagever&amp;id='.
				db_result($result,$i,'snippet_package_version_id').'"><strong>'.
				db_result($result,$i,'version').'</strong></a></td><td>'.
				date($sys_datefmt,db_result($result,$i,'post_date')).'</td><td>'.
				db_result($result,$i,'realname').
				'</td><td align="center"><a href="/snippet/add_snippet_to_package.php?snippet_package_version_id='.
				db_result($result,$i,'snippet_package_version_id').
				'">' . html_image("ic/pencil.png","20","25",array("border"=>"0")) .
				'</a> &nbsp; &nbsp; &nbsp; <a href="/snippet/delete.php?type=package&snippet_package_version_id='.
				db_result($result,$i,'snippet_package_version_id').
				'">' . html_image("ic/trash.png","16","16",array("border"=>"0")) . '</a></td></tr>';
		}

		echo $GLOBALS['HTML']->listTableBottom();

		echo '
		</p><p>' .$Language->getText('snippet_detail','download_a_raw_text').'
		</p>';
	}

	/*
		show the latest version of the package
		and its snippets
	*/

	echo '
		<p>&nbsp;</p>
		<hr />
		<h2>' .$Language->getText('snippet_detail','latest_package_version').' : '.db_result($result,0,'version').'</h2>
		<p>&nbsp;</p>
		<p>&nbsp;</p>';
	snippet_show_package_snippets($newest_version);

	/*
		Show a form so you can add a new version of this package
	*/
	echo '
	<h3><a href="/snippet/addversion.php?type=package&amp;id='.$id.'"><span style="color:red">' .$Language->getText('snippet_detail','submit_a_new_version').'</span></a></h3>
	<p>' .$Language->getText('snippet_detail','you_can_submit_a_new_version_of_package').'.</p>';

	snippet_footer(array());

} else if ($type=='packagever') {
	/*
		Show a specific version of a package and its specific snippet versions
	*/
	
	snippet_header(array('title'=>$Language->getText('snippet_detail','title'),'pagename'=>'snippet_detail'));

	snippet_show_package_details($id);

	snippet_show_package_snippets($id);

	snippet_footer(array());

} else {

	exit_error($Language->getText('general','error'),$Language->getText('snippet_detail','error_was_the_url_mangled'));

}

?>
