<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: editaliases-delete.php,v 1.4 2000/01/26 10:44:32 tperdue Exp $

require "pre.php";    
require "account.php";
require ($DOCUMENT_ROOT.'/project/admin/project_admin_utils.php');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

db_query("DELETE FROM mailaliases WHERE mailaliases_id=$form_mailid AND group_id=$group_id");

session_redirect("/project/admin/editaliases.php?group_id=$group_id");
?>
