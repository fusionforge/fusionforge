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

PluginPhpBBDataHandler::getInstanceData($group_id,$instance_id,$phpbb_cat_id,$name,$url,$encoding);

// NovaForge header page
site_project_header(array('title'=>$lang_administration_phpbb,'group'=>$group_id,'toptab'=>'admin'));

echo $HTML->subMenu(array('[<< '.$lang_back.']',
'['.$lang_add_category.']',
'['.$lang_apply_rule.']'),
array('managecategory.php?group_id='.$group_id.'&instance_id='.$instance_id,
'managecategory.php?action=add&group_id='.$group_id.'&instance_id='.$instance_id,
'applyrule.php?cat_id='.$phpbb_cat_id.'&group_id='.$group_id.'&instance_id='.$instance_id
));




if(isset($phpbb_post) && $phpbb_post = 1 ){

    PluginPhpBBDataHandler::getRoles($instance_id,$arr_real_role_id,$arr_real_php_role_id);

    $trans = array_flip($arr_real_role_id);
    foreach($post_roles as $role_id => $rule_value){
        $key = $trans[$role_id];
        $php_role_id = $arr_real_php_role_id[$key];

        if($php_role_id && $rule_value){
            PluginPhpBBInterface::applyRule($url,$cat_id,$php_role_id,$rule_value);
        }

    }
    header("location:managecategory.php?group_id={$group_id}&instance_id={$instance_id}");
     
}else{


    PluginPhpBBDataHandler::getInstanceData($group_id,$instance_id,$root_php_id,$name,$url,$enc);
    $available_rules = PluginPhpBBInterface::getAvailableRules($url);


    
    PluginPhpBBDataHandler::getRoles($instance_id,$array_roles_id,$array_phpbb_role_id);  
    PluginPhpBBInterface::getRulesFromRoles($url,$cat_id,$array_phpbb_role_id,$arr_ruleID);

    
    PluginPhpBBIHM::display_rules_affect($group_id,$instance_id,$cat_id,$array_roles_id,$arr_ruleID,$available_rules);
}



// NovaForge footer page
site_project_footer(array());

?>

