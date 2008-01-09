<?php
include_once 'includes/init.php';

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
  
  //Debug
  logs($log_file,"trailer.php : role : ".$role_user."\n login : ".$login."\n group : ".$group_cal."\nuser : ".$user."\n");
  //debug
}


//Determine the info type
if($GLOBALS['type_param']=='group'){
  $info_type="type_param=group&group_param=".$GLOBALS['group_param']."&";
}else{
  $info_type="type_param=user&";
}

send_no_cache_header ();

if ( empty ( $user ) )
  $user = $login;

// Only admin user or assistant can specify a username other than his own.
if ( ! $is_admin && $user != $login  && ! $is_assistant)
  $user = $login;

$HeadX = '';
if ( $auto_refresh == "Y" && ! empty ( $auto_refresh_time ) ) {
  $refresh = $auto_refresh_time * 60; // convert to seconds
  $HeadX = "<meta http-equiv=\"refresh\" content=\"$refresh; URL=list_unapproved.php\" />\n";
}
$INC = array('js/popups.php');
print_header($INC,$HeadX);

$key = 0;

// List all unapproved events for the user
// Exclude "extension" events (used when an event goes past midnight)
function list_unapproved ( $user , $info_type="type_param=user&") {
  global $temp_fullname, $key, $login;
  //echo "Listing events for $user <br>";

  $sql = "SELECT e.cal_id, e.cal_name, ".
  "e.cal_description, e.cal_priority, e.cal_date, e.cal_time, ".
  "e.cal_duration, eu1.cal_status ". 
  "FROM webcal_entry e, webcal_entry_user eu1 WHERE ". 
  "e.cal_id = eu1.cal_id AND eu1.cal_status = 'W' ". 
  "AND (eu1.cal_group_status NOT LIKE ('%,A:".$user.",%') ".
  "OR eu1.cal_group_status is null) AND ".
  "(eu1.cal_group_status NOT LIKE ('%,R:".$user.",%') ".
  "OR eu1.cal_group_status is null) AND ". 
  "( eu1.cal_login = '".$user."' OR eu1.cal_login in ". 
  "( SELECT eu2.cal_login FROM webcal_entry e, webcal_entry_user eu2, ".
  "webcal_group_user gu,webcal_group g WHERE ".
  "e.cal_id = eu2.cal_id AND eu2.cal_login = g.cal_name ". 
  "AND g.cal_group_id = gu.cal_group_id ". 
  "AND gu.cal_login = '".$user."' AND ".
  "((eu2.cal_group_status NOT LIKE ('%,A:".$user.",%') ".
  "AND eu2.cal_group_status NOT LIKE ('%,R:".$user.",%')) ".
  " OR eu2.cal_group_status is null)))ORDER BY e.cal_date";
  $res = dbi_query ( $sql );
  $count = 0;
  $eventinfo = "";
  if ( $res ) {
    while ( $row = dbi_fetch_row ( $res ) ) {
      if ($count == 0 ) { echo "<ul>\n"; }
      $key++;
      $id = $row[0];
      $name = $row[1];
      $description = $row[2];
      $pri = $row[3];
      $date = $row[4];
      $time = $row[5];
      $duration = $row[6];
      $status = $row[7];
      $divname = "eventinfo-$id-$key";
      echo "<li><a title=\"" . 
      		translate("View this entry") . "\" class=\"entry\" href=\"view_entry.php?id=$id&amp;user=".$user."&".$info_type;
      echo "\" onmouseover=\"window.status='" . translate("View this entry") .
        "'; show(event, '$divname'); return true;\" onmouseout=\"hide('$divname'); return true;\">";
      $timestr = "";
      if ( $time > 0 ) {
        $timestr = display_time ( $time );
        if ( $duration > 0 ) {
          // calc end time
          $h = (int) ( $time / 10000 );
          $m = ( $time / 100 ) % 100;
          $m += $duration;
          $d = $duration;
          while ( $m >= 60 ) {
            $h++;
            $m -= 60;
          }
          $end_time = sprintf ( "%02d%02d00", $h, $m );
          $timestr .= " - " . display_time ( $end_time );
        }
      }
      echo htmlspecialchars ( $name );
      echo "</a>";
      echo " (" . date_to_str ($date) . ")\n";
//approve
      echo ": <a title=\"" . 
	translate("Approve/Confirm") . "\"  href=\"approve_entry.php?id=$id&amp;ret=list&amp;user=".$user."&".$info_type;
      if ( $user == "__public__" )
        echo "&amp;public=1";
      echo "\" class=\"nav\" onclick=\"return confirm('" .
        translate("Approve this entry?") . "');\">" . 
	translate("Approve/Confirm") . "</a>, ";
//reject
      echo "<a title=\"" . 
	translate("Reject") . "\" href=\"reject_entry.php?id=$id&amp;ret=list&amp;user=".$user."&".$info_type;
      if ( $user == "__public__" )
        echo "&amp;public=1";
      echo "\" class=\"nav\" onclick=\"return confirm('" .
        translate("Reject this entry?") . "');\">" . 
	translate("Reject") . "</a>";
//delete
      if(Can_Modify($id,$login)){
        echo ", <a title=\"" . 
	            translate("Delete") . "\" href=\"del_entry.php?id=$id&amp;ret=list"."&".$info_type;
        if ( $user != $login )
          echo "&amp;user=$user";
        echo "\" class=\"nav\" onclick=\"return confirm('" .
              translate("Are you sure you want to delete this entry?") . "');\">" . 
	            translate("Delete") . "</a>";
	    }
      echo "\n</li>\n";
      $eventinfo .= build_event_popup ( $divname, $user, $description,
                                          $timestr, site_extras_for_popup ( $id ));
      $count++;
    }
    dbi_free_result ( $res );
    if ($count > 0 ) { echo "</ul>\n"; }
  }
  if ( $count == 0 ) {
    user_load_variables ( $user, "temp_" );
    echo "<span class=\"nounapproved\">" . 
	translate("No unapproved events for") . "&nbsp;" . $temp_fullname . ".</span>\n";
  } else {
    if ( ! empty ( $eventinfo ) ) echo $eventinfo;
  }
}
?>

<h2><?php 
	etranslate("Unapproved Events"); 
	if ( $user == '__public__' ) echo " - " . $PUBLIC_ACCESS_FULLNAME; 
?></h2>
<?php
// List unapproved events for this user.
list_unapproved ( ( $is_assistant || $is_nonuser_admin || $is_admin ) ? $user : $login , $info_type);

// Admin users can also approve Public Access events
if ( $is_admin && $public_access == "Y" &&
  ( empty ( $user ) || $user != '__public__' ) ) {
  echo "\n<h3>" . translate ( "Public Access" ) . "</h3>\n";
  list_unapproved ( "__public__" );
}

// NonUser calendar admins cal approve events on that specific NonUser
// calendar.
if ( $nonuser_enabled == 'Y' ) {
  $admincals = get_nonuser_cals ( $login );
  for ( $i = 0; $i < count ( $admincals ); $i++ ) {
    echo "\n<h3>" . $admincals[$i]['cal_fullname'] . "</h3>\n";
    list_unapproved ( $admincals[$i]['cal_login'], $info_type );
  }
}
?>

<?php print_trailer(); ?>
</body>
</html>
