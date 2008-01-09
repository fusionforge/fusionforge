<?php
/* $Id: admin_handler.php,v 1.7.4.4 2006/03/23 16:06:06 cknudsen Exp $ */
include_once 'includes/init.php';

//Debug
logs($log_file,"#######  admin_handler.php  #######\n");
//Debug

//Debug
logs($log_file,"login : ".$login."\n");
//Debug

if(isset($_POST['type_param'])){
  $GLOBALS['type_param']=$_POST['type_param'];
}else{
  $GLOBALS['type_param']='user';
}

if(isset($_POST['group_param'])){
  $GLOBALS['group_param']=$_POST['group_param'];
}

//Debug
logs($log_file, 'type : '.$GLOBALS['type_param']);
//Debug

$error = "";

if ( ! $is_admin ) {
  $error = translate("You are not authorized");
}

if ( $error == "" ) {
  while ( list ( $key, $value ) = each ( $HTTP_POST_VARS ) ) {
  
    //Debug
    logs($log_file, "key : ".strcmp ( $key, "type_param"));
    //Debug
    
    if ( (strcmp ( $key, "type_param") != 0 ) && (strcmp ( $key, "group_param") != 0) ){
    
      //Debug
      logs($log_file, "key : ".$key);
      //Debug
      
      $setting = substr ( $key, 6 );
      if ( $key == 'ovrd'  )
        continue;
      // validate key name.  should start with "admin_" and not include
      // any unusual characters that might cause SQL injection
      if ( ! preg_match ( '/admin_[A-Za-z0-9_]+$/', $key ) ) {
        die_miserable_death ( 'Invalid admin setting name "' .
          $key . '"' );
      }
      if ( strlen ( $setting ) > 0 ) {
        $sql = "DELETE FROM webcal_config WHERE cal_setting = '$setting'";
        if ( ! dbi_query ( $sql ) ) {
          $error = translate("Error") . ": " . dbi_error () .
            "<br /><br /><span style=\"font-weight:bold;\">SQL:</span> $sql";
          break;
        }
        if ( strlen ( $value ) > 0 ) {
          $sql = "INSERT INTO webcal_config " .
            "( cal_setting, cal_value ) VALUES " .
            "( '$setting', '$value' )";
          if ( ! dbi_query ( $sql ) ) {
            $error = translate("Error") . ": " . dbi_error () .
              "<br /><br /><span style=\"font-weight:bold;\">SQL:</span> $sql";
            break;
          }
        }
      }
    }
  }
}

$u_url = "type_param=".$GLOBALS['type_param'];
if($GLOBALS['type_param'] == 'group'){
  $u_url .= "&group_param=".$GLOBALS['group_param'];
}

//Debug
logs($log_file,"u_url : ".$u_url);
//Debug

if ( empty ( $error ) ) {
  if ( empty ( $ovrd ) ){
    do_redirect ( "admin.php?".$u_url );
  }else{
    do_redirect ( "admin.php?ovrd=".$ovrd."&".$u_url );
  }
}

print_header();
?>

<h2><?php etranslate("Error")?></h2>

<?php etranslate("The following error occurred")?>:
<blockquote>
<?php echo $error; ?>
</blockquote>

<?php print_trailer(); ?>

</body>
</html>
