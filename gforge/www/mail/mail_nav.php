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


echo "\n\n<table border=\"0\" width=\"100%\">".
	"\n<tr><td nowrap=\"nowrap\">";

echo "<a href=\"/project/?group_id=$group_id\">" .
		html_image("ic/ofolder15.png","15","13",array("border"=>"0")) . " &nbsp; " .
		group_getname($group_id)." Home Page</a><br />";
echo " &nbsp; &nbsp; <a href=\"/mail/?group_id=$group_id\">" .
        html_image("ic/ofolder15.png","15","13",array("border"=>"0")) . " &nbsp; Mailing Lists</a><br />";
if ($is_admin_page) {
        echo " &nbsp; &nbsp; &nbsp; &nbsp; <a href=\"/survey/admin/?group_id=$group_id\">".
				html_image("ic/ofolder15.png","15","13",array("border"=>"0")) . " &nbsp; Administration</a>";
}
echo "</td></tr>";
echo "\n</table>\n";

?>
