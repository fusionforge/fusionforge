<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 *
 * Portions Copyright 2010 (c) Mélanie Le Bail
 */

require_once('common/event/Event.class.php');
require_once 'mailman/include/MailmanListDao.class.php';
require_once 'common/dao/CodendiDataAccess.class.php';
require_once 'mailman/include/MailmanList.class.php';
require_once 'plugins_utils.php';

class BackendMailmanList {

    protected $_mailinglistdao = null;

    /**
     * Hold an instance of the class
     */
    protected static $_instance;
    
    /**
     * Backends are singletons
     */
    public static function instance() {
    
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

    /**
     * @return MailingListDao
     */
    protected function _getMailingListDao() {
        if (!$this->_mailinglistdao) {
          $this->_mailinglistdao = new MailmanListDao(CodendiDataAccess::instance());
        }
        return $this->_mailinglistdao;
    }


    /** 
     * Update mailman configuration for the given list
     * Write configuration in temporary file, and load it with mailman config_list tool
     * @return true on success, false otherwise
     */
    protected function updateListConfig($list) {
        // write configuration in temporary file
        $config_file=$GLOBALS['tmp_dir']."/mailman_config_".$list->getID().".in";
        
        if ($fp = fopen($config_file, 'w')) {
            // Define encoding of this file for Python. See SR #764 
            // Please note that this allows config_list to run with UTF-8 strings, but if the 	 
            // description contains non-ascii chars, they will be displayed badly in mailman config web page.
            fwrite($fp, "# coding=UTF-8\n\n");
            // Deactivate monthly reminders by default
            fwrite($fp, "send_reminders = 0\n");
            // Setup the description
            fwrite($fp, "description = '".addslashes($list->getDescription())."'\n");
            // Allow up to 200 kB messages
            fwrite($fp, "max_message_size = 200\n");
        
            if ($list->isPublic() == 0) { // Private lists
                // Don't advertise this list when people ask what lists are on this machine
                fwrite($fp, "advertised = False\n");
                // Private archives
                fwrite($fp, "archive_private = 1\n");
                // Subscribe requires approval
                fwrite($fp, "subscribe_policy = 2\n");
            }
            fclose($fp);
            
            if (system($GLOBALS['mailman_bin_dir']."/config_list -i $config_file ".$list->getName()) !== false) {
                if (unlink($config_file)) {
			return true;
                }
            }
        }
        return false;
    }


    /**
     * Create new mailing list with mailman 'newlist' tool
     * then update the list configuration according to list settings
     * @return true on success, false otherwise
     */
    public function createList($group_list_id) {

        $dar = $this->_getMailingListDao()->searchByGroupListId($group_list_id);

        if ($row = $dar->getRow()) {
            $list = new MailmanList($row['group_id'],$row['group_list_id']);
            $user=UserManager::instance()->getUserByID($list->getListAdminId());
	    $list_admin_email= $user->getEmail();
            $list_dir = $GLOBALS['mailman_lib_dir']."/lists/".$list->getName();

	    if($list->isPublic() != 9) {
		    if ((! is_dir($list_dir))) {
			    // Create list
			    system($GLOBALS['mailman_bin_dir']."/newlist -q ".$list->getName()." ".$list_admin_email." ".$list->getPassword()." >/dev/null");
			    // Then update configuraion
			    if( is_dir($list_dir) && $this->updateListConfig($list) !=false ) {
				    $result = $this->_getMailingListDao() -> updateList($list->getID(),$row['group_id'], $list->getDescription(), $list->isPublic(),'3');
				    if (!$result) {
					    printf('Unable to update the list status: '.db_error());
					    return false;
				    }		
				    else {
					    return true;
				    }
			    }
			    else {
				    return false;
			    }
		    }
		    else {
			    $result = $this->_getMailingListDao() -> updateList($list->getID(),$row['group_id'], $list->getDescription(), $list->isPublic(),'3');
			    if (!$result) {
				    printf('Unable to update the list status: '.db_error());
				    return false;
			    }		
			    else {
				    return true;
			    }
		    }
	    }
	}
	return false;
    }

    /**
     * Delete mailing list 
     * - list and archives are deleted
     * - backup first in temp directory
     * @return true on success, false otherwise
     */
    public function deleteList($group_list_id) {
	    $dar = $this->_getMailingListDao()->searchByGroupListId($group_list_id);

	    if ($row = $dar->getRow()) {
		    $list=new MailmanList($row['group_id'],$group_list_id);
		    $list_dir = $GLOBALS['mailman_lib_dir']."/lists/".$list->getName();
		    if ((is_dir($list_dir))&&($list->isPublic() == 9)) {

			    // Archive first
			    $list_archive_dir = $GLOBALS['mailman_lib_dir']."/archives/private/".$list->getName(); // Does it work? TODO
			    $backupfile=$GLOBALS['mailman_lib_dir']."/archives/".$list->getName()."-mailman.tgz";
			    system("tar cfz $backupfile $list_dir $list_archive_dir");
			    chmod($backupfile,0600);

			    // Delete the mailing list if asked to and the mailing exists (archive deleted as well)
			    system($GLOBALS['mailman_bin_dir']. '/rmlist -a '. $list->getName() .' >/dev/null');

			    return true;
		    }
	    }
	    return false;
    }

    /**
     * Check if the list exists on the file system
     * @return true if list exists, false otherwise
     */
    public function listExists($list) {
	    // Is this the best test?
	    $list_dir = $GLOBALS['mailman_lib_dir']."/lists/".$list->getName();
	    if (! is_dir($list_dir)) return false;
	    return true;
    }

}

?>
