<?php
/**
 * PhpBB plugin
 * 
 * Additional API for GForge
 *
 * @author Panneer CANABADY <panneerselvam.canabady@bull.net>
 * @version 1.0
 * @package PluginPhpBB
 */
class NovaForgeHandler{

    /**
     * Supplies a set of actived projects for a specific plug-in.
     *
     * @param string $pluginName the name of the plug-in
     * @param array a set of projects (class Project)
     */
    function getActivedProjets ($pluginName,&$array_project_id,&$array_project_name){
        syslog(LOG_INFO,">> NovaForgeHandler::getActivedProjets ($pluginName) ");

        if (isset($pluginName) && !empty($pluginName))
        {
            $query  = " SELECT gp.group_id,g.group_name ";
            $query .= " FROM plugins p,group_plugin gp, groups g ";
            $query .= " WHERE p.plugin_name='$pluginName' ";
            $query .= " AND gp.plugin_id=p.plugin_id ";
            $query .= " AND gp.group_id=g.group_id ";

            $result = db_query($query);

            if ($result &&  db_numrows($result) > 0) {
                while( $data =& db_fetch_array($result) ){
                    $array_project_id[] = $data['group_id'];
                    $array_project_name[] = $data['group_name'];
                }
            }else{
                syslog (LOG_ERR, "<<  NovaForgeHandler::getActivedProjets: Sql Failed : ".db_error() );
                return false;
            }

        }else{
            syslog(LOG_ERR,">>NovaForgeHandler::getActivedProjets Argument error ");
            return false;
        }
        return true;
    }

    /**
     * Supplies a set of Roles attached for a given project
     *
     * @param integer $projetId project id
     * @param array  a set of roles (class Role)
     */
    function getProjectsRoles($projetId,&$array_roles_id,&$array_roles_name){
        syslog(LOG_INFO,">> NovaForgeHandler::getProjectsRoles($projetId)");
         
        if (isset($projetId) && !empty($projetId) )
        {
            $query  = " SELECT role_id,role_name ";
            $query .= " FROM role ";
            $query .= " WHERE group_id= '$projetId' ";

            $result = db_query($query);

            if ($result)
            {
                while( $data =& db_fetch_array($result) ){
                    $array_roles_id[] = $data['role_id'];
                    $array_roles_name[] = $data['role_name'];
                }
            }else{
                syslog (LOG_ERR, "<<  NovaForgeHandler::getProjectsRoles: Sql Failed : ".db_error() );
                return false;
            }
        }
        else
        {
            syslog(LOG_ERR,">>NovaForgeHandler::getActivedProjets Argument error ");
            return false;
        }
        return true;
    }

    /**
     * Supplies a set of users attached in a role of a project
     *
     * @param integer $projectId project id
     * @param integer $roleId role id
     */
    function getProjectUsers($projectId,$roleId,&$array_user_id,&$array_user_name){
        syslog(LOG_INFO,">> NovaForgeHandler::getProjectUsers($projectId,$roleId)");
         
        if (isset($projectId) && !empty($projectId) &&
        isset($roleId) && !empty($roleId) )
        {
            $query =  " SELECT u.user_id,u.user_name ";
            $query .= " FROM user_group ug ,users u ";
            $query .= " WHERE ug.user_id = u.user_id ";
            $query .= " AND ug.group_id= '$projectId' AND ";
            $query .= " ug.role_id= '$roleId' ";

            $result = db_query($query);

            if ($result)
            {
                while( $data =& db_fetch_array($result) ){
                    $array_user_name[] = $data['user_name'];
                    $array_user_id[] = $data['user_id'];
                }
            }else{
                syslog (LOG_ERR, "<<   NovaForgeHandler::getProjectUsers: Sql Failed : ".db_error() );
                return false;
            }
        }
        else
        {
            syslog(LOG_ERR,">>NovaForgeHandler::getProjectUsers Argument error ");
            return false;
        }
        return true;
    }

}
?>
