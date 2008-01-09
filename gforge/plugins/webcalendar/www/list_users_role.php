<?php

  require_once('www/env.inc.php');
  require_once('pre.php');
  
  header('Content-Type: text/xml'); 
  echo "<?xml version=\"1.0\"?>\n";
  
  echo "<users>\n";
  
  $groupname=$_GET["groupe"];
  $rolename=$_GET["role"];
  
  $dom = domxml_open_file("../bin/users_groups.xml");
  $params = $dom->get_elements_by_tagname('project');
  foreach ($params as $param) 
  {
    if(strcmp($param->get_attribute('name'),$groupname)==0)
    {
      $roles=$param->get_elements_by_tagname('role');
      foreach ($roles as $role) 
      {
        if(strcmp($role->get_attribute('name'),$rolename)==0)
        {
          $users=$role->get_elements_by_tagname('user');
          foreach ($users as $user)
          {
            echo "<user firstname='".$user->get_attribute("firstname")."' lastname='".$user->get_attribute("lastname")."'>".$user->get_content()."</user>\n";
          }
        }
      }
    }
  }
  echo "</users>\n";
?>

