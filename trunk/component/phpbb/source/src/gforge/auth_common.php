<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
?>
<?php
define('PUBLIC_KEY_EXTENSION','.crt');

/**
 *
 */

function authenticate($serverRequest){

    if($serverRequest['request'] == 'start_connexion' ){
        sendCheckingText();
        return 2;

    }else if($serverRequest['request'] == 'connect' ){

        if(  checkReceivedText($serverRequest['crypt'],$serverRequest['novaforge_domain_name']) ){

             
            unset($serverRequest["crypt"]);
            unset($serverRequest["request"]);
            unset($serverRequest["novaforge_domain_name"]);


            return  1;
        }else{
             
            return  0;
        }


    }

}

/**
 *
 * Send a random password to the server
 *
 * @private
 */
function sendCheckingText(){

    $testText = getRandomText(20,5);

    $_SESSION["checkText"] = $testText;
    $_SESSION["ip"]        = $_SERVER["REMOTE_ADDR"];

    echo $testText;
}

/**
 *
 * Check the validity of the password sent by the server
 *
 * @param string $crypt Password crypted and formated in Base 64.
 * @private
 * @ignore
 */
function checkReceivedText($crypt,$NFdomain){
    global  $g_gforge_public_key_dir;

    if(!empty($NFdomain) ){

        $publiqueKey = $g_gforge_public_key_dir.$NFdomain.PUBLIC_KEY_EXTENSION;
         
        $crypt = base64_decode(trim($crypt));
        openssl_public_decrypt($crypt,$plaintext,readf($publiqueKey));

        if (( strcmp($plaintext,$_SESSION["checkText"]) == 0)
        && ( strcmp($_SESSION["ip"],$_SERVER["REMOTE_ADDR"]) == 0)){
             
            unset($_SESSION["checkText"]);
            unset($_SESSION["ip"]);

            return true;
        }else{
            syslog(LOG_ERR,">> checkReceivedText - checking failed (".$plaintext.')===('.$_SESSION["checkText"].')');
            return false;
        }
    }else {
        syslog (LOG_ERR, ">> checkReceivedText - NFdomain empty");
        return false;
    }
}

/**
 *
 * Generates a random Text
 *
 * @param $length password length
 * @param $strength Strength level of the password between 1 and 5
 *
 * @return string a password string
 */
function getRandomText($length,$strength){

    $char_sets = array('48-57','65-90','97-122','35-38','61-64');
    $new_password='';
    srand(microtime()*10000000);
    for($i=0;$i<$length;$i++){
        $random = rand(0,$strength-1);
        list($start,$end) = explode('-',$char_sets[$random]);
        $new_password.= chr(rand($start,$end));
    }
    return($new_password);
}

/**
 *  Read a file
 *  @param $path string file path
 */
function readf($path){
    $returned = '';
    if(isset($path) && !empty($path)){
        $fp=fopen($path,"r");
        $returned=fread($fp,8192);
        fclose($fp);
    }else{
        syslog (LOG_ERR, ">>auth.php | _readf invalide path name : ". $path);
        return false;
    }

    return $returned;
}

?>
