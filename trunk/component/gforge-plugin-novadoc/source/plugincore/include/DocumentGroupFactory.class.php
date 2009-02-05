<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: DocumentGroupFactory.class.php,v 1.4 2006/10/27 18:11:31 pascal Exp $
 *
 * This file is part of GForge.
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
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/*
	Document Groups
*/

require_once ("common/include/Error.class.php");
require_once ("plugins/novadoc/include/DocumentGroupDocs.class.php");
require_once ("plugins/novadoc/include/DocumentConfig.class.php");
require_once ("plugins/novadoc/include/DocumentGroupAuth.class.php");

class DocumentGroupFactory extends Error {
	/**
	 * This variable holds the document groups
	 */
	var $flat_groups;

	/**
	 * This variable holds the document groups for reading them in nested form
	 */
	var $nested_groups;

	/**
	 * The Group object
	 */
	var $Group;

	/**
	 *  Constructor.
	 *
	 *	@return	boolean	success.
	 */
	function DocumentGroupFactory(&$Group) {
		$this->Error ();
		if (!$Group || !is_object($Group)) {
			$this->setError("DocumentGroupFactory:: Invalid Group");
			return false;
		}
		if ($Group->isError()) {
			$this->setError('DocumentGroupFactory:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;


		return true;
	}

	/**
	 *	getNested - Return an array of DocumentGroup objects arranged for nested views.
	 *
	 *	@return	array	The array of DocumentGroup.
	 */
	function &getNested( $stateid_filter=0 ){
		if ($this->nested_groups) {
			return $this->nested_groups;
		}
		
		global $group_id;
		global $LUSER;
		$auth = new DocumentGroupAuth( $group_id, $LUSER );
		
		$sql=" SELECT * FROM plugin_docs_doc_groups
		        WHERE group_id='".$this->Group->getID()."' ";
		
		if( $stateid_filter !== null ){
		    $sql .= " AND stateid = '$stateid_filter' ";
		}
		 
		$sql .= "ORDER BY groupname ASC";

		$result=db_query($sql);
		$rows = db_numrows($result);
		
		if (!$result || $rows < 1) {
			$this->setError('No Groups Found '.db_error());
			return false;
		} else {
			while ($arr = db_fetch_array($result)) {
			    if( $auth->canRead( $arr['doc_group'] ) ){
				    $this->flat_groups[] = new DocumentGroupDocs($this->Group, $arr);
				}
			}
		}
		
		
		$config = DocumentConfig::getInstance();
		$inv = $config->level0inv;
		
		// Build the nested array
		$level0 = array();
		$count = count($this->flat_groups);
		for ($i=0; $i < $count; $i++) {
		    $parent = "".$this->flat_groups[$i]->getParentID();
		    if( !$inv or $parent ){
		        $this->nested_groups[$parent][] =& $this->flat_groups[$i];
		    }else{
		        $level0[$this->flat_groups[$i]->getID()] =& $this->flat_groups[$i];
		    }
		}
		
		if( $inv ){
            krsort( $level0 );
		    foreach( $level0 as $g0 ){
    		    $this->nested_groups[0][] = $g0;
		    }
		}
		
		return $this->nested_groups;

	}
		/**
	 *	getDocumentGroups - Return an array of DocumentGroup objects.
	 *
	 *	@return	array	The array of DocumentGroup.
	 */
	function &getDocumentGroups() {
		if ($this->flat_groups) {
			return $this->flat_groups;
		}
		
		$sql="SELECT * FROM plugin_docs_doc_groups
		WHERE group_id='".$this->Group->getID()."' 
		ORDER BY groupname ASC";

		$result=db_query($sql);
		$rows = db_numrows($result);
		
		if (!$result || $rows < 1) {
			$this->setError('No Groups Found '.db_error());
			return false;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->flat_groups[] = new DocumentGroupDocs($this->Group, $arr);
			}
		}
		

		
		return $this->flat_groups;

	}
	
	
	/**
	 * Copy a branch (arborescence, not document) in a  new branch
	 * 
	 */
	function copyArborescence( $doc_group_source, $newBranchName ){
	    $newGroup = new DocumentGroupDocs( $this->Group );
	    if( $newGroup->create($newBranchName,0) == false ){
	        $this->setError('DocumentGroupFactory::copyGroupBranch() Error Adding Group: '.db_error());
			return false;
	    }
        if( ! $this->copyAuth( $doc_group_source, $newGroup->getID() ) ){
            return false;
        }
	    return $this->copyArboRecursive( $doc_group_source, $newGroup->getID() );
	}


    function copyArboRecursive( $doc_group_source, $doc_group_dest ){
        $this->getNested();
        
        if( isset( $this->nested_groups[ $doc_group_source ] ) ){
            foreach( $this->nested_groups[ $doc_group_source ] as $group ){
                $newGroup = new DocumentGroupDocs( $this->Group );
                if( !$group->isDeleted() ){
                    if( $newGroup->create( addslashes( $group->getName() ), $doc_group_dest) == false ){
    	                $this->setError('DocumentGroupFactory::copyGroupBranch() Error Adding Group: '.db_error());
    			        return false;
    	            }
    	            if( ! $this->copyAuth( $group->getID(), $newGroup->getID() ) ){
    	                return false;
    	            }
    	            
    	            $result = $this->copyArboRecursive( $group->getID(), $newGroup->getID() );
    	            if( !$result ) return false;
    	        }
            }
        }
        
        return true;
	}


    function copyAuth( $doc_group_source, $doc_group_dest ){
        $sql = " SELECT role_id, auth FROM plugin_docs_doc_authorization
                    WHERE doc_group='$doc_group_source' ";
        
        $res = db_query( $sql );
        if( !$res ){
            $this->setError('DocumentGroupFactory::copyAuth() Error get auth: '.db_error());
            return false;
        }
        
        while( $auth = db_fetch_array( $res ) ){
            
            $role_id = $auth['role_id'];
            $auth_id = $auth['auth'];
            
            
            $sql = " INSERT INTO plugin_docs_doc_authorization( doc_group, role_id, auth )
                        VALUES( '$doc_group_dest', '$role_id', '$auth_id' ) ";
            
            $resInsert = db_query( $sql );
            if( !$resInsert ){
                $this->setError('DocumentGroupFactory::copyAuth() Error set auth: '.db_error());
                return false;
            }
            
        }
        
        return true;
    }

	
	function delete_group( $doc_group ){
	    $nested =& $this->getNested();
	    
	    if( isset( $nested[$doc_group] ) and count($nested[$doc_group])>0 ){
	        foreach( $nested[$doc_group] as $child ){
	            if( !$this->delete_group( $child->getId() ) ){
	                return false;
	            }
	        }
	    }
	    $g = new DocumentGroupDocs( $this->Group, $doc_group );
	    if( !$g->deleteGroupDocs() ){
	        $this->setError( $g->getErrorMessage() );
	        return false;
	    }
	    return true;
	}
	
}

?>
