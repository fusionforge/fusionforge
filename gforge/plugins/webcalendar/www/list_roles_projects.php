<?php
  require_once('www/env.inc.php');
  require_once('pre.php');
  header('Content-Type: text/xml'); 
  echo "<?xml version=\"1.0\"?>\n";
  echo "<roles>\n";
  $groupname=$_GET["groupe"];
  $rolename=0;
  $dom = domxml_open_file("../bin/users_groups.xml");
  $params = $dom->get_elements_by_tagname('project');
  foreach ($params as $param) {
        if(strcmp($param->get_attribute('name'),$groupname)==0){
          $roles=$param->get_elements_by_tagname('role');
          foreach ($roles as $role) {
            echo "<role>".$role->get_attribute('name')."</role>\n";
          }
        }
  }
  echo "</roles>\n";
?>
