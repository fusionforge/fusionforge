<?php
// ## export bugs for a specific project
include "pre.php";

header("Content-Type: text/plain");
print("<?xml version=\"1.0\"?>
<!DOCTYPE sf_bugs SYSTEM \"http://$sys_default_domain/export/sf_bugs_0.1.dtd\">
<bugs>
");

if( !isset($group_id) ) {
	print("    <error>Group ID Not Set</error>\n");
} else {
	$project=group_get_object($group_id);
	if( !$project->userIsAdmin() ) {
		print("    <error>You are not an administrator for this project</error>\n");
		print("</bugs>\n");
		exit;
	}

	$query = "SELECT 
				b.*
			  FROM 
				bug b
			  WHERE 
				b.group_id='$group_id'";
	$res = db_query($query);

	while( $row = db_fetch_array($res) ) {
		$submitted_by = db_result(db_query("SELECT user_name FROM users WHERE user_id='" . $row["submitted_by"] . "'"),0,"user_name");
		$assigned_to = db_result(db_query("SELECT user_name FROM users WHERE user_id='" . $row["assigned_to"] . "'"),0,"user_name");
		print("    <bug id='" . $row["bug_id"] . "'>\n");
		print("        <group_id>$group_id</group_id>\n");
		print("        <status_id>" . $row["status_id"] . "</status_id>\n");
		print("        <priority>" . $row["priority"] . "</priority>\n");
		print("        <category_id>" . $row["category_id"] . "</category_id>\n");
		print("        <submitted_by id='" . $row["submitted_by"] . "' name='$submitted_by'/>\n");
		print("        <assigned_to id='" . $row["assigned_to"] . "' name='$assigned_to'/>\n");
		print("        <date>" . $row["date"] . "</date>\n");
		print("        <summary>" . $row["summary"] . "</summary>\n");
		print("        <details>" . $row["details"] . "</details>\n");
		print("        <close_date>" . $row["close_date"] . "</close_date>\n");
		print("        <bug_group_id>" . $row["bug_group_id"] . "</bug_group_id>\n");
		print("        <resolution>" . $row["resolution"] . "</resolution>\n");

		$res_deps = db_query("SELECT * FROM bug_bug_dependencies WHERE bug_id='" . $row["bug_id"] . "'");
		while( $row2=db_fetch_array($res_deps) ) {
				print("        <dependency id='" . $row2["bug_depend_id"] . "'>\n");
				print("            <dep_id>" . $row2["is_dependent_on_bug_id"] . "</dep_id>\n");
				print("        </dependency>\n");
		}

		$res_hist = db_query("SELECT * FROM bug_history WHERE bug_id='" . $row["bug_id"] . "'");
		while( $row3=db_fetch_array($res_hist) ) {
				print("        <history id='" . $row3["bug_history_id"] . "'>\n");
				print("            <field_name>" . $row3["field_name"] . "</field_name>\n");
				print("            <old_value>" . $row3["old_value"] . "</old_value>\n");
				print("            <mod_by>" . $row3["mod_by"] . "</mod_by>\n");
				print("            <date>" . $row3["date"] . "</date>\n");
				print("        </history>\n");
		}

		$res_task = db_query("SELECT * FROM bug_task_dependencies WHERE bug_id='" . $row["bug_id"] . "'");
		while( $row4=db_fetch_array($res_task) ) {
				print("        <task_dependency id='" . $row4["bug_depend_id"] . "'>\n");
				print("            <dep_on_task_id>" . $row4["is_dependent_on_task_id"] . "</dep_on_task_id>\n");
				print("        </task_dependency>\n");
		}
		print("    </bug>\n");
	}
}
?>
</bugs>
