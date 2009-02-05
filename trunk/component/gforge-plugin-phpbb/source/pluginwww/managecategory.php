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
$lang_question_delete_category = dgettext('gforge-plugin-phpbb','question_delete_category');
$lang_flied_name_empty = dgettext('gforge-plugin-phpbb','flied_name_empty');
$lang_error_create_category = dgettext('gforge-plugin-phpbb','error_create_category');
$lang_error_rename_category = dgettext('gforge-plugin-phpbb','error_rename_category');
$lang_error_delete_category = dgettext('gforge-plugin-phpbb','error_delete_category');
$lang_no_subcat_registred = dgettext('gforge-plugin-phpbb','no_subcat_registred');


// NovaForge header page
site_project_header(array('title'=>$lang_administration_phpbb,'group'=>$group_id,'toptab'=>'admin'));

PluginPhpBBDataHandler::getInstanceData($group_id,$instance_id,$phpbb_cat_id,$name,$url,$encoding);

if(isset($action) && !empty($action)){
    $back_url = 'managecategory.php?group_id='.$group_id.'&instance_id='.$instance_id;
}else{
    $back_url = 'admin.php?group_id='.$group_id;
}

echo $HTML->subMenu(array('[<< '.$lang_back.']',
                          '['.$lang_add_category.']',
                          '['.$lang_apply_rule.']'),
                    array($back_url,
                          'managecategory.php?action=add&group_id='.$group_id.'&instance_id='.$instance_id,
                          'applyrule.php?cat_id='.$phpbb_cat_id.'&group_id='.$group_id.'&instance_id='.$instance_id
));



//Rename mode
if(isset($action)&& $action == 'rename'){
    // If rename data are sent.
    if(isset($phpbb_post) && $phpbb_post = 1 ){
        $post_name = trim($post_name);
        if(!empty($post_name) ){
            $cat_id = PluginPhpBB::renameCategory($group_id,$instance_id,$cat_id,$post_name);
            if( $cat_id > 0 ){
                header("location:managecategory.php?group_id={$group_id}&instance_id={$instance_id}");
            }else{
                echo '<b>'.$lang_error_rename_category.'</b>';
            }
        }else{
            echo '<b>'.$lang_flied_name_empty.'</b>';
        }
    }else{
        PluginPhpBBIHM::display_subcat_form('edit',$group_id,$instance_id,$phpbb_cat_id,$cat_name);
    }
    //Delete mode
}else if (isset($action)&& $action == 'delete'){

    if(isset($phpbb_post) && $phpbb_post = 1 ){
        if(isset($post_yes) ){
            $cat_id = PluginPhpBB::deleteCategory($group_id,$instance_id,$cat_id);
            if( $cat_id > 0 ){
                header("location:managecategory.php?group_id={$group_id}&instance_id={$instance_id}");
            }else{
                echo '<b>'.$lang_error_delete_category.'</b>';
            }
        }else{
            header("location:managecategory.php?group_id={$group_id}&instance_id={$instance_id}");
        }
    }else{
        PluginPhpBBIHM::display_confirm_form($lang_question_delete_category);
    }
    //Add mode
}else if (isset($action)&& $action == 'add'){
     
    // If add data are sent.
    if(isset($phpbb_post) && $phpbb_post = 1 ){

        if(!empty($post_name)){
            $cat_id = PluginPhpBB::createCategory($group_id,$instance_id,$phpbb_cat_id,$post_name);
            if( $cat_id > 0 ){
                header("location:managecategory.php?group_id={$group_id}&instance_id={$instance_id}");
            }else{
                echo '<b>'.$lang_error_create_category.'</b>';
            }
        }else{
            echo '<b>'.$lang_flied_name_empty.'</b>';
        }

    }else{
        PluginPhpBBIHM::display_subcat_form('add',$group_id,$instance_id,$phpbb_cat_id,'');
    }

    //Default mode , display mode
}else{
     
    $arr_sub_cat = PluginPhpBBInterface::getSubCategories($url,$phpbb_cat_id);
    
    if(count($arr_sub_cat)>0){
        PluginPhpBBIHM::display_subcat($group_id,$instance_id,$arr_sub_cat);
    }else{
        echo '<b>'.$lang_no_subcat_registred.'</b>';
    }
    
}



// NovaForge footer page
site_project_footer(array());

?>

