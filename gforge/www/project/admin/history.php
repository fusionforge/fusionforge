<?php
/**
  *
  * Project Admin page to show audit trail for group
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

project_admin_header(array('title'=>$Language->getText('project_admin_history','title'),'group'=>$group_id,'pagename'=>'project_admin_history','sectionvals'=>array(group_getname($group_id))));

?>

<p><?php echo $Language->getText('project_admin_history','info') ?>.</p>
<?php
echo show_grouphistory($group_id);

project_admin_footer(array());
?>
