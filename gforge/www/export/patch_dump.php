<?php
require_once('pre.php');
header("Content-Type: text/plain");
print("<?xml version=\"1.0\"?>
<!DOCTYPE sf_bugs SYSTEM \"http://$sys_default_domain/export/sf_patches_0.1.dtd\">
<patches>
");

 if( !isset($group_id) ) {
         print(" <error>Group ID Not Set</error>\n");
 } else {
         $project=group_get_object($group_id);
         if( !$project->userIsAdmin() ) {
                 print(" <error>You are not an administrator for this project</error>\n");
                 print("</bugs>\n");
                 exit;
         }

         $query = "SELECT
                                 p.*
                          FROM
                                 patch p
                          WHERE
                                 p.group_id='$group_id'";
         $res = db_query($query);

         while( $row = db_fetch_array($res) ) {
                 $submitted_by = db_result(db_query("SELECT user_name FROM users WHERE user_id='" . $row["submitted_by"] . "'"),0,"user_name");
                 $assigned_to = db_result(db_query("SELECT user_name FROM users WHERE user_id='" . $row["assigned_to"] . "'"),0,"user_name");
                 print(" <patch id='" . $row["patch_id"] . "'>\n");
                 print(" <group_id>" . $row["group_id"] . "</group_id>\n");
                 print(" <status_id>" . $row["status_id"] . "</status_id>\n");
		 print(" <priority>" . $row["priority"] . "</priority>\n");
                 print(" <category_id>" . $row["category_id"] . "</category_id>\n");
                 print(" <submitted_by id='" . $row["submitted_by"] . "' name='$submitted_by'/>\n");
                 print(" <assigned_to id='" . $row["assigned_to"] . "' name='$assigned_to'/>\n");
                 print(" <open_date>" . $row["open_date"] . "</open_date>\n");
                 print(" <summary>" . $row["summary"] . "</summary>\n");
                 print(" <details>" . $row["details"] . "</details>\n");
                 print(" <close_date>" . $row["close_date"] . "</close_date>\n");
                 print(" <bug_group_id>" . $row["bug_group_id"] ."</bug_group_id>\n");
                 print(" <resolution>" . $row["resolution"] ."</resolution>\n");

                 $res_hist = db_query("SELECT * FROM patch_history WHERE patch_id='" . $row["patch_id"] . "'");
                 while( $row2=db_fetch_array($res_hist) ) {
                                 print(" <history id='" . $row2["patch_history_id"] . "'>\n");
                                 print(" <field_name>" . $row2["field_name"] . "</field_name>\n");
                                 print(" <old_value>" . $row2["old_value"] . "</old_value>\n");
                                 print(" <mod_by>" . $row2["mod_by"] . "</mod_by>\n");
                                 print(" <date>" . $row2["date"] . "</date>\n");
                                 print(" </history>\n");
                 }
                 print(" </patch>\n");
         }
 }
 ?>
 </patches>
