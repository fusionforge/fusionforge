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
<p>&nbsp;</p>
<h3>Add New Language</h3>
<form action="'. $PHP_SELF .'" method="post">
<p><strong>Name:</strong><br />
<input type="text" name="name" value="" /></p>
<p>
<strong>Class Filename:</strong><br />
<input type="text" name="filename" value=".class" /></p>
<p>
<strong>Classname:</strong><br />
<input type="text" name="classname" value="" /></p>
<p>
<input type="submit" name="submit" value="Submit"></p>
</form>
';

$HTML->footer(array());

?>
