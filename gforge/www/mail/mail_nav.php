<?php
/**
  *
  * SourceForge Mailing Lists Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: mail_nav.php,v 1.5 2001/05/22 18:52:31 pfalcon Exp $
  *
  */


echo "\n\n<TABLE BORDER=0 WIDTH=\"100%\">".
	"\n<TR><TD NOWRAP>";

echo "<A HREF=\"/project/?group_id=$group_id\">" .
		html_image("images/ic/ofolder15.png","15","13",array("BORDER"=>"0")) . " &nbsp; " .
		group_getname($group_id)." Home Page</A><BR>";
echo " &nbsp; &nbsp; <A HREF=\"/mail/?group_id=$group_id\">" .
        html_image("images/ic/ofolder15.png","15","13",array("BORDER"=>"0")) . " &nbsp; Mailing Lists</A><BR>";
if ($is_admin_page) {
        echo " &nbsp; &nbsp; &nbsp; &nbsp; <A HREF=\"/survey/admin/?group_id=$group_id\">".
				html_image("images/ic/ofolder15.png","15","13",array("BORDER"=>"0")) . " &nbsp; Administration</A>";
}
echo "</TD></TR>";
echo "\n</TABLE>\n";

?>
