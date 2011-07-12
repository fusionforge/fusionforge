<?php

require_once('pre.php');
$GLOBALS['mailman_lib_dir'] = '/var/lib/mailman';
$GLOBALS['mailman_bin_dir'] = '/usr/lib/mailman/bin';
$GLOBALS['forumml_arch'] = '/var/lib/mailman/archives';
$GLOBALS['forumml_tmp'] = '/var/run/forumml';
$GLOBALS['forumml_dir'] = '/var/lib/codendi/forumml';

function isLogged(){
        return user_isloggedin();
}


function htmlRedirect($url) {
        $GLOBALS['HTML']->redirect($url);
}
function htmlIframe($url,$poub) {
        $GLOBALS['HTML']->iframe($url,array('class' => 'iframe_service'));
}


function helpButton($params)
{
        echo ' | ';
        echo help_button($params,false,_('Help'));
}
function getIcon() {
        echo '<IMG SRC="'.util_get_image_theme("ic/cfolder15.png").'" HEIGHT="13" WIDTH="15" BORDER="0">';
}
function util_make_url ($loc) {
        return session_make_url($loc);
}
function plugin_hook($hook,$params) {
        $em =& EventManager::instance();
        $em->processEvent($hook,$params);
}
function getImage($url) {
return util_get_image_theme($url);
}
?>
