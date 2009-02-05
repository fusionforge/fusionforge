<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: DocumentGroupDocs.class.php,v 1.9 2006/11/22 10:17:24 pascal Exp $
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
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("common/include/Error.class.php");
require_once ("plugins/novadoc/include/DocumentConfig.class.php");
require_once ("plugins/novadoc/include/utils.php");

class DocumentGroupDocs extends Error {

	/**
	 * The Group object.
	 *
	 * @var		object	$Group.
	 */
	var $Group; //object

	/**
	 * Array of data.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;

	/**
	 *  DocumentGroupDocs - constructor.
	 *
	 *  Use this constructor if you are modifying an existing doc_group.
	 *
	 *	@param	object	Group object.
	 *  @param	array	(all fields from doc_groups) OR doc_group from database.
	 *  @return boolean	success.
	 */
	function DocumentGroupDocs(&$Group, $data=false) {
		$this->Error();

		//was Group legit?
		if (!$Group || !is_object($Group)) {
			$this->setError('DocumentGroupDocs: No Valid Group');
			return false;
		}
		//did Group have an error?
		if ($Group->isError()) {
			$this->setError('DocumentGroupDocs: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
//
//	should verify group_id
//
				return true;
			} else {
				if (!$this->fetchData($data)) {
					return false;
				} else {
					return true;
				}
			}
		}
	}


    function checkUnique( $name, $parent_doc_group, $group_id ){
        global $Language;
        
        $sql = " SELECT groupname FROM plugin_docs_doc_groups
                    WHERE group_id = '$group_id'
                    AND parent_doc_group = '$parent_doc_group'
                    AND stateid = 0
                    AND groupname = '$name' ";

		$result=db_query($sql);
		if(  db_numrows($result) != 0 ){
		    $this->setError( dgettext('gforge-plugin-novadoc','group_unique') );
		    return false;
		} 
		return true;               
    }


	/**
	 *	create - create a new item in the database.
	 *
	 *	@param	string	Item name.
	 *  @return id on success / false on failure.
	 */
	function create($name,$parent_doc_group=0) {
		global $Language;
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(dgettext('gforge-plugin-novadoc','name_required'));
			return false;
		}
		
		if ($parent_doc_group) {
			// check if parent group exists
			$res=db_query("SELECT * FROM plugin_docs_doc_groups WHERE doc_group='$parent_doc_group' AND group_id=".$this->Group->getID());
			if (!$res || db_numrows($res) < 1) {
				$this->setError(dgettext('gforge-plugin-novadoc','invalid_parent_id'));
				return false;
			}
		} else {
			$parent_doc_group = 0;
		}
		

		$perm =& $this->Group->getPermission (session_get_user());
		if (!$perm || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}


