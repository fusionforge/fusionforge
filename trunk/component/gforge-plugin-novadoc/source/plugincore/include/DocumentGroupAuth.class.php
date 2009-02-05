<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once ('common/include/Error.class.php');
require_once ('plugins/novadoc/include/DocumentConfig.class.php');

/*
 * Authorizations :
 *   1 none
 *   2 read
 *   3 write
 *   4 write + delete  
 */
class DocumentGroupAuth extends Error {
    var $group_id;
    var $role_id;

    var $tab_auth;          /* array of authorization by doc group */    
    var $default_auth;      /* default auth for the role */
    var $isAdmin;           /* true if user is superadmin or an admin of the projet */
    var $groupIsPublic;     /* true if the group is public (all user have the read auth */
    
    function DocumentGroupAuth( $group_id, &$LUSER ){
        $this->group_id = $group_id;
        $this->role_id = novadoc_getUserRoleId ($group_id, $LUSER);
        $g =& group_get_object ($group_id);
        $perm =& $g->getPermission ($LUSER);
        if( $perm && $perm->isError() && ( $perm->isSuperUser()) || $perm->isAdmin()  ){
	        $this->isAdmin = true;
        }else{
	        $this->isAdmin = false;
        }
        if( $g->isPublic() ){
            $this->groupIsPublic = true;
        }else{
            $this->groupIsPublic = false;
        }
        $this->default_auth = $this->getDefaultAuth();
        if( $this->role_id != false ){
            $this->fill_tab_auth();
        }
    }


    function fill_tab_auth(){
        if( ! $this->role_id ){
            $this->tab_auth = null;
        }
        
        /*
         $sql = "SELECT g.doc_group, g.parent_doc_group, a.auth 
                    FROM plugin_docs_doc_groups g
                    LEFT JOIN role r ON r.group_id = g.group_id
                    LEFT JOIN  plugin_docs_doc_authorization a ON a.doc_group = g.doc_group 
                    WHERE g.group_id = " . $this->group_id . "
                    AND r.role_id = " . $this->role_id;
        */

         $sql = " SELECT g.doc_group, g.parent_doc_group, a.auth, a.role_id
                    FROM plugin_docs_doc_groups g 
                    LEFT JOIN plugin_docs_doc_authorization a ON a.doc_group = g.doc_group 
                    WHERE g.group_id = " . $this->group_id . "
                    AND a.role_id IS NULL OR a.role_id = " . $this->role_id;


		$result=db_query($sql);
		
		if(  !$result ){
		    $this->setError( 'DocumentGroupAuth::fill_tab_auth() : ' . db_error() );
		    return false;
		} 

        $this->tab_auth = array();
        while( $v =& db_fetch_array($result) ){
            //$this->tab_auth[ $v['parent_doc_group'] ][] = $v;
            $this->tab_auth[ $v['doc_group'] ] = $v;
        }
		return true;
    }

    
    function getAllAuth( $group_id ){
        $sql = "SELECT g.doc_group, a.role_id, a.auth 
                    FROM plugin_docs_doc_groups g 
                    JOIN plugin_docs_doc_authorization a     
                    ON a.doc_group = g.doc_group 
                    WHERE g.group_id = " . $group_id;

		$result=db_query($sql);
		if(  !$result ){
		    // $this->setError( 'DocumentGroupAuth::fill_tab_auth() : ' . db_error() );
		    return false;
		} 

        $tabAuh = array();
        while( $v =& db_fetch_array($result) ){
            if( !isset( $tabAuh[ $v['doc_group'] ] ) ){
                $tabAuh[ $v['doc_group'] ] = array();
            }
            $tabAuh[ $v['doc_group'] ][ $v['role_id'] ] = $v;
        }
		return $tabAuh;        
    }
    
    
    function getDefaultAuth(){
        $sql = " SELECT role_name FROM role WHERE role_id = " . $this->role_id;

		$result=db_query($sql);
		if(  !$result ){
		    $this->setError( 'DocumentGroupAuth::getDefaultAuth() : ' . db_error() );
		    return false;
		} 
        
        $role = db_fetch_array($result);
        $role_name = $role['role_name'];
        
        return $this->getDefaultAuthByRoleName( $role_name );
    }
    
    
    function getDefaultAuthByRoleName( $role_name ){
        $config = DocumentConfig::getInstance();
        

        if( isset( $config->defaultAuthorizationRole[$role_name] ) ){
            return $config->defaultAuthorizationRole[$role_name];
        }else{
            return $config->defaultAuthorization;
        }
    }
    
    
    function hasDirectAuth( $doc_gourp ){
        if( $this->tab_auth == null ) return false;
        
        if( isset( $this->tab_auth[ $doc_group ] ) ){
            $this->setError( 'Error, doc_group invalid : ' . $doc_group );
		    return false;
        }
        
        $auth = $this->tab_auth[ $doc_group ]['auth'];
        
        return $auth != null;
    }
    
    
    function getAuth( $doc_group ){
        // not logging
        if( $this->isAdmin ) return 4; /* admin have all right */
        if( $this->role_id === false ) return 0;
        if( $this->tab_auth == null ) return 0;

        if( !isset( $this->tab_auth[ $doc_group ] ) ){
            $auth = null;
        }else{
            $auth = $this->tab_auth[ $doc_group ]['auth'];
        }
        if( $auth == null ){
            // must see the auth of the parent
            if( isset( $this->tab_auth[ $doc_group ] ) ){
                $parent_doc = $this->tab_auth[ $doc_group ]['parent_doc_group'];
            }else{
                $parent_doc = 0;
            }
            
            if( $parent_doc == 0 ){
                // no parent, return default auth
                return $this->default_auth;
            }else{
                return $this->getAuth( $parent_doc );
            }
        }else{
            return $auth;
        }
    }


    /**
     * $auth_arr : 
     *  array of [doc_group] => [role_id] => authorization
     */
    function setAuth( $group_id, $auth_arr ){
        db_begin();
        
        $sql = "    DELETE FROM plugin_docs_doc_authorization 
                        WHERE doc_group IN( 
                            SELECT doc_group FROM plugin_docs_doc_groups WHERE group_id = '$group_id'
                        ) ";

		$result=db_query($sql);
		if(  !$result ){
		    db_rollback();
		    return false;
		} 


        $sqlBegin = " INSERT INTO plugin_docs_doc_authorization(doc_group,role_id,auth) VALUES ";
        
        foreach( $auth_arr as $doc_group => $arr_roles ){
            foreach( $arr_roles as $role_id => $auth ){
                if( $auth!=0 ){
                    $sql = $sqlBegin . "( '$doc_group', '$role_id', '$auth'  )";
            		$result=db_query($sql);
            		if(  !$result ){
            		    db_rollback();
            		    return false;
            		} 
            		
                }
            }
        }
        
        db_commit();
        return true;
    }


    function canRead( $doc_group ){
        if( $this->groupIsPublic ) return true;
       
        
        $auth = $this->getAuth( $doc_group );
        if( $auth === false ) return false;

        return $auth >= 2;
    }    

    function canWrite( $doc_group ){
        $auth = $this->getAuth( $doc_group );
        if( $auth === false ) return false;
        
        return $auth >= 3;
    }    

    function canDelete( $doc_group ){
        $auth = $this->getAuth( $doc_group );
        if( $auth === false ) return false;
        
        return $auth >= 4;
    }    
    

    
}

?>
