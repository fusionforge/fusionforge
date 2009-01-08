<?php


//create the config file for webcalendar
$fichier_conf = "<?\n" . 
		"install_password: 764b9c698569b4e77fd69db363fef414" .
		"\ndb_type: postgresql" .
		"\ndb_host: ".$sys_gfdbhost.
		"\ndb_database: ".$sys_gfdbname.
		"\ndb_login: ".$sys_gfdbuser.
		"\ndb_password: ".$sys_gfdbpasswd.
		"\ndb_persistent: true" .
		"\nsingle_user_login:" . 
		"\nreadonly: false" .
		"\nuse_http_auth: false" .
		"\nsingle_user: false" .
		"\nuser_inc: user.php" .
		"\n?>" ;

$handle = fopen($sys_plugins_path . $pluginname . "/www/includes/settings.php", "w");
fwrite($handle,$fichier_conf);
fclose($handle);

//user
$query = "SELECT user_name,user_pw,email,user_id FROM users WHERE NOT user_name='None' ";
$res = db_query($query);
echo db_error();
while ($row = db_fetch_array($res)) {
	//verify if admin
	$query_flags = "SELECT COUNT(*) FROM user_group WHERE user_id = '".$row['user_id']."' AND admin_flags = 'A'  AND group_id = '1'";
	$res_flags = db_query($query_flags) ;
	$row_flags = db_fetch_array($res_flags) ;
	$cal_query = "INSERT INTO webcal_user (cal_login, cal_passwd, cal_email,cal_firstname, cal_is_admin) VALUES ('" . $row['user_name'] . "','" . $row['user_pw'] . "','" . $row['email'] . "','" . $row['user_name'] . "'";
	if ($row_flags[0] == 1)
		$cal_query .= ",'Y')";
	else
		$cal_query .= ",'N')";
		
	$cal_res = db_query($cal_query);

}

//group

$query2 = "SELECT  unix_group_name,groups.group_id,group_name,email FROM groups,users,user_group WHERE groups.group_id >5 AND groups.group_id = user_group.group_id AND user_group.user_id = users.user_id AND user_group.admin_flags = 'A' ";
$res2 = db_query($query2);
while ($row2 = db_fetch_array($res2)) {
	$cal_query2 = "INSERT INTO webcal_user (cal_login, cal_passwd, cal_firstname,cal_email) VALUES ('" . $row2['unix_group_name'] . "','qdkqshjddoshd','" . addslashes($row2['group_name']) . "','" . $row2['email'] . "')";

	
	//get for admin of project
	$query_user_group = "SELECT user_group.user_id,user_name,email from user_group,users WHERE user_group.user_id = users.user_id AND group_id = '".$row2['group_id']."' AND admin_flags = 'A'" ;
	//print $query_user_group ;
	$res_user_group = db_query($query_user_group);
	
	//get the email of the admin
	$query_mail ="SELECT cal_email FROM webcal_user WHERE  cal_login = '".$row2['unix_group_name']."'";			
	$res_mail = db_query($query_mail);
	$row_mail = db_fetch_array($res_mail);	
	$mail = $row_mail['cal_email'];
	
	if($res_user_group){
		while($row_user_group = db_fetch_array($res_user_group)) {
			$insert_ass = "INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ('".$row2['unix_group_name']."','".$row_user_group['user_name']."')"; 
			$cal_res = db_query($insert_ass);
			
			//add email
			$mail = str_replace($row_user_group['email'],"",$mail);
			$mail = str_replace(",".$row_user_group['email'],"",$mail);
			
			if($mail == ""){
			$virgule = "";	
			}
			else {
			$virgule = ",";	
			}
			$mail = $mail.$virgule.$row_user_group['email'] ;
			
		}	
			$update = "UPDATE webcal_user SET cal_email = '".trim($mail,',')."' WHERE cal_login = '".$row2['unix_group_name']."'" ;
			db_query($update);
			
	} 
	$cal_res = db_query($cal_query2); 

}


