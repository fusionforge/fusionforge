<?php
define('DEFAULT_ENCODING','utf-8');
/**
 * PhpBB plugin
 * 
 * This class handles the plugin's data
 *
 * @author Panneer CANABADY <panneerselvam.canabady@bull.net>
 * @version 1.0
 * @package PluginPhpBB
 */
class PluginPhpBBDataHandler{

    /**
     *
     */
    function createInstance($gforge_group_id,&$plugin_phpbb_instance_id,$phpbb_category_id,$name,$url,$encoding){
        syslog(LOG_INFO, ">> PluginPhpBBDataHandler::createInstance($gforge_group_id,$phpbb_category_id,$name,$url,$encoding)");

        if(isset($gforge_group_id) && !empty($gforge_group_id) &&
        isset($name) && !empty($name) &&
        isset($url) && !empty($url) &&
        isset($encoding) && !empty($encoding)
        ){

            $sql = "INSERT INTO plugin_phpbb_instance(gforge_group_id,phpbb_category_id,name,url,encoding) VALUES('"
            .$gforge_group_id."','"
            .$phpbb_category_id."','"
            .trim($name)."','"
            .trim($url)."','"
            .trim($encoding)
            ."')";


            $result = db_query($sql);
            if( !$result || db_affected_rows($result) < 1){
                syslog (LOG_ERR, "<< PluginPhpBBDataHandler::createInstance : Sql Failed : ".db_error() );
                return false;
            }

            $plugin_phpbb_instance_id = db_insertid($result,'plugin_phpbb_instance','plugin_phpbb_instance_id');

            if (!$plugin_phpbb_instance_id){
                syslog (LOG_ERR, ">> PluginPhpBBDataHandler::createInstance : Sql failed to get inserted id ");
                return false;
            }

            return true;

        }else{
            syslog(LOG_ERR,"<< PluginPhpBBDataHandler::createInstance argument error ");
            return false;
        }
    }

    /**
     *
     */
    function getInstances($gforge_group_id,&$plugin_phpbb_instance_id,&$phpbb_category_id,&$name,&$url,&$encoding){
        syslog (LOG_INFO, ">>PluginPhpBBDataHandler::getInstance($gforge_group_id)");

        if(isset($gforge_group_id) && !empty($gforge_group_id)){

            $sql = "SELECT * FROM plugin_phpbb_instance WHERE gforge_group_id = '".$gforge_group_id."' ";
            $result=db_query($sql);
            if( !$result){
                syslog (LOG_ERR, "<< PluginPhpBBDataHandler::getInstance : Sql Failed : ".db_error() );
                return false;
            }

            while( $data =& db_fetch_array($result) ){
                $plugin_phpbb_instance_id[] = $data['plugin_phpbb_instance_id'];
                $phpbb_category_id[] =$data['phpbb_category_id'];
                $name[] = $data['name'];
                $url[] = $data['url'];
                $encoding[] = $data['encoding'];
            }
            return true;

        }else{
            syslog(LOG_ERR,"<< PluginPhpBBDataHandler::getInstance argument error ");
            return false;
        }
    }

    /**
     *
     */
    function getInstanceData($gforge_group_id,$plugin_phpbb_instance_id,&$phpbb_category_id,&$name,&$url,&$encoding){
        syslog (LOG_INFO, ">>getInstanceData($gforge_group_id,$plugin_phpbb_instance_id)");

        if(isset($gforge_group_id) && !empty($gforge_group_id) &&
        isset($plugin_phpbb_instance_id) && !empty($plugin_phpbb_instance_id)
        ){
            $sql = "SELECT * FROM plugin_phpbb_instance WHERE gforge_group_id = ".$gforge_group_id." ";
            $sql.=" AND plugin_phpbb_instance_id = ".$plugin_phpbb_instance_id." ";

            $result=db_query($sql);
            if(  !$result ){
                syslog (LOG_ERR, "<< PluginPhpBBDataHandler::getInstanceData : Sql Failed : ".db_error() );
                return false;
            }

            while( $data =& db_fetch_array($result) ){
                $phpbb_category_id =$data['phpbb_category_id'];
                $name = $data['name'];
                $url = $data['url'];
                $encoding = $data['encoding'];
            }
            return true;
        }else{
            syslog(LOG_ERR,"<< PluginPhpBBDataHandler::getInstanceData argument error ");
            return false;
        }

    }

