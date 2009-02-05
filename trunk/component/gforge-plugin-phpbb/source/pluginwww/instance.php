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
$lang_back = dgettext('gforge-plugin-phpbb','back');
$lang_add_category = dgettext('gforge-plugin-phpbb','add_category');
$lang_apply_rule = dgettext('gforge-plugin-phpbb','apply_rule');
$lang_updating_instance_failed = dgettext('gforge-plugin-phpbb','updating_instance_failed');
$lang_fields_empty = dgettext('gforge-plugin-phpbb','fields_empty');
$lang_question_delete_instance = dgettext('gforge-plugin-phpbb','question_delete_instance');
$lang_error_create_instance = dgettext('gforge-plugin-phpbb','error_create_instance');
$lang_error_remove_instance = dgettext('gforge-plugin-phpbb','error_remove_instance');
$lang_error_not_valide_url = dgettext('gforge-plugin-phpbb','error_not_valide_url');

ob_start();
echo $HTML->subMenu(array('[<< '.$lang_back.' ]'),
array('admin.php?group_id='.$group_id)
);

//BEGIN Main treatment

// Edit mode
if(isset($action)&& $action == 'edit'){
    // If add data are sent.
    if(isset($phpbb_post) && $phpbb_post = 1 ){
        
        $post_name = trim($post_name);
        $post_url = trim($post_url);
        $post_encoding = trim($post_encoding);
        if(!empty($post_name) && !empty($post_url) && !empty($post_encoding)){
    
            if(preg_match('|^http(s)?://[a-z0-9-]+(\.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $post_url)){
                $post_url = trim($post_url);
                if($post_url[strlen($post_url)-1] != '/' ){
                    $post_url .= '/';
                }

                $result = PluginPhpBBDataHandler::updateInstance($group_id,$post_instance_id,null,$post_name,$post_url,$post_encoding);

                if($result){
                    header("location:admin.php?group_id=$group_id");
                }else{
                    echo '<b>'.$lang_updating_instance_failed.'</b>';
                }
            }else{
                echo '<b>'.$lang_error_not_valide_url.'</b>';
            }
        }else{
            echo '<b>'.$lang_fields_empty.'</b>';
        }
    }else{
        PluginPhpBBDataHandler::getInstanceData($group_id,$instance_id,$phpbb_category_id,$name,$url,$encoding);
        PluginPhpBBIHM::display_instance_form('edit',$group_id,$instance_id,$name,$url,$encoding);
    }
    // Delete mode
}else if(isset($action)&& $action == 'delete'){

    if(isset($phpbb_post) && $phpbb_post = 1 && isset($post_yes)){

        $return = PluginPhpBBDataHandler::removeInstance($group_id,$instance_id);
        if($return){
            header("location:admin.php?group_id=$group_id");
        }else{
            echo '<b>'.$lang_error_remove_instance.'</b>';
        }

    }else{
        PluginPhpBBIHM::display_confirm_form($lang_question_delete_instance);
    }
    // Add mode
}else{

    // If add data are sent.
    if(isset($phpbb_post) && $phpbb_post = 1 ){
        
        $post_name = trim($post_name);
        $post_url = trim($post_url);
        $post_encoding = trim($post_encoding);
        if(!empty($post_name) && !empty($post_url) && !empty($post_encoding)){

            if(preg_match('|^http(s)?://[a-z0-9-]+(\.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $post_url)){
                $post_url = trim($post_url);
                if($post_url[strlen($post_url)-1] != '/' ){
                    $post_url .= '/';
                }
                $return =  PluginPhpBBDataHandler::createInstance($group_id,$instance_id,0,$post_name,$post_url,$post_encoding);
                //PluginPhpBB::activatePlugin($group_id,$instance_id);

                if($return){
                    header("location:admin.php?group_id=$group_id");
                }else{
                    echo '<b>'.$lang_error_create_instance.'</b>';
                }
            }else{
                echo '<b>'.$lang_error_not_valide_url.'</b>';
            }
        }else{
            echo '<b>'.$lang_fields_empty.'</b>';
        }

    }else{
        PluginPhpBBIHM::display_instance_form('add',$group_id,0,'','',DEFAULT_ENCODING);
    }


}


//END Main treatment

$htmldata = ob_get_contents();ob_end_clean();
// NovaForge header page
site_project_header(array('title'=>$lang_administration_phpbb,'group'=>$group_id,'toptab'=>'admin'));

echo $htmldata;
// NovaForge footer page
site_project_footer(array());

?>

