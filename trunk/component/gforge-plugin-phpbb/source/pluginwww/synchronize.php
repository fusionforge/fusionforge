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
require_once ('plugins/phpbb/common/PluginPhpBB.class');
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
$lang_back = dgettext('gforge-plugin-phpbb','back');
$lang_administration_phpbb = dgettext('gforge-plugin-phpbb','administration_phpbb');
$lang_question_synchronize = dgettext('gforge-plugin-phpbb','question_synchronize');
$lang_end_synchronize = dgettext('gforge-plugin-phpbb','end_synchronize');

// NovaForge header page
site_project_header(array('title'=>$lang_administration_phpbb,'group'=>$group_id,'toptab'=>'admin'));

echo $HTML->subMenu(array('[<< '.$lang_back.']'),
                    array('admin.php?group_id='.$group_id));


                    
if(isset($phpbb_post) && $phpbb_post = 1 ){
    if(isset($post_yes)){
        PluginPhpBB::synchronize();
        echo '<b>'.$lang_end_synchronize.'</b>';
    }else{
        header("location:admin.php?group_id=$group_id");
    }    
    
}else{
    PluginPhpBBIHM::display_confirm_form($lang_question_synchronize);
}


// NovaForge footer page
site_project_footer(array());

?>