    /**
     *
     */
    function getPhpBBInstances ($gforge_group_id,&$array_phpbb_instance_id,&$array_phpbb_url, &$array_phpbb_name){
        syslog (LOG_INFO, ">>PluginPhpBBDataHandler::getPhpBBInstances ($gforge_group_id)");

        if(isset($gforge_group_id) && !empty($gforge_group_id) ){
            $sql = "SELECT * FROM plugin_phpbb_instance WHERE gforge_group_id = '".$gforge_group_id."' ";
            $result=db_query($sql);

            if(!$result){
                syslog (LOG_ERR, "<< PluginPhpBBDataHandler::getPhpBBInstances : Sql Failed : ".db_error() );
                return false;
            }

            while( $data =& db_fetch_array($result) ){
                $array_phpbb_instance_id[] = $data['plugin_phpbb_instance_id'];
                $array_phpbb_url[] = $data['url'];
                $array_phpbb_name[] = $data['name'];
            }
            return true;
        }else{
            syslog(LOG_ERR,"<< PluginPhpBBDataHandler::getPhpBBInstances argument error ");
            return false;
        }

    }

    /**
     *
     */
    function updateInstance($gforge_group_id,$plugin_phpbb_instance_id ,$phpbb_category_id = '',$name = '',$url = '',$encoding = ''){
        syslog (LOG_INFO, ">> PluginPhpBBDataHandler::updateInstance($gforge_group_id,$plugin_phpbb_instance_id ,$phpbb_category_id,$name,$url,$encoding)");

        if(isset($gforge_group_id) && !empty($gforge_group_id) &&
        isset($plugin_phpbb_instance_id) && !empty($plugin_phpbb_instance_id)
        ){
            $sql  = "UPDATE plugin_phpbb_instance SET ";

            $attribs = array(
            "phpbb_category_id" => trim($phpbb_category_id),
            "name" => trim($name),
            "url" => trim($url),
            "encoding" => trim($encoding)
            );

            foreach($attribs as $key => $value){
                if(!isset($value) || empty($value)){
                    unset($attribs[$key]);
                }
            }
            $nbAttribs = count($attribs);
            if($nbAttribs <1 ){
                return true;
            }
            foreach($attribs as $key => $value){
                $sql .=" {$key} = '{$value}' ,";
            }
            $sql = substr($sql,0,-1);



            $sql .=" WHERE gforge_group_id = '".$gforge_group_id."' AND ";
            $sql .="       plugin_phpbb_instance_id = '".$plugin_phpbb_instance_id."'  ";

            $result = db_query($sql);

            if(  !$result || db_affected_rows($result) < 1 ){
                syslog (LOG_ERR, "<< PluginPhpBBDataHandler::updateInstance : Sql Failed : ".db_error() );
                return false;
            }
            return true;
        }else{
            syslog(LOG_ERR,"<< PluginPhpBBDataHandler::updateInstance argument error ");
            return false;
        }
    }

    /**
     *
     */
    function removeInstance($gforge_group_id,$plugin_phpbb_instance_id){
        syslog (LOG_INFO, ">>PluginPhpBBDataHandler::removeInstance($gforge_group_id,$plugin_phpbb_instance_id)");

        if(isset($gforge_group_id) && !empty($gforge_group_id) &&
        isset($plugin_phpbb_instance_id) && !empty($plugin_phpbb_instance_id)
        ){
            $sql = "DELETE FROM plugin_phpbb_instance WHERE gforge_group_id = ".$gforge_group_id." ";
            $sql.= "AND plugin_phpbb_instance_id = ".$plugin_phpbb_instance_id." ";

            $result=db_query($sql);
            if(  !$result || db_affected_rows($result) < 1 ){
                syslog (LOG_ERR, "<< PluginPhpBBDataHandler::removeInstance : Sql Failed : ".db_error() );
                return false;
            }
            return true;
        }else{
            syslog(LOG_ERR,"<< PluginPhpBBDataHandler::removeInstance argument error ");
            return false;
        }

    }

    /**
     *
     */
    function getRoles($plugin_phpbb_instance_id, &$array_gforge_role_id, &$array_phpbb_role_id){
        syslog (LOG_INFO, ">>PluginPhpBBDataHandler::getRoles($plugin_phpbb_instance_id)");

        if(isset($plugin_phpbb_instance_id) && !empty($plugin_phpbb_instance_id)){
            $sql = "SELECT * FROM plugin_phpbb_role WHERE plugin_phpbb_instance_id = ".$plugin_phpbb_instance_id." ";

            $result=db_query($sql);
            if(!$result){
                syslog (LOG_ERR, "<< PluginPhpBBDataHandler::getRoles: Sql Failed : ".db_error() );
                return false;
            }

            while( $data =& db_fetch_array($result) ){
                $array_gforge_role_id[] = $data['gforge_role_id'];
                $array_phpbb_role_id[] = $data['phpbb_role_id'];
            }

            return true;
        }else{
            syslog(LOG_ERR,"<< PluginPhpBBDataHandler::getRoles argument error ");
            return false;
        }
    }

