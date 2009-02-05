<?php
session_start();

$phpbb_root_path = '../';

require_once(dirname( __FILE__ ).DIRECTORY_SEPARATOR.'auth_common.php');
define('IN_PHPBB', true);
define('IN_LOGIN', true);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);

# --- openSSL public key path
if(!isset($g_gforge_public_key_dir) || empty($g_gforge_public_key_dir)){
    //if openSSL public key path is not configured use the default path
    $g_gforge_public_key_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'cle'.DIRECTORY_SEPARATOR;
}

    $response  =  authenticate(requests());

    if($response == 1){
        
        $f_username    =  requests('username') ;
        
        $user->session_begin();
        $auth->acl($user->data);
        
          $config['auth_method'] = "novaforge";
          $connectResult = $auth->login($f_username,$f_userpass,  true, 0,  0);
          
          if($connectResult['status'] == LOGIN_SUCCESS){
              echo 1;
          }else{
              echo 0;
          }
          
    }else if($response == 0){
        echo 0;
        
    }else {       
       // Wait while authentification is being setup
    }
    
    
    /**
     * Supplies requests throwed by the server
     */
    function requests($string = false){
        if(!$string){
            return $_POST;
        }else{
            return isset($_POST[$string])?$_POST[$string]:'';
        }
    }
    
    function requestsTest($string = false){
        if(!$string){
            return $_GET;
        }else{
            return isset($_GET[$string])?$_GET[$string]:'';
        }
    }

    
?>
