<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: about.php,v 1.29 2000/09/01 19:16:45 q Exp $

require "pre.php";    
$HTML->header(array(title=>"About ".$GLOBALS[sys_name]));
?>

<P>
<h2>About <?php echo $GLOBALS[sys_name]; ?></h2>

<?php echo $Language->ABOUT_BLURB; ?>

<?php
$HTML->footer(array());

?>
