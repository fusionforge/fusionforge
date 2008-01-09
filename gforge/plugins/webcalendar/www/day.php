<?php
include_once 'includes/init.php';

global $get_unapproved;
$get_unapproved = $GLOBALS['DISPLAY_UNAPPROVED']=='Y';

//Debug
logs($log_file,"#######  day.php #######\n");
//Debug

if(isset($_GET['type_param'])){
  $GLOBALS['type_param']=$_GET['type_param'];
}else{
  $GLOBALS['type_param']='user';
}

//Debug
logs($log_file,"type : ".$GLOBALS['type_param']."\n");
//Debug

global $user;

if(isset($_GET['group_param'])){
  $GLOBALS['group_param']=$_GET['group_param'];
  
  $res=dbi_query("select unix_group_name from groups where group_id=".$GLOBALS['group_param']);
  $row = pg_fetch_array($res);
  $GLOBALS['group_name_param']=$row[0];
  $user = $GLOBALS['group_name_param'];
}else{
  $user = $login;
}


if($GLOBALS['type_param']=='group' && isset($_GET['group_param'])){

  $group_cal=$GLOBALS['group_param'];
  $role_user=user_project_role($login,$group_cal);
  
  //Debug
  logs($log_file,"trailer.php : role : ".$role_user."\n login : ".$login."\n group : ".$group_cal."\nuser : ".$user."\n");
  //Debug
}

/*$can_add=false;
if($GLOBALS['type_param'] == 'group' && $role_user >=2 ){
  $can_add = true;
  //debug
  logs($log_file,"edit_entry_handler.php : can_modify \n");
  //debug
}else{
  if($GLOBALS['type_param'] == 'user'){
      $can_add = true;
  }
}*/

//Determine the info type
if($GLOBALS['type_param']=='group'){
  $info_type="type_param=group&group_param=".$GLOBALS['group_param']."&";
}else{
  $info_type="type_param=user&";
}

//Determine if the user can add event on this calendar
$can_add = Can_Add($login,$GLOBALS['type_param'],$GLOBALS['group_param']);

//debug
if($can_add){
  logs($log_file,"CAN ADD\n");
}else{
  logs($log_file,"!!!!!!!  CAN'T ADD  !!!!!!!\n"); 
}
//Debug

//Debug
logs($log_file,"day.php:\n");
logs($log_file,"GET:".print_r($_GET,true)." \n");
//Debug
    
//Debug
logs($log_file,"day.php : type : ".$GLOBALS['type_param']."\n");
//Debug


if (($user != $login) && $is_nonuser_admin)
  load_user_layers ($user);
else
  load_user_layers ();

load_user_categories ();

$wday = strftime ( "%w", mktime ( 3, 0, 0, $thismonth, $thisday, $thisyear ) );

$now = mktime ( 3, 0, 0, $thismonth, $thisday, $thisyear );
$nowYmd = date ( "Ymd", $now );

$next = mktime ( 3, 0, 0, $thismonth, $thisday + 1, $thisyear );
$nextYmd = date ( "Ymd", $next );
$nextyear = date ( "Y", $next );
$nextmonth = date ( "m", $next );
$nextday = date ( "d", $next );

$prev = mktime ( 3, 0, 0, $thismonth, $thisday - 1, $thisyear );
$prevYmd = date ( "Ymd", $prev );
$prevyear = date ( "Y", $prev );
$prevmonth = date ( "m", $prev );
$prevday = date ( "d", $prev );

if ( ! empty ( $bold_days_in_year ) && $bold_days_in_year == 'Y' ) {
 $boldDays = true;
} else {
 $boldDays = false;
}

$startdate = sprintf ( "%04d%02d01", $thisyear, $thismonth );
$enddate = sprintf ( "%04d%02d31", $thisyear, $thismonth );

if($GLOBALS['type_param'] == 'group'){
  $u_url .= "type_param=group&group_param=".$GLOBALS['group_param']."&";
}else{
  $u_url .= "type_param=user&";
}

//Debug
logs($log_file,"type : ".$GLOBALS['type_param']." u_url : ".$u_url."\n");
//Debug

