<?php
/**
 * PhpBB plugin
 * 
 * This class is the interface of the plugin PhpBB for NovaForge
 *
 * @author Panneer CANABADY <panneerselvam.canabady@bull.net>
 * @version 1.0
 * @package PluginPhpBB
 */

require_once ('plugins/phpbb/common/PluginPhpBBInterface.class');
require_once ('plugins/phpbb/common/NovaForgeHandler.class');


class PluginPhpBB {
    /**
     * Creates a new category under a category for a project
     *
     * @param integer $projectId the id of the project
     * @param integer $instance_id the instance id
     * @param integer $parentId  the id of the parent of the category
     * @param string $newCategory the name of the new category
     */
    function createCategory($projectId,$instance_id,$parentId,$newCategory) {
        syslog (LOG_INFO, ">> PluginPhpBB::createCategory($projectId,$instance_id,$parentId,$newCategory)");

        $returned=-1;

        PluginPhpBBDataHandler::getInstanceData($projectId,$instance_id,$cat_id,$name,$url,$encoding);

        if ( isset ( $url ) && !empty( $url ) && isset ( $newCategory ) && !empty( $newCategory )
        ){
            $returned = PluginPhpBBInterface::createCategory($url,$parentId,$newCategory);
        }
        else
        {
            syslog(LOG_ERR, ">> PluginPhpBB::createCategory verify the parameter");
        }

        syslog (LOG_INFO, "<<  PluginPhpBB::createCategory return $returned");
        return ($returned);
    }

    /**
     * Renames a category
     *
     * @param integer $projectId the id of the project
     * @param integer $instance_id the instance id
     * @param integer $categoryId  the id of the parent of the category
     * @param string $newName the name of the new category
     */
    function renameCategory($projectId,$instance_id,$categoryId,$newName) {
        syslog (LOG_INFO, ">>  PluginPhpBB::renameCategory($projectId,$instance_id,$categoryId,$newName)");

        $returned=-1;

        PluginPhpBBDataHandler::getInstanceData($projectId,$instance_id,$cat_id,$name,$url,$encoding);
        if ( isset ( $categoryId ) && !empty( $categoryId ) &&
        isset ( $newName ) && !empty( $newName ) &&
        isset ( $url ) && !empty( $url )
        ){
            $returned = PluginPhpBBInterface::renameCategory($url,$categoryId,$newName);
        }
        else
        {
            syslog(LOG_ERR, ">>PluginPhpBB::renameCategory verify the parameters");
        }

        syslog (LOG_INFO, "<< PluginPhpBB::renameCategory return $returned");
        return ($returned);
    }
    
    /**
     * Delete a category
     *
     * @param integer $projectId the id of the project
     * @param integer $instance_id the instance id
     * @param integer $categoryId  the id of the parent of the category
     */
    function deleteCategory($projectId,$instance_id,$categoryId) {
        syslog (LOG_INFO, ">>  PluginPhpBB::deleteCategory($projectId,$instance_id,$categoryId)");

        $returned=-1;

        PluginPhpBBDataHandler::getInstanceData($projectId,$instance_id,$cat_id,$name,$url,$encoding);
        if ( isset ( $categoryId ) && !empty( $categoryId ) &&
        isset ( $url ) && !empty( $url )
        ){
            $returned = PluginPhpBBInterface::deleteCategory($url,$categoryId);
        }
        else
        {
            syslog(LOG_ERR, ">>PluginPhpBB::deleteCategory verify the parameters");
        }

        syslog (LOG_INFO, "<< PluginPhpBB::deleteCategory return $returned");
        return ($returned);
    }
    
    /**
     * Get the bookmarks of a user 
     * @return array in each index an other array contains the following keys : <br>
     *                      NAME = The name of the subject <br>
     *                      DATE = the last submitted date<br>
     *                      READ = 'false' if new response no read by the user <br>
     *                      URL = url to point rech the topic <br>
     *                      LAST_URL = url to point rech the last response of the topic <br>
     */
    function getBookmarks($user_id){
        syslog (LOG_INFO, ">>  PluginPhpBB::getBookmarks($user_id)");
        $bookmarks = array();
        $urls =  array();

        $aUser = &user_get_object($user_id);
        $arr_obj_projects = $aUser->getGroups();
 
        foreach ($arr_obj_projects as $obj_project){
            $arr_instance_id = array();
            $project_id = $obj_project->getID();

            PluginPhpBBDataHandler::getInstances($project_id,$arr_instance_id,$arr_phpbb_cat_id,$arr_name,$arr_url,$arr_encoding);

            foreach($arr_instance_id as $key => $instance_id ){
                $url = $arr_url[$key];
                $urls[$url] = array($project_id,$instance_id);
            }

        }

        foreach ($urls as $url => $value){
            $curent_books = PluginPhpBBInterface::getBookmarks($url ,$aUser->getUnixName() );

            $project_id = $value[0];
            $instance_id = $value[1];

            foreach($curent_books as $key => $value){
                $path = "/plugins/phpbb/access.php/{$project_id}/{$instance_id}";
                $url = $curent_books[$key]['URL'];
                $url = stristr($url, '/');
                $url = $path.$url;
                $curent_books[$key]['URL'] = $url;


                $url2 = $curent_books[$key]['LAST_URL'];
                $url2 = stristr($url2, '/');
                $url2 = $path.$url2;
                $curent_books[$key]['LAST_URL'] = $url2;
            }


            $bookmarks  = array_merge($bookmarks,$curent_books);
        }

        return $bookmarks;
    }
    
