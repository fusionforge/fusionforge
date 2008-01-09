<?php
/*
	$Id: adminhome.php,v 1.15 2005/01/19 13:54:13 cknudsen Exp $

	Page Description:
		Serves as the home page for administrative functions.
	Input Parameters:
		None
	Security:
		Users will see different options available on this page.
 */

include_once 'includes/init.php';
include_once 'includes/user.php';

//Debug
logs($log_file,"#######  admin_home.php #######\n");
//Debug

//Debug
logs($log_file,"admin_home.php _ login : ".$login."\n");
//Debug

if(isset($_GET['type_param'])){
  $type_cal=$_GET['type_param'];
}else{
  $type_cal='user';
}

if(isset($_GET['group_param'])){
  $group_cal=$_GET['group_param'];
}

//Debug
logs($log_file,"admin_home.php : group : ".$group_cal."\n");
//Debug

$COLUMNS = 3;

$style = "<style type=\"text/css\">
table.admin {
	padding: 5px;
	border: 1px solid #000000;
";
if ( function_exists ("imagepng") &&
  ( empty ($GLOBALS['enable_gradients']) || $GLOBALS['enable_gradients'] == 'Y' ) ) {
	$style .= "	background-image: url(\"gradient.php?height=300&base=ccc&percent=10\");\n";
} else {
	$style .= "	background-color: #CCCCCC;\n";
}
$style .= "
}
table.admin td {
	padding: 20px;
	text-align: center;
}
.admin td a {
	padding: 10px;
	width: 200px;
	text-align: center;
	background-color: #CCCCCC;
	border-top: 1px solid #EEEEEE;
	border-left: 1px solid #EEEEEE;
	border-bottom: 1px solid #777777;
	border-right: 1px solid #777777;
}
.admin td a:hover {
	padding: 10px;
	width: 200px;
	text-align: center;
	background-color: #AAAAAA;
	border-top: 1px solid #777777;
	border-left: 1px solid #777777;
	border-bottom: 1px solid #EEEEEE;
	border-right: 1px solid #EEEEEE;
}
</style>
";
print_header('', $style);

$names = array ();
$links = array ();

if ($is_admin) {
  if ($type_cal=='group'){
  
    if(user_project_role($login,$group_cal) == '3'){  
	    $names[] = translate("System Settings");
	    $links[] = "admin.php?type_param=".$type_cal."&group_param=".$group_cal;
	  }
	    
	}else{
	  
	    $names[] = translate("System Settings");
	    $links[] = "admin.php?type_param=".$type_cal;
        
  }
}


if ($type_cal=='group'){

  $names[] = translate("Preferences");
  $links[] = "pref.php?type_param=".$type_cal."&group_param=".$group_cal;
  
}else {

  $names[] = translate("Preferences");
  $links[] = "pref.php?type_param=".$type_cal;
  
}
//remove user admin
/*
if ( $is_admin ) {
	$names[] = translate("Users");
	$links[] = "users.php";
} else {
	$names[] = translate("Account");
	$links[] = "users.php";
}
*/

/*if ( $single_user != 'Y' ) {
	$names[] = translate("Assistants");
	$links[] = "assistant_edit.php";
}*/

if ( $categories_enabled == 'Y' ) {
  if ($type_cal=='group'){
  
    if(user_project_role($login,$group_cal) >= '1'){
	    $names[] = translate("Categories");
	    $links[] = "category.php?type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];
	  }
	    
	}else{
	  $names[] = translate("Categories");
	  $links[] = "category.php?type_param=".$GLOBALS['type_param'];  
  }
}

if ($type_cal=='group'){ 
  if(user_project_role($login,$group_cal) >= '1'){
    $names[] = translate("Views");
    $links[] = "views.php?type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];
  }
}else{
  $names[] = translate("Views");
  $links[] = "views.php?type_param=".$GLOBALS['type_param'];
}

if ($type_cal=='group' && user_project_role($login,$group_cal) >= '1' ){
  $names[] = translate("Layers");
  $links[] = "layers.php?type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];
}else{
  $names[] = translate("Layers");
  $links[] = "layers.php?type_param=".$GLOBALS['type_param']; 
}

if ( $reports_enabled == 'Y' ) {
  if ($type_cal=='group' && user_project_role($login,$group_cal) >= '1' ){
	  $names[] = translate("Reports");
	  $links[] = "report.php?type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];
	}else{
	  $names[] = translate("Reports");
	  $links[] = "report.php?type_param=".$GLOBALS['type_param'];   
  }
}

if ( $is_admin ) {
  if ($type_cal=='group' && user_project_role($login,$group_cal) >= '1' ){
  	$names[] = translate("Delete Events");
  	$links[] = "purge.php?type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];

  	$names[] = translate("Activity Log");
  	$links[] = "activity_log.php?type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];
	}else{
  	$names[] = translate("Delete Events");
  	$links[] = "purge.php?type_param=".$GLOBALS['type_param']; 

  	$names[] = translate("Activity Log");
  	$links[] = "activity_log.php?type_param=".$GLOBALS['type_param']; 
  }
}

if ( $is_admin && ! empty ($public_access) && $public_access == 'Y' ) {
  if ($type_cal=='group' && user_project_role($login,$group_cal) >= '1' ){
	  $names[] = translate("Public Preferences");
	  $links[] = "pref.php?public=1&type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];
	}else{
	  $names[] = translate("Public Preferences");
	  $links[] = "pref.php?public=1&type_param=".$GLOBALS['type_param'];  
  }
}

if ( $is_admin && ! empty ( $public_access ) && $public_access == 'Y' &&
	$public_access_can_add == 'Y' && $public_access_add_needs_approval == 'Y' ) {
	
	if ($type_cal=='group' && user_project_role($login,$group_cal) >= '1' ){
	  $names[] = translate("Unapproved Public Events");
	  $links[] = "list_unapproved.php?user=__public__&type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];
	}else{
	  $names[] = translate("Unapproved Public Events");
	  $links[] = "list_unapproved.php?user=__public__&type_param=".$GLOBALS['type_param'];   
  }
}
?>

<h2><?php etranslate("Administrative Tools")?></h2>

<table class="admin">
<?php
	for ( $i = 0; $i < count ($names); $i++ ) {
		if ( $i % $COLUMNS == 0 )
			echo "<tr>\n";
			echo "<td>";
		if ( ! empty ($links[$i]) )
			echo "<a href=\"$links[$i]\">";
		echo $names[$i];
		if ( ! empty ($links[$i]) )
			echo "</a>";
		echo "</td>\n";
		if ($i % $COLUMNS == $COLUMNS - 1)
			echo "</tr>\n";
	}
	if ( $i % $COLUMNS != 0 )
		echo "</tr>\n";
?>
</table>

<?php print_trailer(); ?>
</body>
</html>
