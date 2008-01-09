<?php


include_once 'includes/init.php';

//Debug
logs($log_file,"#######  year.php #######\n");
//Debug


if(isset($_GET['type_param'])){
  $GLOBALS['type_param']=$_GET['type_param'];
}else{
  $GLOBALS['type_param']='user';
}

if(isset($_GET['group_param'])){
  $GLOBALS['group_param']=$_GET['group_param'];
}

if($GLOBALS['type_param']=='group' && isset($_GET['group_param'])){

  $group_cal=$GLOBALS['group_param'];
  $role_user=user_project_role($login,$group_cal);
  
  $res=dbi_query("select unix_group_name from groups where group_id=".$GLOBALS['group_param']);
  $row = pg_fetch_array($res);
  $GLOBALS['group_name_param']=$row[0];
  
  //Debug
  logs($log_file,"trailer.php : role : ".$role_user."\n login : ".$login."\n group : ".$group_cal."\nuser : ".$user."\n");
  //debug
  
}

$can_add = Can_Add($login,$GLOBALS['type_param'],$GLOBALS['group_param']);

send_no_cache_header ();

if (($user != $login) && $is_nonuser_admin){
  load_user_layers ($user);
}else{
  load_user_layers ();
}

if ( empty ( $year ) )
  $year = date("Y");

$thisyear = $year;
if ( $year != date ( "Y") ){
  $thismonth = 1;
}

//set up global $today value for highlighting current date
set_today($date);

if ( $year > "1903" ){
  $prevYear = $year - 1;
}else{
  $prevYear=$year;
}

$nextYear= $year + 1;

if ( $allow_view_other != "Y" && ! $is_admin ){
  $user = "";
}

if($GLOBALS['type_param']=='group'){
  $user=$GLOBALS['group_name_param'];
}else{
  $user=$login;
}

$boldDays = false;
if ( ! empty ( $bold_days_in_year ) && $bold_days_in_year == 'Y' ) {
  /* Pre-Load the repeated events for quckier access */
  $repeated_events = read_repeated_events (
    ( ! empty ( $user ) && strlen ( $user ) ) ? $user : $login, $cat_id, $year . "0101" );

  /* Pre-load the non-repeating events for quicker access */
  $events = read_events ( ( ! empty ( $user ) && strlen ( $user ) )
    ? $user : $login, $year . "0101", $year . "1231", $cat_id );
  $boldDays = true;
}

// Include unapproved events?
$get_unapproved = ( $DISPLAY_UNAPPROVED == 'Y' );
if ( $user == "__public__" ){
  $get_unapproved = false;
}

print_header();

$href_prev = "year.php?year=".$prevYear;
$href_next = "year.php?year=".$nextYear;

if($GLOBALS['type_param'] == 'group'){
  $href = "&user=".$GLOBALS['group_name_param']."&type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];
  $info_type = "type_param=".$GLOBALS['type_param']."&group_param=".$GLOBALS['group_param'];
}else{
  $href = "&user=".$login."&type_param=".$GLOBALS['type_param'];
  $info_type = "type_param=".$GLOBALS['type_param'];
}

$href_prev .= $href;
$href_next .= $href;

$echo = "<div class=\"title\">
<a title=\"".translate("Previous")."\" class=\"prev\" href=\"".$href_prev."\"><img src=\"leftarrow.gif\" alt=\"".translate("Previous")."\" /></a>
<a title=\"".translate("Next")."\" class=\"next\" href=\"".$href_next."\"><img src=\"rightarrow.gif\" alt=\"".translate("Next")."\" /></a>
<span class=\"date\">".$thisyear."</span><br />
<span class=\"user\">";

if($GLOBALS['type_param'] == 'group'){
  $res = dbi_query("SELECT group_name from groups where unix_group_name = '".$GLOBALS['group_name_param']."'");
  $row = pg_fetch_array($res);
  $echo .= $row[0];
}else{
  $echo .= $login;
}
  
	/*if ( $single_user == "N" ) {
			$echo .= "<br />\n";
			if ( ! empty ( $user ) ) {
				user_load_variables ( $user, "user_" );
				$echo .= $user_fullname;
			} else {
				$echo .= $fullname;
			}
			if ( $is_assistant )
				$ .= echo "<br /><strong>-- " . translate("Assistant mode") . " --</strong>";
		}*/
		
$echo .="</span>
</div>
<br />
 
<div align=\"center\">
	<table class=\"main\">";


	
echo $echo;

echo "<tr><td>";
      display_small_month(1,$year,False,False,'','month.php?',$info_type);
echo "</td><td>";
			display_small_month(2,$year,False,False,'','month.php?',$info_type);
echo "</td><td>";
			display_small_month(3,$year,False,False,'','month.php?',$info_type);
echo "</td><td>";
			display_small_month(4,$year,False,False,'','month.php?',$info_type);
			
echo "</td></tr>
		  <tr><td>";
			display_small_month(5,$year,False,False,'','month.php?',$info_type);
echo "</td><td>";
			display_small_month(6,$year,False,False,'','month.php?',$info_type);
echo "</td><td>";
			display_small_month(7,$year,False,False,'','month.php?',$info_type);
echo "</td><td>";
			display_small_month(8,$year,False,False,'','month.php?',$info_type);
			
echo "</td></tr>
		  <tr><td>";
      display_small_month(9,$year,False,False,'','month.php?',$info_type);
echo "</td><td>";
      display_small_month(10,$year,False,False,'','month.php?',$info_type);
echo "</td><td>";
      display_small_month(11,$year,False,False,'','month.php?',$info_type);
echo "</td><td>";
      display_small_month(12,$year,False,False,'','month.php?',$info_type);

echo "</td></tr>
	</table>
</div>

<br />";

display_unapproved_events ( $login , $info_type);
?>
<br />
<a title="<?php 
	etranslate("Generate printer-friendly version")
?>" class="printer" href="year.php?<?php
	if ( $thisyear )
		echo "year=$thisyear&amp;";
	if ( $user != $login && ! empty ( $user ) )
		echo "user=$user&amp;";
?>friendly=1" target="cal_printer_friendly" onmouseover="window.status = '<?php etranslate("Generate printer-friendly version")?>'">[<?php etranslate("Printer Friendly")?>]</a>

<?php print_trailer(); ?>
</body>
</html>