    /**
     *
     * Applies a rule to a role of a project
     *
     * @param integer $projectId the id of the project
     * @param integer $instance_id the instance id
     * @param integer $categoryId  the id of the category
     */
    function applyRule($projectId,$instance_id,$categoryId,$roleId,$rule) {
        syslog (LOG_INFO, ">> PluginPhpBB::applyRule($projectId,$instance_id,$categoryId,$roleId,$rule)");

        $returned=-1;

        PluginPhpBBDataHandler::getInstanceData($projectId,$instance_id,$cat_id,$name,$url,$encoding);

        if ( isset ($url) && isset ($categoryId) && isset ($roleId)&& isset ($rule)  )
        {
            $returned = PluginPhpBBInterface::applyRule($url,$categoryId,$roleId,$rule);
        }
        else
        {
            syslog(LOG_ERR, ">>   PluginPhpBB::applyRule verify the parameter");
        }

        syslog (LOG_INFO, "<<  PluginPhpBB::applyRule return $returned");
        return ($returned);
    }
    
    /**
     * Active the plugin for a project
     *
     * @param $projetId the id of the project to be activated
     */
    function activatePlugin($projectId,$instance_id) {

    }
    
    /**
     * Synchronize data between NovaFroge and PhpBB
     */
    function synchronize() {
        NovaForgeHandler::getActivedProjets('phpbb',$arr_project_id,$arr_project_name);

        foreach($arr_project_id as $project_id){
            $arr_instance_id = $arr_category_id =$arr_name = $arr_url = $arr_encoding = array();

            PluginPhpBBDataHandler::getInstances($project_id,$arr_instance_id,$arr_category_id,$arr_name,$arr_url,$arr_encoding);
            foreach($arr_instance_id as $instance_id){
                PluginPhpBB::_synchronizeInstance($project_id,$instance_id);
            }
        }
    }
    
    /**
     * Synchronize an instance
     *
     * @param $projetId the id of the project to be activated
     * @param integer $instance_id the instance id
     */
    function _synchronizeInstance($projectId,$instance_id) {
        syslog (LOG_INFO, ">> PluginPhpBB::_synchronizeInstance($projectId,$instance_id) ");

         
        PluginPhpBBDataHandler::getInstanceData($projectId,$instance_id,$phpbb_category_id,$name,$url,$encoding);
        $group = &group_get_object($projectId);
        $projectName = $group->getPublicName();
        
        //Tests category by ID 
        $phpbb_category_id = PluginPhpBBInterface::existsCategoryID($url,$phpbb_category_id,$phpbb_category_name);
        if($phpbb_category_id < 1 ){//don't exists
            //Tests category by Name
            $phpbb_category_id = PluginPhpBBInterface::existsCategoryName($url,0,$projectName);
            if($phpbb_category_id < 1 ){
                $phpbb_category_id = PluginPhpBB::createCategory($projectId,$instance_id,0,$projectName);
            }
        }else{//exists
            if( trim($projectName) != trim($phpbb_category_name) ){
                PluginPhpBB::renameCategory($projectId,$instance_id,$phpbb_category_id,$projectName);

            }
        }
        
        PluginPhpBBDataHandler::updateInstance($projectId,$instance_id ,$phpbb_category_id);
        if($phpbb_category_id > 0){

            //Get back admin phpbb group id
            $admins_role_phpbb_id = PluginPhpBBInterface::existsGroup($url,PluginPhpBB::_getGroupName($projectId,'admins'));
            //if the admin phpbb group does not exists , create a new one.
            if($admins_role_phpbb_id < 1 ){
                $admins_role_phpbb_id = PluginPhpBBInterface::createGroup($url,PluginPhpBB::_getGroupName($projectId,'admins'));
            }
            //Apply the default administration rules for the group
            PluginPhpBB::applyRule($projectId,$instance_id,$phpbb_category_id,$admins_role_phpbb_id,PluginPhpBB::_getDefaultAdminRule());


            PluginPhpBB::_synchronizeUsers($projectId,$instance_id);
            PluginPhpBB::_synchronizeRoles($projectId,$instance_id);
             
        }else{
            syslog(LOG_ERR, ">>  PluginPhpBB::_synchronizeInstance | can not create new category: $phpbb_category_id ");
            return false;
        }

    }
    
