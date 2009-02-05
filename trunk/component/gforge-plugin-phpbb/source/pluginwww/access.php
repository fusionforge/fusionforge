<?php


/*
 * Plugin PhpBB
 */

//===================================
// Imports
//===================================

require_once('../../env.inc.php');
require_once($gfwww.'include/pre.php');
require_once('common/novaforge/api/SessionApi.class');
require_once('common/novaforge/api/proxy.php');
require_once('common/novaforge/api/auth/auth.php');
require_once('plugins/phpbb/config.php');

//===================================
// BEGIN Security checking
//===================================


// Formating the URI to retieve the project ID and the query.
$query = substr( $_SERVER['REQUEST_URI'], strlen(  $_SERVER['SCRIPT_NAME'] ) + 1  );
list( $group_id,$instance_id, $q ) = explode( '/', $query, 3 );


// Are we in a project context ?
if (! isset($group_id) || !$group_id || ! isset($instance_id) || !$instance_id) {
    exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g)) {
    exit_no_group();
} elseif ($g->isError()) {
    exit_error('Error',$g->getErrorMessage());
}

// Is this project use the plugin phpbb ?
if ( !$g->usesPlugin ('phpbb') ) {
    exit_error( "Erreur", dgettext('gforge-plugin-phpbb','not_activated_plugin') .  $g->getPublicName() );
}

if ( !session_loggedin() ) {
    header( "Location: /account/login.php" );
}
//===================================
// END Security checking
//===================================

PluginPhpBBDataHandler::getInstanceData($group_id,$instance_id,$cat_id,$instance_name,$url,$encoding);


$noHead = false;
$sessionApi = new SessionApi();

$page =  'viewforum.php?f='.$cat_id;
if (!isset ($_GET ['gconnect'])){
    if (isset($_GET['q'] ))    $page = urldecode( $_GET['q'] );
    if (isset($q) )            $page = urldecode( $q );
}

$confProxy = new ProxyConfig($url ,     // server url to proxify
'' ,        // id string for cookie
$encoding   // encoding of the pages
);

$user = user_get_object($G_SESSION->getId());
$t_username = $user->getUnixName();
$t_passwd = $sessionApi->getUserPassword($t_username);

$UrlToProxify = trim($confProxy->getUrlToProxify());
if($UrlToProxify[strlen($UrlToProxify)-1] != DIRECTORY_SEPARATOR ){
    $UrlToProxify .= DIRECTORY_SEPARATOR;
}
$client = $UrlToProxify.'gforge/auth.php';

if( authenticate($client,$cookies,$t_username,$sys_default_domain) ){
    $nProxy = new ProxyRequest( $confProxy );
    $notError = $nProxy->getServHttpReponse( $page, $_POST, null, null, $cookies );

    if( $notError !== true ){
        exit_error( "Erreur",  "Connection PhpBB : " .  $notError );
        exit;
    }
}else{// Not authenticated
    exit_error("Erreur",dgettext('gforge-plugin-phpbb','error_authenticate'));
    exit;
}




//Manages the response
$httpHeader = new ProxyHttpHeader( $confProxy, $nProxy->httpHeader );
$httpHeader->fetchHeader();
$httpHeader->sendHeader();



// is it a html page ?
if($httpHeader->isHeaderOfHtmlPage() ){
     
    $reponse = new ProxyReponse( $confProxy, $nProxy->httpHtml );

    if( ! $reponse->hasHtlmHead() ) $noHead = false;

    $reponse->changeLink();
    $reponse->deleteHtmlHeader();
    $reponse->changeCharset();

    if( $noHead==false ){
        site_project_header(array('title'=>'PhpBB','group'=>$group_id,'toptab'=>$instance_name));
    }

    echo $reponse->reponse;

    if( $noHead==false ){
        site_project_footer(array());
    }
}else{

    // No change
    echo $nProxy->httpHtml;

}


?>
