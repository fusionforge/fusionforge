<?php
/**
  *
  * Site Admin page to edit language localizations
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');    
require_once('common/include/account.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

if ($submit) {
	db_query("
		INSERT INTO supported_languages (name,filename,classname)
		VALUES ('$name','$filename','$classname')
	");
}

$HTML->header(array('title'=>'Add New Languages'));

$sql="SELECT * FROM supported_languages ORDER BY name ASC";
$res=db_query($sql);

echo ShowResultSet($res,'Existing Languages');

echo '
<P>
<h3>Add New Language</h3>
<P>
<FORM ACTION="'. $PHP_SELF .'" METHOD="POST">
<B>Name:</B><BR>
<INPUT TYPE="TEXT" NAME="name" VALUE="">
<P>
<B>Class Filename:</B><BR>
<INPUT TYPE="TEXT" NAME="filename" VALUE=".class">
<P>
<B>Classname:</B><BR>
<INPUT TYPE="TEXT" NAME="classname" VALUE="">
<P>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Submit">
</FORM>
';

$HTML->footer(array());

?>
