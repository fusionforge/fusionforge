<?php


define('IN_PHPBB', true);

$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
require($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/functions_user.' . $phpEx);
require($phpbb_root_path . 'includes/functions_module.' . $phpEx);
require($phpbb_root_path . 'includes/acp/acp_forums.' . $phpEx);
require($phpbb_root_path . 'includes/acp/acp_permissions.' . $phpEx);
require($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
require($phpbb_root_path . 'includes/ucp/ucp_main.' . $phpEx);

require_once("logger.php");

/**
 * This static class handles PhpBB and gives some services to use phpBB without the web interface
 *
 * @author Panneer CANABADY <panneerselvam.canabady@bull.net>
 * @version 1.0
 * @package PluginPhpBB
 */
class PhpBBHandler {


    /**
     * Subscribes a user into PhpBB
     *
     * @param String $username user name.
     * @param String $email email adress
     * @param String $password  MD5 encoded password
     * @param String $language the user language ex: 'en', 'fr'
     * @param float $timezone shifted time related to UTC base time.
     *                           examples : paris : +1 / kaboul +4.5 / hawaii -10 .
     *
     * @return integer the new user id or zero if an error occured
     */
    function createUser($username,$email,$password,$language = 'en' ,$timezone = 1 ) {

        $user_row = PhpBBHandler::_getDefaultUserData() + array(
        'username'              => $username,
        'user_password'         => $password,
        'user_email'            => $email,
        'user_lang'             => $language,
        'user_timezone'         => (float)$timezone
        );

        // Register user...
        $user_id = user_add($user_row);
        return $user_id;
    }

    /**
     * Creates a group into PhpBB
     *
     * @param string groupName group name
     *
     * @return the new group id or zero if an error occured
     */
    function createGroup($groupName,$groupDesc = '' ) {
        group_create($group_id, GROUP_CLOSED, $groupName, $groupDesc,array()) ;
        return $group_id;
    }

    /**
     * Creates category into PhpBB.
     *
     * @param int $parentId
     * @param string $newCategory new category name
     *
     * @return the new category id or zero if an error occured
     */
    function createCategory($parentId,$newCategory) {
         
        $forum_data = PhpBBHandler::_getDefaultCategoryData() + array(
        'parent_id'     => $parentId,
        'forum_name'        => $newCategory
        );


        $v = new acp_forums;
        $v->update_forum_data($forum_data);
        PhpBBHandler::_copyPermissons($forum_data,$parentId);
        return $forum_data['forum_id'];
    }

    /**
     * Renames a category
     *
     * @param integer $categoryId the id of the category
     * @param string $newName the new name of the categorie
     */
    function renameCategory($categoryId,$newName) {

        $v = new acp_forums;
        $forum_data = $v->get_forum_info($categoryId);

        $forum_data = array_merge($forum_data,array('forum_id'          => $categoryId,
        'forum_name'        => $newName,
        'forum_type'        => FORUM_POST
        )
        );
        $v->update_forum_data($forum_data);

        return $forum_data['forum_id'];
    }

    /**
     * Deletes a categorie (alias project) from PhpBB
     *
     * @param integer the id of the categorie
     */
    function deleteCategory($categoryId) {
        $action_subforums   = 'delete';
        $subforums_to_id    =  0;
        $action_posts       = 'delete';
        $posts_to_id        = 0;

        $forum_id = $categoryId;

        $v = new acp_forums;
        //Action = delete or move
        $errors = $v->delete_forum($forum_id, $action_posts, $action_subforums,
        $posts_to_id,$subforums_to_id);

        return $forum_id;
    }

    function _copyPermissons($forum_data,$forum_perm_from){
        global $db,$auth, $cache;
         

        // $action = 'edit';
        // Copy permissions?
        if ($forum_perm_from && !empty($forum_perm_from) && $forum_perm_from != $forum_data['forum_id'])
        {
            // if we edit a forum delete current permissions first
            if ($action == 'edit')
            {
                $sql = 'DELETE FROM ' . ACL_USERS_TABLE . '
                                    WHERE forum_id = ' . (int) $forum_data['forum_id'];
                $db->sql_query($sql);

                $sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
                                    WHERE forum_id = ' . (int) $forum_data['forum_id'];
                $db->sql_query($sql);
            }

            // From the mysql documentation:
            // Prior to MySQL 4.0.14, the target table of the INSERT statement cannot appear in the FROM clause of the SELECT part of the query. This limitation is lifted in 4.0.14.
            // Due to this we stay on the safe side if we do the insertion "the manual way"

            // Copy permisisons from/to the acl users table (only forum_id gets changed)
            $sql = 'SELECT user_id, auth_option_id, auth_role_id, auth_setting
                                FROM ' . ACL_USERS_TABLE . '
                                WHERE forum_id = ' . $forum_perm_from;
            $result = $db->sql_query($sql);

            $users_sql_ary = array();
            while ($row = $db->sql_fetchrow($result))
            {
                $users_sql_ary[] = array(
                'user_id'           => (int) $row['user_id'],
                'forum_id'          => (int) $forum_data['forum_id'],
                'auth_option_id'    => (int) $row['auth_option_id'],
                'auth_role_id'      => (int) $row['auth_role_id'],
                'auth_setting'      => (int) $row['auth_setting']
                );
            }
            $db->sql_freeresult($result);

            // Copy permisisons from/to the acl groups table (only forum_id gets changed)
            $sql = 'SELECT group_id, auth_option_id, auth_role_id, auth_setting
                                FROM ' . ACL_GROUPS_TABLE . '
                                WHERE forum_id = ' . $forum_perm_from;
            $result = $db->sql_query($sql);

            $groups_sql_ary = array();
            while ($row = $db->sql_fetchrow($result))
            {
                $groups_sql_ary[] = array(
                'group_id'          => (int) $row['group_id'],
                'forum_id'          => (int) $forum_data['forum_id'],
                'auth_option_id'    => (int) $row['auth_option_id'],
                'auth_role_id'      => (int) $row['auth_role_id'],
                'auth_setting'      => (int) $row['auth_setting']
                );
            }
            $db->sql_freeresult($result);

            // Now insert the data
            $db->sql_multi_insert(ACL_USERS_TABLE, $users_sql_ary);
            $db->sql_multi_insert(ACL_GROUPS_TABLE, $groups_sql_ary);

            $auth->acl_clear_prefetch();
            $cache->destroy('sql', FORUMS_TABLE);
        }
    }

    /**
     *
     * Applies a rule to a group attached to a project.
     *
     * @param int $$catID the group id
     * @param int $groupID the group id
     * @param string $ruleName the rule to be applied to the group
     *
     * @return true if the rule is applied successfully or false otherwise
     */
    function applyRule($catID,$roleID,$ruleID){

        tracer("applyRule($catID,$roleID,$ruleID)");
        $auth = array();
        $adm = new auth_admin;
        $adm->acl_delete('group',$roleID,$catID, 'f_');
        $adm->acl_set('group',$catID,$roleID,$auth,$ruleID);
         
        return $catID;
    }

    /**
     * Adds a user into a group
     *
     * @param string $userName the user name
     * @param string $groupName the group name
     * @return boolean
     */
    function addUserToGroup($group_id,$username_ary){
        $error = group_user_add($group_id,false,$username_ary);
        return !$error;
    }

    /**
     * Deletes a group from PhpBB
     *
     * @param integer $role_id role id
     * @param array user name array
     */
    function removeUserToGroup($role_id,$arr_user_name) {
        $error = group_user_del($role_id, false, $arr_user_name, false);
        return !$error;
    }

    /**
     * Removes All users from a group
     *
     * @param integer $role_id role id
     */
    function removesAllUsers($role_id){
        $members = group_memberships(array($role_id) );

        if(is_array($members) && count($members) >0){
            $arr_user_id = array();
            foreach($members as $user){
                $arr_user_id[] = $user['user_id'];
            }
            group_user_del($role_id,$arr_user_id);
        }

        return $role_id;
    }

    /**
     * Gives the direct sub-categories of a category
     *
     * @param integer $catID category ID
     */
    function getSubForums($catID){
        $response = get_forum_branch($catID);
        $result = array();
        foreach ($response as $row)
        {
            if($row['parent_id'] == $catID){
                $result[]= $row;
            }
        }
        return $result;
    }

    /**
     *
     * Supplies a set of Rules available
     *
     * @return array A set of Rules
     *
     */
    function getAvailableRules() {

        global $db;
        // Get available roles

        $sql = 'SELECT *
                FROM ' . ACL_ROLES_TABLE . "
                WHERE role_type = 'f_'
                ORDER BY role_order ASC";

        $result = $db->sql_query($sql);

        $roles = array();
        while ($row = $db->sql_fetchrow($result))
        {

            $id = $row['role_id'];
            $roles[$id] = $row['role_name'];

        }
        $db->sql_freeresult($result);
        return $roles;
    }

    /**
     * Test the existence of a User in PhpBB
     *
     * @param User $usr user to check
     * @return boolean returns true if the user exists
     */
    function existsUser($usrName) {
        $usrID = 0;
        PhpBBHandler::_checkExistence('user',$usrName,$usrID);
        return $usrID;
    }

    /**
     * Test the existence of a Category by checking the ID
     *
     * @param integer $catID the project to check
     * @param integer out $catName the related name of the category
     * @return integer postif integer $catID if success
     */
    function existsCategoryID($catID,&$catName){
        $catName = '';
        if(PhpBBHandler::_checkExistence('forum_id',$catID,$catName)){
            return $catID;
        }else{
            return 0;
        }

    }

    /**
     * Test the existence of a Category by checking the Name
     *
     * @param integer $catName the project name to check
     * @param integer out $catID ID witch maches witch the name
     * @param integer $parent_id parent id
     * @return integer postif integer $catID if success
     */
    function existsCategoryName($catName,$parent_id,&$catID){
        $catID = 0;
        PhpBBHandler::_checkExistence('forum_name',$catName,$catID,$parent_id);
        return $catID;
    }


    /**
     * Test the existence of a Group in PhpBB
     *
     * @param Group $aRole Group to check
     * @return boolean returns true if the group exists
     */
    function existsGroup($roleName){
        $roleID = 0 ;
        PhpBBHandler::_checkExistence('group',$roleName,$roleID);
        return $roleID;
    }

    function getBookmarks($userName){
        global $user, $db, $template, $config, $auth;

        if(isset($userName) && !empty($userName)){
            // Start session management
            $user->session_begin();
            $auth->acl($user->data);

            $config['auth_method'] = "novaforge";
            $connectResult = $auth->login($userName,$f_userpass,  true, 0,  0);

            if($connectResult['status'] == LOGIN_SUCCESS){
                $user->setup('ucp');
                $user->add_lang('viewforum');

                // Setting a variable to let the style designer know where he is...
                $template->assign_var('S_IN_UCP', true);
                $module = new p_master();
                $ucp = new ucp_main($module);

                $template = new template();
                $template->set_custom_template(".", "novaforge");
                $template->set_filenames(array(  'body2' => 'bookmark.xml') );

                //global $user, $db, $template, $config, $auth,
                $ucp->assign_topiclist('bookmarks');

                $template->display('body2');
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    function getRuleFromRole($role_id,$cat_id){
        global $db;

        $sql = 'SELECT  auth_role_id FROM phpbb_acl_groups where  group_id = '.$role_id;
        $sql .= ' AND  forum_id='.$cat_id ;

        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);       
        
        $db->sql_freeresult($result);
        if($row){
            return $row['auth_role_id'];
        }else{
            return 0;
        }
    }

    /**
     * Supplies the default setup for a new category subscription
     *
     * @return array a set of data for building a new category
     */
    function _getDefaultCategoryData(){
        return array(
        'forum_type'            => FORUM_POST,
        'type_action'           => '',
        'forum_status'          => ITEM_UNLOCKED,
        'forum_parents'         => '',
        'forum_link'            => '',
        'forum_link_track'      => false,
        'forum_desc'            => '',
        'forum_desc_uid'        => '',
        'forum_desc_options'            => 7,
        'forum_desc_bitfield'           => '',
        'forum_rules'           => '',
        'forum_rules_uid'       => '',
        'forum_rules_options'           => 7,
        'forum_rules_bitfield'          => '',
        'forum_rules_link'      => '',
        'forum_image'           => '',
        'forum_style'           => 0,
        'display_on_index'      => false,
        'forum_topics_per_page'         => 0,
        'enable_indexing'       => true,
        'enable_icons'          => false,
        'enable_prune'          => false,
        'enable_post_review'            => true,
        'prune_days'            => 7,
        'prune_viewed'          => 7,
        'prune_freq'            => 1,
        'prune_old_polls'       => false,
        'prune_announce'        => false,
        'prune_sticky'          => false,
        'forum_password'        => '',
        'forum_password_confirm'        => ''
        );
    }

    /**
     * Supply the default data to create a user
     *
     */
    function _getDefaultUserData(){
        return  array(
        'group_id'              => 2,       //Registred user by default
        'user_dst'              => 0, //dÃ©catage
        'user_type'             => USER_NORMAL,
        'user_actkey'           => '',
        'user_ip'               => '',
        'user_regdate'          => time(),
        'user_inactive_reason'  => 0,
        'user_inactive_time'    => 0
        );

    }

    /**
     * Gives a set of permissions related to a rule
     * TODO to delete
     * @param integer the Id of the rule
     * @return array gives a list of permissions related to a rule
     */
    function _getPermissionsFromRule($ruleName){
        global $db;

        $returnArray = array();

        $sql = 'SELECT dr.role_id, o.auth_option, dr.auth_setting
            FROM ' . ACL_ROLES_DATA_TABLE . ' dr, ' . ACL_OPTIONS_TABLE . ' o, '.ACL_ROLES_TABLE.' r
            WHERE o.auth_option_id = dr.auth_option_id
                                AND r.role_id = dr.role_id 
            AND r.role_name = \'' . $db->sql_escape($ruleName).'\' ';

        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result))
        {

            $optionName = $row['auth_option'];
            $flag = substr($optionName, 0, strpos($optionName, '_') + 1);

            if ($flag == $optionName)
            continue;

            $returnArray[$optionName] = $row['auth_setting'];

        }
        $db->sql_freeresult($result);
        return $returnArray;
    }

    /**
     * Check the existance of a user , a group of user or a forum
     *
     * @param $mode string 'user' | 'group' | 'forum'
     * @param $val string testing value
     * @return boolean
     */
    function _checkExistence($mode, $val, &$val2,$val3 = false){
        global $db;

        switch ($mode)
        {
            case 'user':
                $table = USERS_TABLE;
                $sql_id = 'username';
                $sql_id2 = '';
                $real_id = 'user_id';
                break;

            case 'group':
                $table = GROUPS_TABLE;
                $sql_id = 'group_name';
                $sql_id2 = '';
                $real_id = 'group_id';
                break;

            case 'forum_name':
                $table = FORUMS_TABLE;
                $sql_id = 'forum_name';
                $sql_id2 = 'parent_id';
                $real_id = 'forum_id';
                break;
            case 'forum_id':
                $table = FORUMS_TABLE;
                $sql_id = 'forum_id';
                $sql_id2 = '';
                $real_id = 'forum_name';
                break;
        }

        if (sizeof($val))
        {
            $sql = "SELECT $sql_id,$real_id
                    FROM $table
                    WHERE " . $db->sql_in_set($sql_id, $val);

            if($val3 ){
                $sql.= " AND ". $db->sql_in_set($sql_id2, $val3);
            }
            $result = $db->sql_query($sql);

            $row = $db->sql_fetchrow($result);

            $db->sql_freeresult($result);

            if($row ){
                $val2 = $row[$real_id];
                return true;
            }else{
                return false;
            }

        }else{
            return false;
        }
    }

}
?>
