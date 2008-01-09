<?php
include_once 'includes/init.php';

//Debug
logs($log_file,"#######  views.php  #######\n");
//Debug

//Debug
logs($log_file,"login : ".$login."\n");
//Debug

if(isset($_GET['type_param'])){
  $GLOBALS['type_param']=$_GET['type_param'];
}else{
  $GLOBALS['type_param']='user';
}

if(isset($_GET['group_param'])){
  $GLOBALS['group_param']=$_GET['group_param'];
}

//Debug
logs($log_file, 'type : '.$GLOBALS['type_param']);
//Debug

if ( ! $is_admin )
  $user = $login;

print_header();
?>

<h2><?php etranslate("Views")?></h2>
<a title="<?php etranslate("Admin") ?>" class="nav" href="adminhome.php<?php echo ($GLOBALS['type_param']=='group'? "?type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'] : "?type_param=".$GLOBALS['type_param']) ?>">&laquo;&nbsp;<?php etranslate("Admin") ?></a><br /><br />
<ul>
<?php

$info_type = "type_param=".$GLOBALS['type_param'];
if($GLOBALS['type_param']=='group'){
  $info_type .= "&group_param=".$GLOBALS['group_param'];
}

$global_found = false;
for ( $i = 0; $i < count ( $views ); $i++ ) {
  if ( $views[$i]['cal_is_global'] != 'Y' || $is_admin ) {
    echo "<li><a href=\"views_edit.php?id=" . $views[$i]["cal_view_id"] . "&".$info_type.
      "\">" . $views[$i]["cal_name"] . "</a>";
    if ( $views[$i]['cal_is_global'] == 'Y' ) {
      echo "<sup>*</sup>";
      $global_found = true;
    }
    echo "</li>";
  }
}
?>
</ul>
<?php
  if ( $global_found )
    echo "<br />\n<sup>*</sup> " . translate ( "Global" );
?>
<br /><br />
<?php
  echo "<a href=\"views_edit.php?".$info_type."\">" . translate("Add New View") .
    "</a><br />\n";
?>

<?php print_trailer(); ?>
</body>
</html>