    /**
     * Synchronize roles 
     *
     * @param $projetId the id of the project to be activated
     * @param integer $instance_id the instance id
     */
    function _synchronizeRoles($projectId,$instance_id){
        syslog (LOG_INFO, ">> PluginPhpBB::_synchronizeRoles($projectId,$instance_id) ");
        PluginPhpBBDataHandler::getInstanceData($projectId,$instance_id,$cat_id,$name,$url,$encoding);
        $group = &group_get_object($projectId);

        $arr_roles_id = array();
        $arr_roles_phpbb = array();

        NovaForgeHandler::getProjectsRoles($projectId,$arr_roles_id,$arr_roles_name);
        
       
        foreach($arr_roles_id as  $key => $role_id){
            $phpbb_role_id = PluginPhpBBInterface::existsGroup($url,PluginPhpBB::_getGroupName($projectId,$role_id));
            syslog (LOG_INFO, ">> PluginPhpBB::_synchronizeRoles exists  $phpbb_role_id ");
            //Checks if the phpbb group is already created
            if($phpbb_role_id > 0 ){
                $role_id_phpbb = $phpbb_role_id;
                PluginPhpBBInterface::removeAllUsers($url,$phpbb_role_id);
            }else{
                $role_id_phpbb = PluginPhpBBInterface::createGroup($url,PluginPhpBB::_getGroupName($projectId,$role_id));
                //Apply the rule to the phpbb Group
                PluginPhpBB::applyRule($projectId,$instance_id,$cat_id,$role_id_phpbb,PluginPhpBB::_getDefaultRule());
            }
            $arr_roles_phpbb[] = $role_id_phpbb;


            //Add the role users to phpbb Group.
            $array_user_name = array();
            NovaForgeHandler::getProjectUsers($projectId,$role_id,$array_user_id,$array_user_name);
            foreach($array_user_name as $user_name ){
                $user_id = PluginPhpBBInterface::existsUser($url,$user_name);
                if($user_id > 0){
                    PluginPhpBBInterface::addUserToGroup($url,$role_id_phpbb,$user_name);
                }
            }
        }
        PluginPhpBBDataHandler::setRoles($instance_id,$arr_roles_id,$arr_roles_phpbb);
    }
    
    /**
     * Synchronize users
     *
     * @param $projetId the id of the project to be activated
     * @param integer $instance_id the instance id
     */
    function _synchronizeUsers($projectId,$instance_id){
        syslog (LOG_INFO, ">> PluginPhpBB:: _synchronizeUsers($projectId,$instance_id) ");
        PluginPhpBBDataHandler::getInstanceData($projectId,$instance_id,$cat_id,$name,$url,$encoding);

        $group   = &group_get_object($projectId);
        $members = &$group->getMembers();
        $members[] = user_get_object_by_name('admin');

        foreach($members as $aUser){
            $user_id = PluginPhpBBInterface::existsUser($url,$aUser->getUnixName());

            if($user_id < 1){
                PluginPhpBBInterface::createUser($url,$aUser->getUnixName(),$aUser->getEmail(),$aUser->getMD5Passwd());
                 
                syslog (LOG_INFO, ">> PluginPhpBB::_synchronizeUsers :createUser($url,".$aUser->getUnixName()."");
            }
            
            
            $perm = &$group->getPermission ($aUser);
            if($perm->isAdmin()){
                
                $admins_role_phpbb_id = PluginPhpBBInterface::existsGroup($url,PluginPhpBB::_getGroupName($projectId,'admins'));
                if($admins_role_phpbb_id > 0){
                    PluginPhpBBInterface::addUserToGroup($url,$admins_role_phpbb_id,$aUser->getUnixName());
                }else{
                    syslog (LOG_ERR, ">> PluginPhpBB:: _synchronizeUsers  can't getback the admin group user id ");
                }
            }

        }
    }

    /**
     * Get the name of the group to be subscribed to phpbb
     * @return string default rule
     */
    function _getGroupName($projectId,$role_id){
        return "NovaForge_".$projectId.'_'.$role_id;
    }

    /**
     * Get the default rule
     * @return string default rule
     */
    function _getDefaultRule(){
        /*
        14  ROLE_FORUM_FULL             Full Access
        15  ROLE_FORUM_STANDARD         Standard Access
        16  ROLE_FORUM_NOACCESS         No Access
        17  ROLE_FORUM_READONLY         Read Only Access
        18  ROLE_FORUM_LIMITED          Limited Access
        19  ROLE_FORUM_BOT              Bot Access
        20  ROLE_FORUM_ONQUEUE          On Moderation Queue
        21  ROLE_FORUM_POLLS            Standard Access + Polls
        22  ROLE_FORUM_LIMITED_POLLS    Limited Access + Polls
        */
        return 15;
    }

    /**
     * Get the administrators default rule
     * @return string administrators default rule
     */
    function _getDefaultAdminRule(){
        /*
        14  ROLE_FORUM_FULL             Full Access
        15  ROLE_FORUM_STANDARD         Standard Access
        16  ROLE_FORUM_NOACCESS         No Access
        17  ROLE_FORUM_READONLY         Read Only Access
        18  ROLE_FORUM_LIMITED          Limited Access
        19  ROLE_FORUM_BOT              Bot Access
        20  ROLE_FORUM_ONQUEUE          On Moderation Queue
        21  ROLE_FORUM_POLLS            Standard Access + Polls
        22  ROLE_FORUM_LIMITED_POLLS    Limited Access + Polls
        */
        return 14; //ROLE_FORUM_FULL
    }

}


?>