    function getPhpBBRole($plugin_phpbb_instance_id, $role_id, &$phpbb_role_id){
        syslog (LOG_INFO, ">>PluginPhpBBDataHandler::getPhpBBRole($plugin_phpbb_instance_id, $role_id)");

        if(isset($plugin_phpbb_instance_id) && !empty($plugin_phpbb_instance_id)){
            $sql  = " SELECT phpbb_role_id FROM plugin_phpbb_role ";
            $sql .= " WHERE plugin_phpbb_instance_id = ".$plugin_phpbb_instance_id." ";
            $sql .= " AND gforge_role_id = ".$role_id." ";

            $result=db_query($sql);
            if(!$result){
                syslog (LOG_ERR, "<< PluginPhpBBDataHandler::getPhpBBRole: Sql Failed : ".db_error() );
                return false;
            }

            $data =& db_fetch_array($result);
            if($data){
                $phpbb_role_id = $data['phpbb_role_id'];
            }else{
                $phpbb_role_id = 0;
                return false;
            }
             

            return true;
        }else{
            syslog(LOG_ERR,"<< PluginPhpBBDataHandler::getPhpBBRole argument error ");
            return false;
        }
    }

    /**
     *
     */
    function setRoles($plugin_phpbb_instance_id, $array_gforge_role_id, $array_phpBB_role_id){
        $debug_array_gforge_role_id = var_export($array_gforge_role_id,true);
        $debug_array_phpBB_role_id = var_export($array_phpBB_role_id,true);
        syslog (LOG_INFO, ">> PluginPhpBBDataHandler::setRoles($plugin_phpbb_instance_id, ".var_export($array_gforge_role_id, true).", ".var_export($array_phpBB_role_id, true)." ");

        $returned = true;

        if(isset($plugin_phpbb_instance_id) && !empty($plugin_phpbb_instance_id) &&
        is_array($array_gforge_role_id)  && is_array($array_phpBB_role_id)&&
        count($array_gforge_role_id) == count($array_phpBB_role_id) && count($array_gforge_role_id) > 0
        ){
            
            //Delete wrong values
            foreach($array_gforge_role_id as $key => $value){
                if(!isset($value) || empty($value) || !is_numeric($value) || ($value < 1) ){
                    unset($array_gforge_role_id[$key]);
                    unset($array_phpBB_role_id[$key]);
                }
            }
            reset($array_gforge_role_id);
          
            //array to SQL string to fit into SQL IN function
            $SQLstring = "";
            foreach($array_gforge_role_id as $item)
            {
                $SQLstring .= "'$item',";
            }
            reset($array_gforge_role_id);
            $SQLstring = rtrim($SQLstring, ",");
            $SQLstring = str_replace("'',", "", $SQLstring);

            //BEGIN Deleteing old role id
            $sql  = "DELETE FROM plugin_phpbb_role WHERE gforge_role_id IN($SQLstring) AND plugin_phpbb_instance_id = '{$plugin_phpbb_instance_id}' ";

            
            db_begin();
            $result = db_query($sql);
            
            if(  !$result ){
                db_rollback();
                syslog (LOG_ERR, "<<  PluginPhpBBDataHandler::setRoles request 1: Sql Failed : ".db_error() );
                $returned =  false;
            }

            //BEGIN Inserting new role id
            reset($array_gforge_role_id);reset($array_phpBB_role_id);
            while ( (list($key, $value) = each($array_gforge_role_id)) &&  $returned ) {

                $sql  = "INSERT INTO plugin_phpbb_role(plugin_phpbb_instance_id,gforge_role_id,phpbb_role_id) ";
                $sql .= "VALUES($plugin_phpbb_instance_id,$value, {$array_phpBB_role_id[$key]} ) ";
              
                $result = db_query($sql);
                if(  !$result || db_affected_rows($result) < 1 ){
                    db_rollback();
                    syslog (LOG_ERR, "<<  PluginPhpBBDataHandler::setRoles request 2: Sql Failed : ".db_error() );
                    $returned =  false;
                }
            }

            if($returned){
                db_commit();
            }
        }else{
            syslog(LOG_ERR,"<< PluginPhpBBDataHandler::setRoles argument error ");
            $returned = false;
        }

        return $returned;
    }

}
?>
