<?php
  require_once('www/env.inc.php');
  require_once('pre.php');
  require_once('common/include/cron_utils.php');
 
  $groupname=0;
  $rolename=0;
  $dom = domxml_new_doc('1.0');
  $root = $dom->create_element('projects');
  $dom->append_child($root);
  
  db_begin();
  //Clear of the webcal_group_user table
  db_query("delete from webcal_group_user");
  db_commit();

  db_begin();
  
  $result=db_query("select g.group_id, g.unix_group_name, r.role_id, r.role_name, u.user_id, u.user_name, u.firstname, u.lastname
                    from (((groups g JOIN user_group ug ON g.group_id=ug.group_id)
                    JOIN users u ON u.user_id = ug.user_id) 
                    JOIN role r ON r.role_id = ug.role_id)
                    ORDER BY g.group_id,r.role_id,u.user_id");
 
 while ($trait = db_fetch_array($result)) {

    if(strcmp($trait['unix_group_name'],$groupname)!= 0){
      echo "\n".$trait['unix_group_name'];
      $groupname = $trait['unix_group_name'];
      $newGroup = $dom->create_element('project');
      $newGroup->set_attribute('name', $groupname);
      $root->append_child($newGroup);
      $rolename=0;
      
      $testvalue = "SELECT cal_name FROM webcal_group WHERE cal_name = '".$groupname."'";
      $res = db_query($testvalue);
      if (!$res || db_numrows($res) < 1){
        
        $testvalue = "SELECT cal_name FROM webcal_group";
        $res = db_query($testvalue);
        
        $cal = "INSERT INTO webcal_group (cal_group_id,cal_owner, cal_name, cal_last_update) 
                      VALUES ('".db_numrows($res)."',' ','" .$groupname."','".date("Ymd")."')";
        $resul = db_query($cal);
        db_commit();
        echo "\n ".db_numrows($res);
      }
      
    }
    if(strcmp($trait['role_name'],$rolename)!= 0){
      echo "\n    ".$trait['role_name'];
      $rolename = $trait['role_name'];
      $newRole = $dom->create_element('role');
      $newRole->set_attribute('name', $rolename);
      $newGroup->append_child($newRole);      
      
      $testvalue = "SELECT cal_name FROM webcal_group WHERE cal_name = '".$groupname.".".$rolename."'";
      $res = db_query($testvalue);
      if (!$res || db_numrows($res) < 1){
        $testvalue = "SELECT cal_name FROM webcal_group";
        $res = db_query($testvalue);
        
        $cal = "INSERT INTO webcal_group (cal_group_id,cal_owner, cal_name, cal_last_update) 
                      VALUES ('".db_numrows($res)."',' ','" .$groupname.".".$rolename."','".date("Ymd")."')";
        $resul = db_query($cal);
        db_commit();
        echo "\n ".db_numrows($res);
      }
      $testvalue = "SELECT cal_login FROM webcal_user WHERE cal_login = '".$groupname.".".$rolename."'";
      $res = db_query($testvalue);
      if (!$res || db_numrows($res) < 1){
        $cal = "INSERT INTO webcal_user (cal_login, cal_passwd, cal_email,
                          cal_firstname, cal_is_admin) 
                      VALUES ('" .$groupname.".".$rolename. "','cccc',' ','" .$groupname.".".$rolename. "','N')";
        $resul = db_query($cal);
        db_commit();
        echo "\n ".$resul;
      }                             
                                   
    }
    echo "\n        ".$trait['user_name'];
    $username = $trait['user_name'];
    $firstname = $trait['firstname'];
    $lastname = $trait['lastname'];
    $newUser = $dom->create_element('user');
    $newUser->set_attribute('firstname', $firstname);
    $newUser->set_attribute('lastname', $lastname);
    $newRole->append_child($newUser);
    $name = $dom->create_text_node($username);
    $newUser->append_child($name);
    
    
    
    $testvalue = "SELECT * FROM webcal_user WHERE cal_login = '".$groupname.".".$rolename."'";
    $res = db_query($testvalue);
    $cal_email = db_fetch_array($res);
    
    $testgroup = "SELECT * FROM webcal_group WHERE cal_name = '".$groupname."'";
    $resu = db_query($testgroup);
    $calgroup = db_fetch_array($resu);
    
    $testusergroup = "SELECT * FROM webcal_group_user WHERE cal_group_id = '".$calgroup['cal_group_id']."' and cal_login='".$username."'";
    $re = db_query($testusergroup);
    echo "\n ".$testusergroup;
     if (!$re || db_numrows($re) < 1){
        $cal = "INSERT INTO webcal_group_user (cal_group_id, cal_login) 
                      VALUES ('".$calgroup['cal_group_id']."','".$username."')";
        $resul = db_query($cal);
        echo "\n ".$cal;
				db_commit();
     }
    
    $testgroup = "SELECT * FROM webcal_group WHERE cal_name = '".$groupname.".".$rolename."'";
    $resu = db_query($testgroup);
    $calgroup = db_fetch_array($resu);
    
    $testusergroup = "SELECT * FROM webcal_group_user WHERE cal_group_id = '".$calgroup['cal_group_id']."' and cal_login='".$username."'";
    $re = db_query($testusergroup);
    echo "\n ".$testusergroup;
     if (!$re || db_numrows($re) < 1){
        $cal = "INSERT INTO webcal_group_user (cal_group_id, cal_login) 
                      VALUES ('".$calgroup['cal_group_id']."','".$username."')";
        $resul = db_query($cal);
        echo "\n ".$cal;
				db_commit();
     }    
    
        
    if ($res && db_numrows($res) == 1){
        $query = "SELECT email FROM users WHERE user_name = '".$username."'";
				$resu = db_query($query);
				$traite = db_fetch_array($resu);
				$cal_query = "UPDATE webcal_user SET cal_email = '".$cal_email['cal_email'].",".$traite['email']."' WHERE cal_login = '".$groupname.".".$rolename."'";
        $res_cal = db_query($cal_query);
				db_commit();
    }    
  }

  $dom->dump_file("users_groups.xml", false, true); 
  
  cron_entry(0,"Lists_utils_projects.php");
?>