//link

$query_hierarchy = "select p1.group_id as father_id,p1.unix_group_name as father_unix_name,p1.group_name as father_name,p2.group_id as son_id,p2.unix_group_name as son_unix_name,p2.group_name as son_name from groups as p1,groups as p2,plugin_projects_hierarchy where p1.group_id=plugin_projects_hierarchy.project_id and p2.group_id=plugin_projects_hierarchy.sub_project_id and plugin_projects_hierarchy.activated='t' AND plugin_projects_hierarchy.link_type='shar'";
$res_hierarchy = db_query($query_hierarchy);
if($res_hierarchy){
		while($row_hierarchy = db_fetch_array($res_hierarchy)) {
			$query_entry = "SELECT cal_id FROM webcal_entry_user WHERE cal_login = '".$row_hierarchy['son_unix_name']."' AND cal_status = 'A'" ;
			$res_entry = db_query($query_entry);
			if($res_entry){
				while($row_entry = db_fetch_array($res_entry)) {
				$insert_entry = "INSERT INTO webcal_entry_user (cal_id,cal_login,cal_status) VALUES ('".$row_entry['cal_id']."','".$row_hierarchy['father_unix_name']."','A')";	
				$res_insert_entry = db_query($insert_entry);
				}
			}
		}	
		
	}  

//admin
	
$query_flags = "SELECT value, user_id, group_id FROM user_group,role_setting WHERE role_setting.role_id = user_group.role_id AND role_setting.section_name = 'webcal'" ;				
				//$query_flags = "SELECT admin_flags FROM user_group WHERE user_id = '".$params[0]."' AND group_id = '".$params[1]."'";
				$res = db_query($query_flags);
				if($res){
						while( $row_flags = db_fetch_array($res)){
						
						
						
								//get user name :
								$query_nom_boss = "SELECT unix_group_name FROM groups WHERE group_id = '".$row_flags['group_id']."' ";
								$res_nom_boss = db_query($query_nom_boss);
								$row_nom_boss = db_fetch_array($res_nom_boss);
								
								
								$query_nom_user = "SELECT user_name FROM users WHERE user_id = '".$row_flags['user_id']."' ";
								$res_nom_user = db_query($query_nom_user);
								$row_nom_user = db_fetch_array($res_nom_user);
								
								//webcal admin flags
								$query_flags = "SELECT COUNT(*) FROM webcal_asst WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' AND cal_assistant = '".$row_nom_user['user_name']."'";
								$res_count = db_query($query_flags);
								$row_num = db_fetch_array($res_count);
								
								//select email
								$query_mail ="SELECT cal_email FROM webcal_user WHERE  cal_login = '".$row_nom_boss['unix_group_name']."'";			
								$res_mail = db_query($query_mail);
								$row_mail = db_fetch_array($res_mail);	
								
								if(($row_num[0] != 1 ) && ($row_flags['value'] == 1)){
								//recuperer le nom du user et du group
								$insert_ass =  "INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ('".$row_nom_boss['unix_group_name']."','".$row_nom_user['user_name']."')";	
								$res_insert  = db_query($insert_ass);
								
								//we add email of the new admin
								$mail = $row_mail['cal_email'].",".$row_nom_user['email'] ;
								$update = "UPDATE webcal_user SET cal_email = '".$mail."' WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
								db_query($update);
								}
								elseif($row_num[0] == 1 && ($row_flags['value'] != 1)){
								$del_ass = "DELETE FROM webcal_asst WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' AND cal_assistant = '".$row_nom_user['user_name']."'";
								$res_del = db_query($del_ass);
								
								//we del email of the old admin
								$mail = str_replace(",".$row_nom_user['email'],"",$row_mail['cal_email']) ;
								$update = "UPDATE webcal_user SET cal_email = '".$mail."' WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
								db_query($update);	
								}
						}
				}

				


?>
