<?php
/*
 * Plugin PhpBB
 */

#===================================
# Imports
#===================================

require_once ('../../env.inc.php');
require_once ($gfwww.'include/pre.php');
require_once ('plugins/phpbb/include/config_log.php');
require_once ('plugins/phpbb/common/PluginPhpBBDataHandler.class');
require_once ('plugins/phpbb/common/PluginPhpBBIHM.class');


//===================================
// BEGIN Security checking
//===================================


// Checks if the user is connected ? 
if (session_loggedin () == false)
{
    exit_not_logged_in ();
}
// Are we in a project context ?
if (!$group_id)
{
    exit_no_group ();
}
$g =& group_get_object($group_id);
if (!$g || !is_object ($g))
{
    exit_no_group ();
}
elseif ($g->isError ())
{
    exit_error ('Error', $g->getErrorMessage ());
}
// Checks if the connected user is the administrator of this project 
$perm =& $g->getPermission( session_get_user() );
if (!$perm->isAdmin()) {
    exit_permission_denied();
}

// Checks if the project use the phpbb plugin
if ( !$g->usesPlugin ('phpbb') ) {
    exit_error( "Erreur", dgettext('gforge-plugin-phpbb','not_activated_plugin') .  $g->getPublicName() );
}

//===================================
// END Security checking
//===================================

//Language vars
$lang_administration_phpbb = dgettext('gforge-plugin-phpbb','administration_phpbb');
$lang_add_new_instance = dgettext('gforge-plugin-phpbb','add_new_instance');
$lang_synchronize_data = dgettext('gforge-plugin-phpbb','synchronize_data');
$lang_no_instance_registred = dgettext('gforge-plugin-phpbb','no_instance_registred');

// NovaForge header page
site_project_header(array('title'=> $lang_administration_phpbb ,'group'=>$group_id,'toptab'=>'admin'));

echo $HTML->subMenu(array('['.$lang_add_new_instance.']',
                          '['.$lang_synchronize_data.']'),
                    array('instance.php?group_id='.$group_id,
                          'synchronize.php?group_id='.$group_id
     ));

     

$response = PluginPhpBBDataHandler::getInstances($group_id,$plugin_phpbb_instance_id,
$phpbb_category_id,$name,$url,$encoding);

if(count($plugin_phpbb_instance_id)>0){
    for($i = 0; $i<count($plugin_phpbb_instance_id) ; $i++){
        //reinit data
        $data = array();
        $data["group_id"] = $group_id;
        $data["url"] = $url[$i];
        $data["encoding"] = $encoding[$i];
        $data["name"] = $name[$i];
        $data["instance_id"] = $plugin_phpbb_instance_id[$i];
        echo PluginPhpBBIHM::display_instance($data);

    }
}else{
    echo '<b>'.$lang_no_instance_registred.'</b>';
}


// NovaForge footer page
site_project_footer(array());

?>
