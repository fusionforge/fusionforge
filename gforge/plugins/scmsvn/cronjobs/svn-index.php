<?php
 
require_once('squal_pre.php');    // Initial db and session library, opens session
?>
<html> 
 <head> 
 <title>Subversion Repositories at <?php echo $sys_name; ?></title> 
 </head> 
 <body> 
 


<h2>Subversion Repositories at <?php echo $sys_name; ?> </h2> 
 <p><?php 
     $svnparentpath = "/var/lib/gforge/docman/groups"; 
     $svnparenturl = "/groups"; 
 
    db_begin();

    $res = db_query("SELECT unix_group_name,group_name FROM groups WHERE status='A' AND is_public='1' AND use_cvs='1';");
    if (!$res) {
            echo "Error!\n";
    }
	    
    while ( $row = db_fetch_array($res) ) {
	$svndir = $svnparentpath . "/" . $row["unix_group_name"];
	if ( is_dir ($svndir) ) {
		echo "<a href=\"" . $svnparenturl . "/". $row["unix_group_name"] ."\">"; 
		echo $row["group_name"]. "</a><br />\n";
	}

    }
 ?> 
 </p> 
 


</body> 
</html> 
