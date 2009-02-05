<?php
/**
 * GForge Fr Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: FileGroupFrs.class.php,v 1.9 2006/11/22 10:17:24 pascal Exp $
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
	File Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("common/include/Error.class.php");
require_once ("plugins/novafrs/include/FileConfig.class.php");
require_once ("plugins/novafrs/include/utils.php");

class FileGroupFrs extends Error {

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
	 *  FileGroupFrs - constructor.
	 *
	 *  Use this constructor if you are modifying an existing fr_group.
	 *
	 *	@param	object	Group object.
	 *  @param	array	(all fields from fr_groups) OR fr_group from database.
	 *  @return boolean	success.
	 */
	function FileGroupFrs(&$Group, $data=false) {
		$this->Error();

		//was Group legit?
		if (!$Group || !is_object($Group)) {
			$this->setError('FileGroupFrs: No Valid Group');
			return false;
		}
		//did Group have an error?
		if ($Group->isError()) {
			$this->setError('FileGroupFrs: '.$Group->getErrorMessage());
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


    function checkUnique( $name, $parent_fr_group, $group_id ){
        global $Language;
        
        $sql = " SELECT groupname FROM plugin_frs_fr_groups
                    WHERE group_id = '$group_id'
                    AND parent_fr_group = '$parent_fr_group'
                    AND stateid = 0
                    AND groupname = '$name' ";

		$result=db_query($sql);
		if(  db_numrows($result) != 0 ){
		    $this->setError( dgettext('gforge-plugin-novafrs','group_unique') );
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
	function create($name,$parent_fr_group=0) {
		global $Language;
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(dgettext('gforge-plugin-novafrs','name_required'));
			return false;
		}
		
		if ($parent_fr_group) {
			// check if parent group exists
			$res=db_query("SELECT * FROM plugin_frs_fr_groups WHERE fr_group='$parent_fr_group' AND group_id=".$this->Group->getID());
			if (!$res || db_numrows($res) < 1) {
				$this->setError(dgettext('gforge-plugin-novafrs','invalid_parent_id'));
				return false;
			}
		} else {
			$parent_fr_group = 0;
		}
		

		$perm =& $this->Group->getPermission (session_get_user());
		if (!$perm || !$perm->isReleaseTechnician()) {
			$this->setPermissionDeniedError();
			return false;
		}


		if( !$this->checkUnique( $name, $parent_fr_group, $this->Group->getID() ) ){
		    return false;
		}

		
		$sql="INSERT INTO plugin_frs_fr_groups (group_id,groupname,parent_fr_group)
			VALUES ('".$this->Group->getID()."','".($name)."','".$parent_fr_group."')";
		$result=db_query($sql);
		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
		} else {
			$this->setError('FileGroupFrs::create() Error Adding Group: '.db_error());
			return false;
		}

		$fr_group = db_insertid($result, 'plugin_frs_fr_groups', 'fr_group');

		//	Now set up our internal data structures
		if (!$this->fetchData($fr_group)) {
			return false;
		}


        $config = FileConfig::getInstance();
		$dirPath = $config->sys_novafrs_path . '/' . $this->Group->getUnixName () . '/' . $this->getPath ();
		
	    if( !is_dir( $dirPath ) ){
    		if( !mkdir( $dirPath ) ){
		        $this->setError( 'mkdir failed '. $dirPath );
		        return false;
		    }
	    }		


		return true;
	}


	/**
	 *	fetchData - re-fetch the data for this FileGroupFrs from the database.
	 *
	 *	@param	int		ID of the fr_group.
	 *	@return boolean.
	 */
	function fetchData($id) {
		global $Language;
		$res=db_query("SELECT * FROM plugin_frs_fr_groups WHERE fr_group='$id'");
		if (!$res || db_numrows($res) < 1) {
			$this->setError(dgettext('gforge-plugin-novafrs','invalid_id'));
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getGroup - get the Group Object this FileGroupFrs is associated with.
	 *
	 *	@return Object Group.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getID - get this FileGroupFrs's ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['fr_group'];
	}
	
	/**
	 *	getID - get parent FileGroupFrs's id.
	 *
	 *	@return	int	The id #.
	 */
	function getParentID() {
		return $this->data_array['parent_fr_group'];
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
	 *  update - update a FileGroupFrs.
	 *
	 *  @param	string	Name of the category.
	 *  @return boolean.
	 */
	function update($name,$parent_fr_group) {
		global $Language;

		$perm =& $this->Group->getPermission (session_get_user());
		if (!$perm || !$perm->isReleaseTechnician()) {
			$this->setPermissionDeniedError();
			return false;
		}
		if (!$name) {
			$this->setMissingParamsError();
			return false;
		}
		
		if ($parent_fr_group) {
			// check if parent group exists
			$res=db_query("SELECT * FROM plugin_frs_fr_groups WHERE fr_group='$parent_fr_group' AND group_id=".$this->Group->getID());
			if (!$res || db_numrows($res) < 1) {
				$this->setError(dgettext('gforge-plugin-novafrs','invalid_parent_id'));
				return false;
			}
		} else {
			$parent_fr_group=0;
		}

		if( !$this->checkUnique( $name, $parent_fr_group,  $this->Group->getID()  ) ){
		    return false;
		}

		
		$sql="UPDATE plugin_frs_fr_groups
			SET groupname='".htmlspecialchars($name)."',
			parent_fr_group='".$parent_fr_group."'
			WHERE fr_group='". $this->getID() ."'
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
	* hasFiles - Recursive function that checks if this group or any of it childs has files associated to it
	*
	* A group has associated files if and only if there are files associated to this
	* group or to any of its childs
	*
	* @param array	Array of nested groups information, fetched from FileGroupFrsFactory class
	* @param object	The FileFactory object
	* @param int	(optional) State of the files
	*/
	function hasFiles(&$nested_groups, &$file_factory, $stateid=0) {
		static $result = array();	// this function will probably be called several times so we better store results in order to speed things up
		if (!is_array($result[$stateid])) $result[$stateid] = array();

		$fr_group_id = $this->getID();

		if (array_key_exists($fr_group_id, $result[$stateid])) return $result[$stateid][$fr_group_id];

		
		// check if it has files
		if ($stateid) {
			$file_factory->setStateID($stateid);
		}
		$file_factory->setFrGroupID($fr_group_id);
		$frs = $file_factory->getFiles();
		if (is_array($frs) && count($frs) > 0) {		// this group has files
			$result[$stateid][$fr_group_id] = true;
			return true;
		}
		
		// this group doesn't have files... check recursively on the childs
		if ( isset( $nested_groups[$fr_group_id])  && is_array($nested_groups["$fr_group_id"])) {
			$count = count($nested_groups["$fr_group_id"]);
			for ($i=0; $i < $count; $i++) {
				if ($nested_groups["$fr_group_id"][$i]->hasFiles($nested_groups, $file_factory, $stateid)) {
					// child has files
					$result[$stateid][$fr_group_id] = true;
					return true;
				}
			}
			// no child has files, then this group doesn't have associated files
			$result[$stateid][$fr_group_id] = false;
			return false;
		} else {	// this group doesn't have childs
			$result[$stateid][$fr_group_id] = false;
			return false;
		}
	}

	/**
	* hasSubgroup - Checks if this group has a specified subgroup associated to it
	*
	* @param array Array of nested groups information, fetched from FileGroupFrsFactory class
	* @param int	ID of the subgroup
	*/
	function hasSubgroup(&$nested_groups, $fr_subgroup_id) {
		$fr_group_id = $this->getID();

		if( isset($nested_groups[$fr_group_id]) && is_array($nested_groups[$fr_group_id])) {
			$count = count($nested_groups["$fr_group_id"]);
			for ($i=0; $i < $count; $i++) {
				// child is a match?
				if ($nested_groups["$fr_group_id"][$i]->getID() == $fr_subgroup_id) {
					return true;
				} else {
					// recursively check if this child has this subgroup
					if ($nested_groups["$fr_group_id"][$i]->hasSubgroup($nested_groups, $fr_subgroup_id)) {
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	
	function countGroup(){
	    $res=db_query("SELECT count(*) AS nb FROM plugin_frs_fr_groups WHERE group_id=".$this->Group->getID());
		
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
   function createDefaultArbo ()
   {
      $retour = true;
      $config = FileConfig::getInstance ();
      $nb = $this->countGroup ();
      if ($nb === false)
      {
         $retour = false;
      }
      else
      {
         if (($config->defaultArbo != null) && ($nb == 0))
         {
            if ($this->createDefaultRecur ($config->defaultArbo, 0) == false)
            {
               db_rollback ();
               $retour = false;
            }
         }
      }
      return $retour;
   }
	
   function createDefaultRecur($arbo, $parent)
   {
      $retour = true;
      if (is_array ($arbo) == true)
      {
         $keys = array_keys ($arbo);
         $i = count ($arbo);
         while (($i > 0) && ($retour == true))
         {
            $i--;
            $k = $keys [$i];   // cle = nom du répertoire
            $v = $arbo [$k];
            $d = new FileGroupFrs ($this->Group);
            if ($d->create (addslashes ($k), $parent) == false)
            {
               $this->setError ($d->getErrorMessage ());
               $retour = false;
            }
            else
            {
               if ($v)
               {
                  $retour = $this->createDefaultRecur ($v, $d->getID ());
               }
            }
         }
      }
      else
      {
         if (is_string ($arbo) == true)
         {
            $d = new FileGroupFrs ($this->Group);
            if ($d->create (addslashes ($arbo), $parent) == false)
            {
               $this->setError ($d->getErrorMessage ());
               $retour = false;
            }
         }
      }
      return $retour;
   }



    function getPath( ){
    	$group = $this->Group->getID();
    	$parent = $this->getParentID();
        $thisName = novafrs_unixString($this->getName());

        if( $parent == 0 ){
            return $thisName;
        }else{
            $dg = new FileGroupFrs($this->Group, $parent);
            return $dg->getPath() . '/' . $thisName;
        }

    }
	

	
	
	function deleteGroupFrs(){
	    $req = " SELECT frid FROM plugin_frs_fr_data WHERE fr_group = '" . $this->getID() . "'";
	    $res=db_query( $req );
	    
		if (!$res) {
			$this->setError( 'Error select fr of group : ' . db_error() );
			return false;
		}
		
		while( $d =& db_fetch_array($res) ){
		    $frObj = new File( $this->Group, $d['frid'] );
		    if( !$frObj->delete() ){
		        $this->setError( 'Error delete a fr : ' . $frObj->getErrorMessage() );
		        return false;
		    }
		}
		
		// $req = " DELETE FROM plugin_frs_fr_groups WHERE fr_group = '" . $this->getID() . "'" ;
		$req = " UPDATE plugin_frs_fr_groups SET stateid='2' WHERE fr_group = '" . $this->getID() . "'" ;
		$res=db_query( $req );

		if (!$res) {
			$this->setError( 'Error delete group : ' . db_error( ) );
			return false;
		}
		
		
		return true;
	}
	
}

?>
