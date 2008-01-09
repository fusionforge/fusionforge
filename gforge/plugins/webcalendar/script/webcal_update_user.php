<?php
require_once('www/env.inc.php');
require_once('pre.php');
require_once('common/include/cron_utils.php');

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
$query = "SELECT user_name,user_pw,email,user_id 
          FROM users 
          WHERE NOT user_name='None' 
                AND user_id > 99";
$res = db_query($query);
echo db_error();
while ($row = db_fetch_array($res)) {

  //Get the admin_flag
  $res_flags = db_query("SELECT COUNT(*) 
                         FROM user_group 
                         WHERE user_id = '".$row['user_id']."' 
                           AND admin_flags = 'A'  
                           AND group_id = '1'");
  $row_flags = db_fetch_array($res_flags);
  $admin_flag = ($row_flags[0] == 1 ? 'Y' : 'N');

  //Control if the gforge user exists in the webcalendar data base
  $res_login = db_query("SELECT cal_login 
                         FROM webcal_user
                         WHERE cal_login = '".$row['user_name']."'");
  echo "login : ".$row['user_name'];
          
  //if the user doesn't exists, insert his on the webcalendar database
  if(pg_num_rows($res_login) < 1){
  
    echo " not exist and must be insert\n";
    $cal_query = "INSERT INTO webcal_user 
                         (cal_login, cal_passwd, cal_email,cal_firstname, cal_is_admin) 
                  VALUES ('" . $row['user_name'] . "','" . $row['user_pw'] . "','" . $row['email'] . "','" . $row['user_name'] . "','".$admin_flag."')";
    echo "INSERTION : ".$cal_query;
    $cal_res = db_query($cal_query);
  
  //Control if any fields have changed
  }else{

    echo " exist";
    $res_update = db_query("SELECT cal_login 
                          FROM webcal_user 
                          WHERE cal_login = '".$row['user_name']."'
                            and cal_passwd = '".$row['user_pw']."'
                            and cal_email = '".$row['email']."'
                            and cal_firstname = '".$row['user_name']."'
                            and cal_is_admin = '".$admin_flag."'");  
    
    echo "Number of result : ".pg_num_rows($res_update)."\n";
    
    //if one or more field have changed, then update the user
    if(pg_num_rows($res_update) < 1){
      echo " and must be updated";
      $cal_query = "UPDATE webcal_user 
                    SET cal_login = '".$row['user_name']."',
                        cal_passwd = '".$row['user_pw']."',
                        cal_email = '".$row['email']."',
                        cal_firstname = '".$row['user_name']."',
                        cal_is_admin = '".$admin_flag."'
                    WHERE cal_login='".$row['user_name']."'";
      echo "\nUPDATE : ".$cal_query;
      echo "\nTEST : "."SELECT cal_login 
                          FROM webcal_user 
                          WHERE cal_login = '".$row['user_name']."'
                            and cal_passwd = '".$row['user_pw']."'
                            and cal_email = '".$row['email']."'
                            and cal_firstname = '".$row['user_name']."'
                            and cal_is_admin = '".$admin_flag."'";
      $cal_res = db_query($cal_query);
    }
  }
  echo "\n";
}

//group

$query2 = "SELECT  unix_group_name,groups.group_id,group_name,email 
           FROM groups,users,user_group 
           WHERE groups.group_id >5 
             AND groups.group_id = user_group.group_id 
             AND user_group.user_id = users.user_id 
             AND user_group.admin_flags = 'A' ";
$res2 = db_query($query2);
echo db_error();
$IDS='';
while ($row2 = db_fetch_array($res2)) {
  
  if(!strpos($IDS, $row2['group_id'])){
    
    //memorize the Project ID
    $IDS .= ','.$row2['group_id'];
    
    echo "\nQUERY : "."SELECT u.email 
                           FROM users u, groups g, user_group ug
                           WHERE g.group_id = ".$row2['group_id']."
                             AND ug.user_id = u.user_id
                           LIMIT 1\n";
    //get the e_mail of the project admin
    $res_email = db_query("SELECT u.email 
                           FROM users u, groups g, user_group ug
                           WHERE g.group_id = ".$row2['group_id']."
                             AND ug.user_id = u.user_id
                           LIMIT 1");
    $row_email = pg_fetch_row($res_email);
    $email = $row_email[0];
  
    //Control if the Project exist on the webcalendar data base
    $res_project = db_query("SELECT cal_login
                             FROM webcal_user
                             WHERE cal_login = '".$row2['unix_group_name']."'");
                             
    echo $row2['unix_group_name'];
    
    //If the project doesn't exist, insert it in the database
    if(pg_num_rows($res_project) < 1){
    
      echo " doesn't exist and must be inserted";
      $cal_query2 = "INSERT 
                     INTO webcal_user 
                     (cal_login, cal_passwd, cal_firstname,cal_email) 
                     VALUES 
                     ('" . $row2['unix_group_name'] . "','qdkqshjddoshd','" . addslashes($row2['group_name']) . "','" . $email . "')";
    
    //Else test if any field have change for the project
    }else{
       echo " exist";
      $res_update = db_query("SELECT cal_login 
                     FROM webcal_user
                     WHERE cal_login = '".$row2['unix_group_name']."'
                       and cal_passwd = 'qdkqshjddoshd'
                       and cal_firstname = '".addslashes($row2['group_name'])."'
                       and cal_email = '".$email."'");
                       
      //If any field have changed, I update the project                 
      if(pg_num_rows($res_update)<1){
        echo " and must be updated";
        $cal_query2 = "UPDATE webcal_user
                       SET cal_login = '".$row2['unix_group_name']."',
                           cal_passwd = 'qdkqshjddoshd',
                           cal_firstname = '".addslashes($row2['group_name'])."',
                           cal_email = '".$email."'
                       WHERE cal_login = '".$row2['unix_group_name']."'";
      }      
    }
    
    echo "\n";
	
                         
	  //print $query_user_group ;
	  $res_user_group = db_query("SELECT user_group.user_id,user_name,email 
                                FROM user_group,users 
                                WHERE user_group.user_id = users.user_id 
                                  AND group_id = '".$row2['group_id']."' 
                                  AND admin_flags = 'A'");
	
  	//get the email of the admin
  	$query_mail ="SELECT cal_email 
                  FROM webcal_user 
                  WHERE  cal_login = '".$row2['unix_group_name']."'";			
	  $res_mail = db_query($query_mail);
	  $row_mail = db_fetch_array($res_mail);	
	  $mail = $row_mail['cal_email'];
	
	  if(pg_num_rows($res_user_group) > 0){
		
	    while($row_user_group = db_fetch_array($res_user_group)) {
	      	 
	      //Test if the assistant exists for this project in the webcalendar data base
	      $res_assistant = db_query("SELECT * 
                                   FROM webcal_asst 
                                   WHERE cal_boss='".$row2['unix_group_name']."'
                                     and cal_assistant='".$row_user_group['user_name']."'");
                                     
        echo $row2['unix_group_name']." assistant ".$row_user_group['user_name'];
    
        //If the user doesn't exist, Insert it in the database                        
        if(pg_num_rows($res_assistant) < 1){
        
          echo " doesn't exist and must be inserted";
      
          $insert_ass = "INSERT INTO webcal_asst 
                                (cal_boss, cal_assistant) 
                         VALUES ('".$row2['unix_group_name']."','".$row_user_group['user_name']."')"; 
		  	  $cal_res = db_query($insert_ass);
			
        }
	
		    //Suppress the mail in the list
		    $mail = str_replace($row_user_group['email'],"",$mail);
		    $mail = str_replace(",".$row_user_group['email'],"",$mail);
			
	    	if($mail == ""){
		      $virgule = "";	
		    }else {
		    	$virgule = ",";	
		    }
		
		    //Add the mail in the list
    	  $mail = $mail.$virgule.$row_user_group['email'] ;
			
			  echo "\n";
		  }	
		
      //update the list of email for the project	
      $update = "UPDATE webcal_user 
                 SET cal_email = '".trim($mail,',')."' 
                 WHERE cal_login = '".$row2['unix_group_name']."'" ;
		  db_query($update);
			
	  }
  
    //If the project doesn't exist or must be updated in the webcalendar database I execute the query
    if($cal_query2 != ""){
	    $cal_res = db_query($cal_query2); 
	  }

  }
}

//link

/*$query_hierarchy = "SELECT p1.group_id as father_id,
                           p1.unix_group_name as father_unix_name,
                           p1.group_name as father_name,
                           p2.group_id as son_id,
                           p2.unix_group_name as son_unix_name,
                           p2.group_name as son_name 
                    FROM groups as p1,
                         groups as p2,
                         plugin_projects_hierarchy 
                    WHERE p1.group_id = plugin_projects_hierarchy.project_id 
                      AND p2.group_id = plugin_projects_hierarchy.sub_project_id
                      AND plugin_projects_hierarchy.activated='t' 
                      AND plugin_projects_hierarchy.link_type='shar'";
$res_hierarchy = db_query($query_hierarchy);

if(pg_num_rows($res_hierarchy) > 0){

  while($row_hierarchy = db_fetch_array($res_hierarchy)) {
		
	  $query_entry = "SELECT cal_id 
                    FROM webcal_entry_user 
                    WHERE cal_login = '".$row_hierarchy['son_unix_name']."' 
                      AND cal_status = 'A'" ;                  
		$res_entry = db_query($query_entry);
			
		if($res_entry){
		
			while($row_entry = db_fetch_array($res_entry)) {
				
			  $insert_entry = "INSERT INTO webcal_entry_user 
                                (cal_id, cal_login, cal_status) 
                         VALUES ('".$row_entry['cal_id']."','".$row_hierarchy['father_unix_name']."','A')";	
			  $res_insert_entry = db_query($insert_entry);
				
			}
			
		}
		
	}	
		
}  */

//admin
	
$query_flags = "SELECT value, user_id, group_id 
                FROM user_group,role_setting 
                WHERE role_setting.role_id = user_group.role_id 
                  AND role_setting.section_name = 'webcal'" ;
                  				
//$query_flags = "SELECT admin_flags FROM user_group WHERE user_id = '".$params[0]."' AND group_id = '".$params[1]."'";
$res = db_query($query_flags);
		
if(pg_num_rows($res) > 0){

  while( $row_flags = db_fetch_array($res)){
				
	  //get project name :
		$query_nom_boss = "SELECT unix_group_name 
                       FROM groups 
                       WHERE group_id = '".$row_flags['group_id']."' ";
		$res_nom_boss = db_query($query_nom_boss);
		$row_nom_boss = db_fetch_array($res_nom_boss);
							
		//get user name
		$query_nom_user = "SELECT user_name 
                       FROM users 
                       WHERE user_id = '".$row_flags['user_id']."' ";
		$res_nom_user = db_query($query_nom_user);
		$row_nom_user = db_fetch_array($res_nom_user);
							
		//webcal admin flags
		$query_flags = "SELECT COUNT(*) 
                    FROM webcal_asst 
                    WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' 
                      AND cal_assistant = '".$row_nom_user['user_name']."'";
		$res_count = db_query($query_flags);
		$row_num = db_fetch_array($res_count);
							
		//select email
		$query_mail ="SELECT cal_email 
                  FROM webcal_user 
                  WHERE  cal_login = '".$row_nom_boss['unix_group_name']."'";			
		$res_mail = db_query($query_mail);
		$row_mail = db_fetch_array($res_mail);
    
    echo $row_nom_boss['unix_group_name']." assistant ".$row_nom_user['user_name'];
		
		if(($row_num[0] != 1 ) && ($row_flags['value'] == 2)){
		
		  echo " must be inserted\n";
		
		  //recuperer le nom du user et du group
		  $insert_ass =  "INSERT INTO webcal_asst 
                             (cal_boss, cal_assistant) 
                      VALUES ('".$row_nom_boss['unix_group_name']."','".$row_nom_user['user_name']."')";	
		  $res_insert  = db_query($insert_ass);
		
		  //we add email of the new admin
		  $mail = $row_mail['cal_email'].",".$row_nom_user['email'] ;
		  $update = "UPDATE webcal_user 
                 SET cal_email = '".$mail."' 
                 WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
		  db_query($update);
		  
		}else if($row_num[0] == 1 && ($row_flags['value'] != 2)){
		
		  echo " must be deleted\n";
		  $del_ass = "DELETE 
                  FROM webcal_asst 
                  WHERE cal_boss = '".$row_nom_boss['unix_group_name']."' 
                    AND cal_assistant = '".$row_nom_user['user_name']."'";
		  $res_del = db_query($del_ass);
		
		  //we del email of the old admin
		  $mail = str_replace(",".$row_nom_user['email'],"",$row_mail['cal_email']) ;
		  $update = "UPDATE webcal_user 
                 SET cal_email = '".$mail."' 
                 WHERE cal_login = '".$row_nom_boss['unix_group_name']."'" ;
		  db_query($update);
       	
		}
		echo "\n";
	}
}
cron_entry(0,"webcal_update_user.php");
?>