$HeadX = '';
if ( $auto_refresh == "Y" && ! empty ( $auto_refresh_time ) ) {
  $refresh = $auto_refresh_time * 60; // convert to seconds
  $HeadX = "<meta http-equiv=\"refresh\" content=\"$refresh; url=day.php?$u_url" .
    "date=$nowYmd$caturl" . ( ! empty ( $friendly ) ? "&amp;friendly=1" : "") . "\" />\n";
}
$INC = array('js/popups.php');
print_header($INC,$HeadX);
?>

<?php
//Debug
logs($log_file, "UNAPPROVED : ".$GLOBALS['DISPLAY_UNAPPROVED']."\n");
logs($log_file,"avant repeat_event \n");
//Debug

/* Pre-Load the repeated events for quckier access */
$repeated_events = read_repeated_events ( empty ( $user ) ? $login : $user,
  $cat_id, $startdate  );

//Debug
logs($log_file,"après repeat_event".print_r($repeated_events,true)."\n avant no repeat_event\n");
//Debug

/* Pre-load the non-repeating events for quicker access */
$events = read_events ( empty ( $user ) ? $login : $user, $startdate, $enddate,
  $cat_id  );
  
//Debug
logs($log_file,"après no repeat_event".print_r($events,true)."\n");
//Debug
?>

<table>
<tr><td style="vertical-align:top; width:82%;">
<div style="border-width:0px;">
<a title="<?php etranslate("Next"); ?>" class="next" href="day.php?<?php echo $u_url;?>date=<?php echo $nextYmd . $caturl;?>"><img src="rightarrow.gif" alt="<?php etranslate("Next"); ?>" /></a>
<a title="<?php etranslate("Previous"); ?>" class="prev" href="day.php?<?php echo $u_url;?>date=<?php echo $prevYmd . $caturl;?>"><img src="leftarrow.gif" alt="<?php etranslate("Previous"); ?>" /></a>
<div class="title">
<span class="date"><?php
  echo date_to_str ( $nowYmd );
?></span>
<span class="user"><?php
  // display current calendar's user (if not in single user)
  /*if ( $single_user == "N" ) {
    echo "<br />";
    echo $user_fullname;
  }
  if ( $is_nonuser_admin )
    echo "<br />-- " . translate("Admin mode") . " --";
  if ( $is_assistant )
    echo "<br />-- " . translate("Assistant mode") . " --";*/
echo "<br />";
if($GLOBALS['type_param'] == 'group'){
  $res = dbi_query("SELECT group_name from groups where unix_group_name = '".$GLOBALS['group_name_param']."'");
  $row = pg_fetch_array($res);
  echo $row[0];
}else{
  echo $login;
}
?></span>
<?php
  if ( $categories_enabled == "Y" && (!$user || ($user == $login || $is_assistant ))) {
    echo "<br />\n<br />\n";
    print_category_menu( 'day', sprintf ( "%04d%02d%02d",$thisyear, $thismonth, $thisday ), $cat_id );
  }
?>
</div>
</div>
</td>
<td style="vertical-align:top;" rowspan="2">
<!-- START MINICAL -->
<div class="minicalcontainer">
<?php display_small_month ( $thismonth, $thisyear, true, false, '', "month.php?", $info_type); ?>
</div>
</td></tr><tr><td>
<table class="glance" cellspacing="0" cellpadding="0">
<?php
if ( empty ( $TIME_SLOTS ) )
  $TIME_SLOTS = 24;

print_day_at_a_glance ( date ( "Ymd", $now ),
  empty ( $user ) ? $login : $user, $can_add );
?>
</table>
</td>
</tr></table>
<br />
<?php
 if ( ! empty ( $eventinfo ) ) echo $eventinfo;

  display_unapproved_events ( ( $is_assistant || $is_nonuser_admin ? $user : $login ) );
?>
<br />
<a title="<?php etranslate("Generate printer-friendly version")?>" class="printer" href="day.php?<?php
  echo $u_url;
  if ( $thisyear ) {
    echo "year=$thisyear&amp;month=$thismonth&amp;day=$thisday&amp;";
  }
  if ( ! empty ( $cat_id ) ) echo "cat_id=$cat_id&amp;";
?>friendly=1" target="cal_printer_friendly" onmouseover="window.status = '<?php etranslate("Generate printer-friendly version")?>'">[<?php etranslate("Printer Friendly")?>]</a>

<?php print_trailer (); ?>
</body>
</html>
