<?php
/**
 * Copyright 2010 (c) Mélanie Le Bail
 */



 
$GLOBALS['mailman_bin_dir'] =  '/usr/lib/mailman/bin';
$GLOBALS['mailman_lib_dir'] = '/var/lib/mailman';
$GLOBALS['forumml_arch'] = '/var/lib/mailman/archives';
$GLOBALS['forumml_tmp'] = '/var/spool/forumml';
$GLOBALS['forumml_dir'] = '/var/lib/gforge/forumml';
$GLOBALS['sys_lf'] = "\n"; 
global $html;

function isLogged(){
        
        return session_loggedin();
}

function htmlRedirect($url) {
        session_redirect($url);
}
function htmlIframe($url,$poub) {
        if (isset($poub['id'])) {
                $id=$poub['id'];
        }
        else {
                $id='';
        }
        echo ('<iframe src= "'.$url.'" id="'.$id.'" width=100% height=500px></iframe>');
}
    
function helpButton($help) {

}
function getIcon($url,$w=16,$h=16,$args=array()) {
        echo html_image($url,$w,$h,$args); 
}   
function getImage($img) {
        echo util_make_url($html->imgroot.$img);

}
function get_server_url() {
        return util_make_url();
}
?>
