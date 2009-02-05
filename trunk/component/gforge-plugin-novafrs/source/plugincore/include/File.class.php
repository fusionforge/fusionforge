<?php
/**
 * GForge Fr Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: File.class.php,v 1.10 2006/11/22 10:17:24 pascal Exp $
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

require_once ('common/include/Error.class.php');
require_once ('plugins/novafrs/include/FileConfig.class.php');

class File extends Error
{

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $data_array.
	 */
	var $data_array;
    
    /**
     * Table of status
     * @var array   $tableStatus
     */
    var $tableStatus;

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group; //group object

    var $config; // config of files class FileConfig

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this file is associated.
	 *  @param  int	 The frid.
	 *  @param  array	The associative array of data.
	 *	@return	boolean	success.
	 */
	function File(&$Group, $frid=false, $arr=false) {
		$this->Error();
		$this->config = FileConfig::getInstance();
		if (!$Group || !is_object($Group)) {
			$this->setNotValidGroupObjectError();
			return false;
		}
		if ($Group->isError()) {
			$this->setError('File:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($frid) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($frid)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('Group_id in db result does not match Group Object');
					$this->data_array = null;
					return false;
				}
			}
			if (!$this->isPublic()) {
				$perm =& $this->Group->getPermission( session_get_user() );

				if (!$perm || !is_object($perm) || !$perm->isMember()) {
					$this->setPermissionDeniedError();
					$this->data_array = null;
					return false;
				}
			}
		}
		return true;
	}



    /**
     * Check the file is unique in a branch
     * @param $filname name of the file
     * @param $fr_group the branch id
     * @param $frid_exception no match with this file
     */
    function checkUnique( $filename, $fr_group, $frid_exception=null ){
        global $Language;

		$sql = "  SELECT filename FROM plugin_frs_fr_data 
		            WHERE fr_group = '$fr_group'
		            AND is_current = '1' 
		            AND filename = '$filename' ";
    
        if( $frid_exception ){
            $sql .= " AND frid <> '$frid_exception' ";
        }
        
		$result=db_query($sql);
		if(  db_numrows($result) != 0 ){
		    $this->setError( dgettext('gforge-plugin-novafrs','fr_unique') );
		    return false;
		} 
		return true;       
    }


    function getNewChrono(){
        $group_id = $this->Group->getId();
        
        $sql = " SELECT group_id, chrono FROM plugin_frs_fr_chrono WHERE group_id = '$group_id' ";
		$result=db_query($sql);
		
		if( ! $result ){
		    $this->setError( 'getNewChrono(), select : ' . db_error() );
		    return false;
		}
		
		if(  db_numrows($result) == 0 ){
		    $sql = " INSERT INTO plugin_frs_fr_chrono( group_id, chrono ) VALUES ('$group_id', '1' ) ";

    		$result=db_query($sql);
	    	if( ! $result ){
		        $this->setError( 'getNewChrono(), insert : ' . db_error() );
		        return false;
		    }
		    
		    return 1;
		    
		}else{
            $val = db_fetch_array( $result);
            $chrono = $val['chrono'] + 1;
            
            $sql = " UPDATE plugin_frs_fr_chrono SET chrono = '$chrono' WHERE group_id = '$group_id' ";
            
    		$result=db_query($sql);
	    	if( ! $result ){
		        $this->setError( 'getNewChrono(), update : ' . db_error() );
		        return false;
		    }
		    
		    return $chrono;
		} 
    }



	/**
	 *	create - use this function to create a new entry in the database.
	 *
	 *	@param	string	The filename of this file. Can be a URL.
	 *	@param	string	The filetype of this file. If filename is URL, this should be 'URL';
	 *	@param	string	The contents of this file (should be addslashes()'d before entry).
	 *	@param	int	The fr_group id of the fr_groups table.
	 *	@param	string	The title of this file.
	 *	@param	int	The language id of the supported_languages table.
	 *	@param	string	The description of this file.
	 *  @param  frid_replace The id of the old version of the file
	 *	@return	boolean	success.
	 */
	function create($filename,$filetype,$data,$fr_group,$title,$language_id,$description,
                        $status,$author,$writingDate,$frType,$reference,$version, $fr_observation, $filesize, $fr_chrono=null, $frid_replace=null ) {
		global $Language;
		if (strlen($title) < 5) {
			$this->setError(dgettext('gforge-plugin-novafrs','error_min_title_length'));
			return false;
		}

		if( !$this->checkUnique( $filename, $fr_group, $frid_replace ) ){
		    $this->setError( dgettext('gforge-plugin-novafrs', 'fr_unique') );
		    return false;
		}


        if( $fr_chrono == null ){
            $fr_chrono = $this->getNewChrono();
            if( $fr_chrono === false ) return false;
        }

		$user_id = ((session_loggedin()) ? user_getid() : 100);

		$fr_initstatus = '3';
		// If Editor - uploaded Files are ACTIVE
		if ( session_loggedin() ) {
			$fr_initstatus = '1';
		}

		$sql="INSERT INTO plugin_frs_fr_data (group_id,title,description,createdate,fr_group,
			stateid,language_id,filename,filetype,filesize,data,created_by,status,status_modif_by,
			status_modif_date,author,writing_date,fr_type,reference,version,is_current,fr_observation,
			fr_chrono)
			VALUES ('".$this->Group->getId()."',
			'". htmlspecialchars($title) ."',
			'". htmlspecialchars($description) ."',
			'". time() ."',
			'$fr_group',
			'$fr_initstatus',
			'$language_id',
			'$filename',
			'$filetype',
			'$filesize',
			'". /*base64_encode(stripslashes($data)) .*/"',
			'$user_id',
			'$status',
			'$user_id',
			'". time() . "',
            '$author',
            '$writingDate',
            '$frType',
            '$reference',
            '$version',
            '1' ,
            '$fr_observation',
            '$fr_chrono'  
            )";

		db_begin();
		$result=db_query($sql);
		if (!$result) { 
			$this->setError('Error Adding File: '.db_error());
			db_rollback();
			return false;
		}
		$frid=db_insertid($result,'plugin_frs_fr_data','frid');

		if (!$this->fetchData($frid)) { 
			db_rollback();
			return false;
		}

        // juste created, this is the current version
        if( !$this->updateVersion( true, $frid ) ){ 
			db_rollback();
			return false;
        }

		$this->sendNotice(true);
		db_commit();
		return true;
	}

	/**
	 *  fetchData() - re-fetch the data for this file from the database.
	 *
	 *  @param  int	 The file id.
	 *	@return	boolean	success
	 */
	function fetchData($frid) {
		global $Language;
		
		$sql = "SELECT * FROM plugin_frs_frdata_vw
			WHERE frid='$frid'
			AND group_id='". $this->Group->getID() ."'";
		
		$res=db_query($sql);
		if (!$res || db_numrows($res) < 1) {
			$this->setError($sql.' '.dgettext('gforge-plugin-novafrs','invalid_frid') . ' : ' . db_error() );
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		db_free_result($res);
		return true;
	}



    function fetchDataStatus(){
        global $Language;
        $id = $this->getID();
        if( !$id ) return;
        
        $req = " SELECT * FROM plugin_frs_fr_status_table
        			WHERE frid='$id' ";
 
        $res = db_query( $req );
        
        if (!$res){
            $this->setError(dgettext('gforge-plugin-novafrs','invalid_frid') . ' : ' . db_error() );
        }
        $status = array();
        while( $v =& db_fetch_array($res) ){
            $status[ $v['statustype'] ] = $v;
        }
        db_free_result($res);
        
        if( $this->config->statusTable ){
            foreach( $this->config->statusTable as $k=>$v ){
                if( !isset( $status[ $k ] ) ){
                    $v = array();
                    $v['statustype'] = $k;
                    $v['date'] = '';
                    $v['name'] = '';
                    $v['description'] = '';
                    $status[ $k ] = $v;
                }
            }
        }
        
        $this->tableStatus = $status;        
    }

    function updateStatusTable( $statusArray ){
        global $Language;
        $id = $this->getID();
        if( !$id ) return false;
        
        db_begin();
        
        $req = " DELETE FROM plugin_frs_fr_status_table WHERE frid='$id' ";
        $res = db_query( $req );
        
        if (!$res){
            $this->setError(dgettext('gforge-plugin-novafrs','invalid_frid') . ' : ' . db_error() );
            db_rollback();
            return false;
        }
        db_free_result($res);
        
        
        foreach( $statusArray as $k=>$s ){
            if( $s['date'] || $s['name'] || $s['description'] ){
                $req = " INSERT INTO plugin_frs_fr_status_table( frid, statustype, date, name, description ) VALUES ";
                
                $date = $s['date'];
                $name = $s['name'];
                $description = $s['description'];
                $req .= "( '$id', '$k', '$date', '$name', '$description'  )";

                $res = db_query( $req );
                if (!$res){
                    $this->setError( ' updateStatus failed ' . ' : ' . db_error() );
                    db_rollback();
                    return false;
                }

            }
        }
         
        db_commit(); 
        return true;
        
    }


	/**
	 *	getGroup - get the Group object this File is associated with.
	 *
	 *	@return	Object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}


    function getData( $name ){
        if( isset( $this->data_array[ $name ] ) ){
            return $this->data_array[ $name ];
        }else{
            return '';
        }
    }


	/**
	 *	getID - get this frid.
	 *
	 *	@return	int	The frid.
	 */
	function getID() {
		return $this->getData('frid');
	}

	/**
	 *	getName - get the name of this file.
	 *
	 *	@return string	The name of this file.
	 */
	function getName() {
		return $this->getData('title');
	}

	/**
	 *	getDescription - the description of this file.
	 *
	 *	@return string	The description.
	 */
	function getDescription() {
		return $this->getData('description');
	}

	/**
	 *	isURL - whether this file is a URL and not a local file.
	 *
	 *	@return	boolean	is_url.
	 */
	function isURL() {
		return ($this->getData('filetype') == 'URL');
	}

	/**
	 *	isPublic - whether this file is available to the general public.
	 *
	 *	@return	boolean	is_public.
	 */
	function isPublic() {
		return (($this->getData('stateid') == 1) ? true  : false);
	}

	/**
	 *	getStateID - get this stateid.
	 *
	 *	@return	int	The stateid.
	 */
	function getStateID() {
		return $this->getData('stateid');
	}
	
	function isDeleted(){
	    return ( $this->getStateID()==2 );
	}


    /**
     *  getStatus - get the status
     *
     *  @return the status 
     */
    function getStatus(){
        return $this->getData('status');
    }
    
    function getStatusModifBy(){
        return $this->getData('status_realname');
    }
    
    
    function getStatusModifDate(){
        return $this->getData('status_modif_date');
    }


	/**
	 *	getStateName - the statename of this file.
	 *
	 *	@return string	The statename.
	 */
	function getStateName() {
		return $this->getData('state_name');
	}

	/**
	 *	getLanguageID - get this language_id.
	 *
	 *	@return	int	The language_id.
	 */
	function getLanguageID() {
		return $this->getData('language_id');
	}

	/**
	 *	getLanguageName - the language_name of this file.
	 *
	 *	@return string	The language_name.
	 */
	function getLanguageName() {
		return $this->getData('name');
	}

	/**
	 *	getFrGroupID - get this fr_group_id.
	 *
	 *	@return	int	The fr_group_id.
	 */
	function getFrGroupID() {
		return $this->getData('fr_group');
	}

	/**
	 *	getFrGroupName - the fr_group_name of this file.
	 *
	 *	@return string	The frgroupname.
	 */
	function getFrGroupName() {
		return $this->getData('group_name');
	}

	/**
	 *	getCreatorID - get this creator's user_id.
	 *
	 *	@return	int	The user_id.
	 */
	function getCreatorID() {
		return $this->getData('created_by');
	}

	/**
	 *	getCreatorUserName - the unix name of the person who created this file.
	 *
	 *	@return string	The unix name of the creator.
	 */
	function getCreatorUserName() {
		return $this->getData('user_name');
	}

	/**
	 *	getCreatorRealName - the real name of the person who created this file.
	 *
	 *	@return string	The real name of the creator.
	 */
	function getCreatorRealName() {
		return $this->getData('realname');
	}

	/**
	 *	getCreatorEmail - the email of the person who created this file.
	 *
	 *	@return string	The email of the creator.
	 */
	function getCreatorEmail() {
		return $this->getData('email');
	}

	/**
	 *	getFileName - the filename of this file.
	 *
	 *	@return string	The filename.
	 */
	function getFileName() {
		return $this->getData('filename');
	}

	/**
	 *	getFileType - the filetype of this file.
	 *
	 *	@return string	The filetype.
	 */
	function getFileType() {
		return $this->getData('filetype');
	}
	
	function getAuthor(){
	    return $this->getData('author');
	}
	
	function getWritingDate(){
	    return $this->getData('writing_date');
	}
	
	function getFrType(){
	    return $this->getData('fr_type');
	}
	
	function getReference(){
	    return $this->getData('reference');
	}

    function getVersion(){
        return $this->getData('version');
    }

    function getUpdateDate(){
        return $this->getData('updatedate');
    }

    function getCreateDate(){
        return $this->getData('createdate');        
    }
    
    function getObservation(){
        return $this->getData('fr_observation');        
    }

    function getChrono(){
        return $this->getData('fr_chrono');        
    }
    
    function isCurrent(){
        return $this->getData('is_current');        
    }
    
    

	/**
	 *	getFileData - the filedata of this file.
	 *
	 *	@return string	The filedata.
	 */
	function getFileData() {
		//
		//	Because this could be a large string, we only fetch if we actually need it
		//
		$res=db_query("SELECT data FROM plugin_frs_fr_data WHERE frid='".$this->getID()."'");
		return base64_decode(db_result($res,0,'data'));
	}
	
	/**
	* getFileSize - Return the size of the file
	*
	* @return	int	The file size
	*/
	function getFileSize() {
		return $this->getData('filesize');
	}

	/**
	 *	update - use this function to update an existing entry in the database.
	 *
	 *	@param	string	The filename of this file. Can be a URL.
	 *	@param	string	The filetype of this file. If filename is URL, this should be 'URL';
	 *	@param	string	The contents of this file (should be addslashes()'d before entry).
	 *	@param	int	The fr_group id of the fr_groups table.
	 *	@param	string	The title of this file.
	 *	@param	int	The language id of the supported_languages table.
	 *	@param	string	The description of this file.
	 *	@param	int	The state id of the fr_states table.
	 *	@return	boolean	success.
	 */
	function update($filename,$filetype,$data,$fr_group,$title,$language_id,$description,$stateid,
	                $status,$author,$writingDate,$frtype,$reference,$version,$observation, $filesize){	
		global $Language;
		if (strlen($title) < 5) {
			$this->setError(dgettext('gforge-plugin-novafrs','error_min_title_length'));
			return false;
		}

        if( !session_loggedin() ){
			$this->setPermissionDeniedError();
			return false;
        }

        if( addslashes($this->getFileName())!=$filename or $this->getFrGroupId()!=$fr_group) {
		    if( !$this->checkUnique( $filename, $fr_group ) ){
		        return false;
		    }
		}

        if( $stateid=='' ) $stateid=1;

		if ($data) {
			$datastr="data='". base64_encode(stripslashes($data)) ."', filesize='".$filesize."',";
		}else{
		    $datastr = '';
		}
		
		if( $filesize === null ){
		    $filesize_req  = '';
		}else{
		    $filesize_req  = "filesize='$filesize',";
		}		
		
		$user_id = ((session_loggedin()) ? user_getid() : 100);
		
		$setDateStatus = false;
		if( !isset( $this->data_array['status'] )  or $this->getStatus() != $status ){
		    $setDateStatus = time();
		}

        $req = "UPDATE plugin_frs_fr_data SET
			title='". htmlspecialchars($title) ."',
			description='". htmlspecialchars($description) ."',
			stateid='$stateid',
			fr_group='$fr_group',
			filetype='$filetype',
			filename='$filename',
			$datastr
			$filesize_req
			language_id='$language_id',
			status='$status',
			updatedate='". time() ."',
			status_modif_by='". $user_id . "'" .
			($setDateStatus?",status_modif_date='". time() ."' " : ''). ",
			author='$author',
			writing_date='$writingDate',
			fr_type='$frtype',
			reference='$reference',
			version='$version',
			fr_observation='$observation'
			WHERE group_id='".$this->Group->getID()."'
			AND frid='".$this->getID()."'";
		
		$res = db_query( $req );


		if (!$res || db_affected_rows($res) < 1) { 
			$this->setOnUpdateError(db_error());
			return false;
		}
		
		$this->fetchData( $this->getID() );
		
		$this->sendNotice(false);
		return true;
	}


    function updateHistoryFrGroup( $fr_group ){
        $req = "UPDATE plugin_frs_fr_data SET
            fr_group='$fr_group'
			WHERE group_id='".$this->Group->getID()."'
			AND frid_current_version ='".$this->getID()."'";
		$res = db_query( $req );

		if (!$res || db_affected_rows($res) < 1) { 
			$this->setOnUpdateError(db_error());
			return false;
		}
		$this->sendNotice(false);
		return true;		
	}


    function updateStatus( $statusId ){
		$perm =& $this->Group->getPermission( session_get_user() );
		//if (!$perm || !is_object($perm) || !$perm->isReleaseTechnician()  ) {
		if( !session_loggedin() ){
			$this->setPermissionDeniedError();
			return false;
		}

        if( $this->getStatus() == $statusId ){
            // Pas de changement
            return true;
        }

		$setDateStatus = false;
		if( !isset( $this->data_array['status'] )  or $this->getStatus() != $statusId ){
		    $setDateStatus = time();
		}
        $user_id = ((session_loggedin()) ? user_getid() : 100);
        
        $req = "UPDATE plugin_frs_fr_data SET
			status='$statusId',
			status_modif_by='$user_id' ,
			status_modif_date='". time() ."' ,
			updatedate='". time() ."'
			WHERE group_id='".$this->Group->getID()."'
			AND frid='".$this->getID()."'";

		$res = db_query( $req );


		if (!$res || db_affected_rows($res) < 1) { 
			$this->setOnUpdateError(db_error());
			return false;
		}
		$this->sendNotice(false);
		return true;		
    }
    
	/**
	*   sendNotice - Notifies of file submissions
	*/
	function sendNotice ($new=true) {
		return true;
	}
	
	function delete() {
        $id = $this->getID();
        $sql = "UPDATE plugin_frs_fr_data SET stateid=2 WHERE frid='$id' ";
        $res = db_query( $sql );
        if (!$res){
            $this->setError('Error Deleting File: '.db_error());
            return false;
        }
		
		return true;
	}
	
	
	function updateVersion( $isCurrent, $frid_current_version ){
		$perm =& $this->Group->getPermission( session_get_user() );

		if( !session_loggedin() ){
			$this->setPermissionDeniedError();
			return false;
		}
        
        $isCurrent = ($isCurrent?1:0 );
        
        $req = "UPDATE plugin_frs_fr_data SET
			is_current='$isCurrent',
			frid_current_version='$frid_current_version'
			WHERE group_id='".$this->Group->getID()."'
			AND frid='".$this->getID()."'";

		$res = db_query( $req );


		if (!$res || db_affected_rows($res) < 1) { 
			$this->setError( db_error() );
			return false;
		}
		$this->sendNotice(false);
		return true;		
    }	    
	
	
	function updateHistory( $lastCurrent, $newCurrent ){
        
        $req = "UPDATE plugin_frs_fr_data SET
			is_current='0',
			frid_current_version='$newCurrent'
			WHERE group_id='".$this->Group->getID()."'
			AND frid_current_version='$lastCurrent' ";

		$res = db_query( $req );


		if (!$res || db_affected_rows($res) < 1) { 
			$this->setOnUpdateError(db_error());
			return false;
		}
		$this->sendNotice(false);
		return true;		
	}
	
	
    /**
     * Create a new version of the file.
     * Field 'is_current' of the current version is set to false.
     */
	function newVersion(){
	    $newFr = new File( $this->Group );
	    
	    $ret = $newFr->create( 
	        addslashes($this->getFileName()), addslashes($this->getFileType()), $this->getFileData(), $this->getFrGroupID(), addslashes($this->getName()),
	        $this->getLanguageID(), addslashes($this->getDescription()), $this->getStatus(), addslashes($this->getAuthor()), addslashes($this->getWritingDate()),
	        $this->getFrType(), addslashes($this->getReference()), addslashes($this->getVersion()), addslashes($this->getObservation()), $this->getFileSize(),
	        $this->getChrono(), $this->getID() );
	        
	        
	    if( ! $ret ){
	        return false;
	    }
        
        
        $ret = $this->updateHistory( $this->getID(), $newFr->getID() );
	    if( ! $ret ){
	        return false;
	    }    
	    
	    return $newFr;
	}
	
	
	function getHistory(){
	    
	    $sql = " SELECT * FROM plugin_frs_frdata_vw WHERE is_current<>'1' "
	            . " AND  frid_current_version = ". $this->getID() ." ORDER BY frid DESC ";

		$result = db_query($sql);
		if (!$result) {
			$this->setOnUpdateError( ' getHistory : ' . db_error());
			return false;
		}
		
		$history = array();
		
		while ($arr =& db_fetch_array($result)) {
			$history[] = new File($this->Group, $arr['frid'], $arr);
		}	    
	    return $history;
	}
	
	
	function getUnixFileNameHistory(){
	    return novafrs_unixString( $this->getFileName() ) . '#' . $this->getID();
	}
	
	
	function getChronoTable(){
	    
	    $sql = " SELECT * FROM plugin_frs_frdata_vw WHERE is_current='1' "
	            . " AND  group_id = ". $this->Group->getId() ." ORDER BY fr_chrono DESC ";

		$result = db_query($sql);

		if (!$result) {
			$this->setOnUpdateError( ' getChronoTable : ' . db_error()); 
			return false;
		}
		$chronos = array();
		
		while ($arr =& db_fetch_array($result)) {
			$chronos[] = new File($this->Group, $arr['frid'], $arr);
		}	    
	    return $chronos;
	}	    
	
}

?>