		if( !$this->checkUnique( $name, $parent_doc_group, $this->Group->getID() ) ){
		    return false;
		}

		
		$sql="INSERT INTO plugin_docs_doc_groups (group_id,groupname,parent_doc_group)
			VALUES ('".$this->Group->getID()."','".($name)."','".$parent_doc_group."')";
		$result=db_query($sql);
		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
		} else {
			$this->setError('DocumentGroupDocs::create() Error Adding Group: '.db_error());
			return false;
		}

		$doc_group = db_insertid($result, 'plugin_docs_doc_groups', 'doc_group');

		//	Now set up our internal data structures
		if (!$this->fetchData($doc_group)) {
			return false;
		}

		$config = DocumentConfig::getInstance();
		$dirPath = $config->sys_novadoc_path.'/'.$this->Group->getUnixName().'/'.$this->getPath();
		
		if( !is_dir( $dirPath ) ){
			if( !mkdir( $dirPath ) ){
				$this->setError( 'mkdir failed '. $dirPath );
				return false;
			}
		}		

		return true;
	}


	/**
	 *	fetchData - re-fetch the data for this DocumentGroupDocs from the database.
	 *
	 *	@param	int		ID of the doc_group.
	 *	@return boolean.
	 */
	function fetchData($id) {
		global $Language;
		$res=db_query("SELECT * FROM plugin_docs_doc_groups WHERE doc_group='$id'");
		if (!$res || db_numrows($res) < 1) {
			$this->setError(dgettext('gforge-plugin-novadoc','invalid_id'));
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getGroup - get the Group Object this DocumentGroupDocs is associated with.
	 *
	 *	@return Object Group.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getID - get this DocumentGroupDocs's ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['doc_group'];
	}
	
	/**
	 *	getID - get parent DocumentGroupDocs's id.
	 *
	 *	@return	int	The id #.
	 */
	function getParentID() {
		return $this->data_array['parent_doc_group'];
	}

	/**
	 *	getName - get the name.
	 *
	 *	@return	String	The name.
	 */
	function getName() {
		return $this->data_array['groupname'];
	}

    /**
     * Return true if the group is deleted
     */
    function isDeleted(){
        return ( $this->data_array['stateid'] == 2 );
    }

	/**
	 *  update - update a DocumentGroupDocs.
	 *
	 *  @param	string	Name of the category.
	 *  @return boolean.
	 */
	function update($name,$parent_doc_group) {
		global $Language;

		$perm =& $this->Group->getPermission (session_get_user());
		if (!$perm || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}
		if (!$name) {
			$this->setMissingParamsError();
			return false;
		}
		
		if ($parent_doc_group) {
			// check if parent group exists
			$res=db_query("SELECT * FROM plugin_docs_doc_groups WHERE doc_group='$parent_doc_group' AND group_id=".$this->Group->getID());
			if (!$res || db_numrows($res) < 1) {
				$this->setError(dgettext('gforge-plugin-novadoc','invalid_parent_id'));
				return false;
			}
		} else {
			$parent_doc_group=0;
		}

		if( !$this->checkUnique( $name, $parent_doc_group,  $this->Group->getID()  ) ){
		    return false;
		}

		
		$sql="UPDATE plugin_docs_doc_groups
			SET groupname='".htmlspecialchars($name)."',
			parent_doc_group='".$parent_doc_group."'
			WHERE doc_group='". $this->getID() ."'
			AND group_id='".$this->Group->getID()."'";
		$result=db_query($sql);
		if ($result && db_affected_rows($result) > 0) {
		    $this->fetchData( $this->getId() );
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}
	}
		
	/**
	* hasDocuments - Recursive function that checks if this group or any of it childs has documents associated to it
	*
	* A group has associated documents if and only if there are documents associated to this
	* group or to any of its childs
	*
	* @param array	Array of nested groups information, fetched from DocumentGroupDocsFactory class
	* @param object	The DocumentFactory object
	* @param int	(optional) State of the documents
	*/
	function hasDocuments(&$nested_groups, &$document_factory, $stateid=0) {
		static $result = array();	// this function will probably be called several times so we better store results in order to speed things up
		if (!is_array($result[$stateid])) $result[$stateid] = array();

		$doc_group_id = $this->getID();

		if (array_key_exists($doc_group_id, $result[$stateid])) return $result[$stateid][$doc_group_id];

		
		// check if it has documents
		if ($stateid) {
			$document_factory->setStateID($stateid);
		}
		$document_factory->setDocGroupID($doc_group_id);
		$docs = $document_factory->getDocuments();
		if (is_array($docs) && count($docs) > 0) {		// this group has documents
			$result[$stateid][$doc_group_id] = true;
			return true;
		}
		
		// this group doesn't have documents... check recursively on the childs
		if ( isset( $nested_groups[$doc_group_id])  && is_array($nested_groups["$doc_group_id"])) {
			$count = count($nested_groups["$doc_group_id"]);
			for ($i=0; $i < $count; $i++) {
				if ($nested_groups["$doc_group_id"][$i]->hasDocuments($nested_groups, $document_factory, $stateid)) {
					// child has documents
					$result[$stateid][$doc_group_id] = true;
					return true;
				}
			}
			// no child has documents, then this group doesn't have associated documents
			$result[$stateid][$doc_group_id] = false;
			return false;
		} else {	// this group doesn't have childs
			$result[$stateid][$doc_group_id] = false;
			return false;
		}
	}

	/**
	* hasSubgroup - Checks if this group has a specified subgroup associated to it
	*
	* @param array Array of nested groups information, fetched from DocumentGroupDocsFactory class
	* @param int	ID of the subgroup
	*/
	function hasSubgroup(&$nested_groups, $doc_subgroup_id) {
		$doc_group_id = $this->getID();

		if( isset($nested_groups[$doc_group_id]) && is_array($nested_groups[$doc_group_id])) {
			$count = count($nested_groups["$doc_group_id"]);
			for ($i=0; $i < $count; $i++) {
				// child is a match?
				if ($nested_groups["$doc_group_id"][$i]->getID() == $doc_subgroup_id) {
					return true;
				} else {
					// recursively check if this child has this subgroup
					if ($nested_groups["$doc_group_id"][$i]->hasSubgroup($nested_groups, $doc_subgroup_id)) {
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	
	function countGroup(){
	    $res=db_query("SELECT count(*) AS nb FROM plugin_docs_doc_groups WHERE group_id=".$this->Group->getID());
		
		if (!$res || db_numrows($res) < 1) {
			$this->setError( 'Error count group' );
			return false;
		}
		
		$row =& db_fetch_array($res);
		return $row['nb'];
		
	}
	
	
	/**
	 * Creation of the default arborescence
	 */
   function createDefaultArbo (){ 
      $config = DocumentConfig::getInstance ();
      $nb = $this->countGroup ();
      if($nb === false){
        return false;
      }
      if( $nb == 0 ){
         if ($config->defaultArbo != null)
         {
            if ($this->createDefaultRecur( $config->defaultArbo, 0 ) == false)
            {
               db_rollback ();
               return false;
            }
         }
      }
      return true;
   }

	
	function createDefaultRecur($arbo, $parent){
	    
	    if( is_array( $arbo ) ){
	        $keys = array_keys($arbo);
            for ($i = count($arbo)-1; $i>=0; $i--) {
                $k = $keys[$i];   // cle = nom du r?ertoire
                $v = $arbo[ $k ];
                
                $d = new DocumentGroupDocs( $this->Group );
	            
	            if( !$d->create( addslashes($k), $parent ) ){
	                $this->setError( $d->getErrorMessage() );
	                return false;
	            }
	            
	            if( $v ){
	                if( !$this->createDefaultRecur( $v, $d->getID() ) ){
	                    return false;
	                }
	            }
	        }
	    }else if( is_string( $arbo ) ){
	        
	        $d = new DocumentGroupDocs( $this->Group );
	        
            if( !$d->create( addslashes($arbo), $parent ) ){
                $this->setError( $d->getErrorMessage() );
                return false;
            }
	    }
	    return true;
	}


    function getPath( ){
    	$group = $this->Group->getID();
    	$parent = $this->getParentID();
        $thisName = novadoc_unixString ($this->getName ());

        if( $parent == 0 ){
            return $thisName;
        }else{
            $dg = new DocumentGroupDocs($this->Group, $parent);
            return $dg->getPath() . '/' . $thisName;
        }

    }
	

	
	
	function deleteGroupDocs(){
	    $req = " SELECT docid FROM plugin_docs_doc_data WHERE doc_group = '" . $this->getID() . "'";
	    $res=db_query( $req );
	    
		if (!$res) {
			$this->setError( 'Error select doc of group : ' . db_error() );
			return false;
		}
		
		while( $d =& db_fetch_array($res) ){
		    $docObj = new Document( $this->Group, $d['docid'] );
		    if( !$docObj->delete() ){
		        $this->setError( 'Error delete a doc : ' . $docObj->getErrorMessage() );
		        return false;
		    }
		}
		
		// $req = " DELETE FROM plugin_docs_doc_groups WHERE doc_group = '" . $this->getID() . "'" ;
		$req = " UPDATE plugin_docs_doc_groups SET stateid='2' WHERE doc_group = '" . $this->getID() . "'" ;
		$res=db_query( $req );

		if (!$res) {
			$this->setError( 'Error delete group : ' . db_error( ) );
			return false;
		}
		
		
		return true;
	}
	
}

?>
