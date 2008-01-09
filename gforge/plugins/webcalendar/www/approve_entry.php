<?php
include_once 'includes/init.php';
load_user_categories();

$error = "";

if ( $readonly == 'Y' ) {
  $error = translate("You are not authorized");
}

// Allow administrators to approve public events
if ( $public_access == "Y" && ! empty ( $public ) && $is_admin )
  $app_user = "__public__";
else
  $app_user = ( $is_assistant || $is_nonuser_admin ? $user : $login );

if ( empty ( $error ) && $id > 0 ) {

  $res = dbi_query ( "select COUNT(*) as num FROM webcal_entry_user WHERE cal_status = 'W' " .
          "AND cal_login = '$app_user' AND cal_id = ".$id );
  if(!$res || $res['num'] < 1 ){
    $result = dbi_query ( "SELECT * FROM webcal_entry_user WHERE cal_id = ".$id);
    while ($trait = dbi_fetch_row($result)) { 
      if($trait['cal_group_status'] == null){
        dbi_query ( "UPDATE webcal_entry_user SET cal_group_status = ',A:".$app_user.",' " .
                    "WHERE cal_id = ".$id." AND cal_login = '".$trait['cal_login']."'" );
      }else{
        //Get the users who approved the event
        $res_grp_status = dbi_query("select cal_group_status 
                                    from webcal_entry_user
                                    where cal_id=".$id." and cal_login='".$app_user."'");
        $grp_status = dbi_fetch_row($res_grp_status);
        if( !ereg('A:'.$app_user,$grp_status) ){
          dbi_query ( "UPDATE webcal_entry_user SET cal_group_status = cal_group_status||',A:".$app_user.",' " .
                      "WHERE cal_id = ".$id." AND cal_login = '".$trait['cal_login']."'" );
        }
      }
    }
    
    //Get the groups of the user for this entry
    $res_grps = dbi_query ( "select distinct(gu.cal_group_id),g.cal_name
                             from webcal_group_user gu, webcal_group g, webcal_entry_user eu
                             where gu.cal_login = '".$login."'
                               and g.cal_group_id = gu.cal_group_id
                               and g.cal_name = eu.cal_login
                               and eu.cal_id=".$id);
    if ( $res_grps ) {
      while ($group = dbi_fetch_row($res_grps) ){
        //Get the users for the group
        $res_users = dbi_query("select gu.cal_login 
                                from webcal_group_user gu, webcal_entry e
                                where gu.cal_group_id=".$group["cal_group_id"]."
                                  and e.cal_create_by <> gu.cal_login
                                  and e.cal_id=".$id);
        //Get the users who approved the event
        $res_grp_status = dbi_query("select cal_group_status 
                                     from webcal_entry_user eu, webcal_group g
                                     where cal_id=".$id." and eu.cal_login=g.cal_name and g.cal_group_id='".$group["cal_group_id"]."'");
        $grp_status = dbi_fetch_row($res_grp_status);
        $all_approve=1;
        //Control if all the users for the group have approve the event
        while ( $eu_user = dbi_fetch_row($res_users) ) {
          if( strpos($grp_status["cal_group_status"],"A:".$eu_user["cal_login"])==false ){
            $all_approve=0;
          }
        }
        
        if($all_approve==1){
          dbi_query ( "UPDATE webcal_entry_user SET cal_status = 'A' " .
                      "WHERE cal_login = '".$group["cal_name"]."' AND cal_id = ".$id );
        }
      }
    }
      
      //update the line of the user
      dbi_query ( "UPDATE webcal_entry_user SET cal_status = 'A' " .
                  "WHERE cal_login = '".$app_user."' AND cal_id = ".$id );    
  }else {
    if ( ! dbi_query ( "UPDATE webcal_entry_user SET cal_status = 'A' " .
                        "WHERE cal_login = '".$app_user."' AND cal_id = ".$id ) ) {
      $error = translate("Error approving event") . ": " . dbi_error ();
      //plugin add father
      $params[0] = $app_user ;
      $params[1] = $id ;
      plugin_hook('add_cal_link_father_event',$params);
    
    } else {
      activity_log ( $id, $login, $app_user, $LOG_APPROVE, "" );
    }
  }
  // Update any extension events related to this one.
  $res = dbi_query ( "SELECT cal_id FROM webcal_entry " .
    "WHERE cal_ext_for_id = $id" );
  if ( $res ) {
    if ( $row = dbi_fetch_row ( $res ) ) {
      $ext_id = $row[0];
      if ( ! dbi_query ( "UPDATE webcal_entry_user SET cal_status = 'A' " .
        "WHERE cal_login = '$app_user' AND cal_id = $ext_id" ) ) {
        $error = translate("Error approving event") . ": " . dbi_error ();
      }
    }
    dbi_free_result ( $res );
  }
}

if ( empty ( $error ) ) {
  if ( ! empty ( $ret ) && $ret == "list" )
    do_redirect ( "list_unapproved.php?user=$app_user" );
  else
    do_redirect ( "view_entry.php?id=$id&amp;user=$app_user" );
  exit;
}
print_header ();
echo "<h2>" . translate("Error") . "</h2>\n";
echo "<p>" . $error . "</p>\n";
print_trailer ();
?>
