<?php


//create the config file for webcalendar
$fichier_conf = "<?php
install_password: 764b9c698569b4e77fd69db363fef414
db_type: postgresql
db_host: $sys_gfdbhost
db_database: $sys_gfdbname
db_login: $sys_gfdbuser
db_password: $sys_gfdbpasswd
db_persistent: true
single_user_login:
readonly: false
use_http_auth: false
single_user: false
user_inc: user.php
?>" ;

$handle = fopen(forge_get_config('plugins_path') . '/' . $pluginname . "/www/includes/settings.php", "w");
fwrite($handle,$fichier_conf);
fclose($handle);

//user
$res = db_query_params ('SELECT user_name,user_pw,email,user_id FROM users WHERE NOT user_name=$1 ',
			array ('None'));
echo db_error();
while ($row = db_fetch_array($res)) {
	//verify if admin
	$res_flags = db_query_params ('SELECT COUNT(*) FROM user_group WHERE user_id = $1 AND admin_flags = $2  AND group_id = 1',
				      array ($row['user_id'],
					     'A'));
	$row_flags = db_fetch_array($res_flags) ;
	$cal_res = db_query_params ('INSERT INTO webcal_user (cal_login, cal_passwd, cal_email,cal_firstname, cal_is_admin) VALUES ($1,$2,$3,$4,$5)',
				    array ($row['user_name'] ,
					   $row['user_pw'] ,
					   $row['email'] ,
					   $row['user_name'],
					   $row_flags[0] == 1 ? 'Y' : 'N'));
}

//group

$res2 = db_query_params ('SELECT  unix_group_name,groups.group_id,group_name,email FROM groups,users,user_group WHERE groups.group_id >5 AND groups.group_id = user_group.group_id AND user_group.user_id = users.user_id AND user_group.admin_flags = $1 ',
			array ('A'));
while ($row2 = db_fetch_array($res2)) {

	//get for admin of project
	$res_user_group = db_query_params ('SELECT user_group.user_id,user_name,email from user_group,users WHERE user_group.user_id = users.user_id AND group_id = $1 AND admin_flags = $2',
			array ($row2['group_id'],
				'A'));

	//get the email of the admin
		$res_mail = db_query_params ('SELECT cal_email FROM webcal_user WHERE  cal_login = $1',
			array ($row2['unix_group_name']));
	$row_mail = db_fetch_array($res_mail);
	$mail = $row_mail['cal_email'];

	if($res_user_group){
		while($row_user_group = db_fetch_array($res_user_group)) {
			$cal_res = db_query_params ('INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ($1,$2)',
			array ($row2['unix_group_name'],
				$row_user_group['user_name']));

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
		db_query_params ('UPDATE webcal_user SET cal_email = $1 WHERE cal_login = $2',
				 array (trim($mail,','),
					$row2['unix_group_name'])) ;
	}
	$cal_res = db_query_params ('INSERT INTO webcal_user (cal_login, cal_passwd, cal_firstname,cal_email) VALUES ($1,$2,$3,$4)',
			array ($row2['unix_group_name'] ,
				'qdkqshjddoshd',
				addslashes($row2['group_name']) ,
				$row2['email'] ));

}


//link

$res_hierarchy = db_query_params ('select p1.group_id as father_id,p1.unix_group_name as father_unix_name,p1.group_name as father_name,p2.group_id as son_id,p2.unix_group_name as son_unix_name,p2.group_name as son_name from groups as p1,groups as p2,plugin_projects_hierarchy where p1.group_id=plugin_projects_hierarchy.project_id and p2.group_id=plugin_projects_hierarchy.sub_project_id and plugin_projects_hierarchy.activated=$1 AND plugin_projects_hierarchy.link_type=$2',
			array ('t',
				'shar'));
if($res_hierarchy){
		while($row_hierarchy = db_fetch_array($res_hierarchy)) {
			$res_entry = db_query_params ('SELECT cal_id FROM webcal_entry_user WHERE cal_login = $1 AND cal_status = $2',
			array ($row_hierarchy['son_unix_name'],
				'A'));
			if($res_entry){
				while($row_entry = db_fetch_array($res_entry)) {
					$res_insert_entry = db_query_params ('INSERT INTO webcal_entry_user (cal_id,cal_login,cal_status) VALUES ($1,$2,$3)',
			array ($row_entry['cal_id'],
				$row_hierarchy['father_unix_name'],
				'A'));
				}
			}
		}

	}

//admin

				$res = db_query_params ('SELECT value, user_id, group_id FROM user_group,role_setting WHERE role_setting.role_id = user_group.role_id AND role_setting.section_name = $1',
			array ('webcal'));
				if($res){
						while( $row_flags = db_fetch_array($res)){



								//get user name :
							$res_nom_boss = db_query_params ('SELECT unix_group_name FROM groups WHERE group_id = $1 ',
			array ($row_flags['group_id']));
								$row_nom_boss = db_fetch_array($res_nom_boss);


																$res_nom_user = db_query_params ('SELECT user_name FROM users WHERE user_id = $1 ',
			array ($row_flags['user_id']));
								$row_nom_user = db_fetch_array($res_nom_user);

								//webcal admin flags
								$res_count = db_query_params ('SELECT COUNT(*) FROM webcal_asst WHERE cal_boss = $1 AND cal_assistant = $2',
			array ($row_nom_boss['unix_group_name'],
				$row_nom_user['user_name']));
								$row_num = db_fetch_array($res_count);

								//select email
								$res_mail = db_query_params ('SELECT cal_email FROM webcal_user WHERE  cal_login = $1',
			array ($row_nom_boss['unix_group_name']));
								$row_mail = db_fetch_array($res_mail);

								if(($row_num[0] != 1 ) && ($row_flags['value'] == 1)){
								//recuperer le nom du user et du group
									$res_insert = db_query_params ('INSERT INTO webcal_asst (cal_boss, cal_assistant) VALUES ($1,$2)',
			array ($row_nom_boss['unix_group_name'],
				$row_nom_user['user_name']));

								//we add email of the new admin
								$mail = $row_mail['cal_email'].",".$row_nom_user['email'] ;
db_query_params ('UPDATE webcal_user SET cal_email = $1 WHERE cal_login = $2',
			array ($mail,
				$row_nom_boss['unix_group_name']));
								}
								elseif($row_num[0] == 1 && ($row_flags['value'] != 1)){
									$res_del = db_query_params ('DELETE FROM webcal_asst WHERE cal_boss = $1 AND cal_assistant = $2',
			array ($row_nom_boss['unix_group_name'],
				$row_nom_user['user_name']));

								//we del email of the old admin
								$mail = str_replace(",".$row_nom_user['email'],"",$row_mail['cal_email']) ;
db_query_params ('UPDATE webcal_user SET cal_email = $1 WHERE cal_login = $2',
			array ($mail,
				$row_nom_boss['unix_group_name']));
								}
						}
				}




?>
