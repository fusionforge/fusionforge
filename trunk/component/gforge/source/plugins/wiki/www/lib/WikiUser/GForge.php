<?php //-*-php-*-
rcs_id('$Id: GForge.php,v 1.6 2005/08/06 13:21:37 rurban Exp $');
/* Copyright (C) 2006 Alain Peyrat
 * This file is part of PhpWiki. Terms and Conditions see LICENSE. (GPL2)
 */

/** Call the gforge functions to get the username 
 *  
 */
class _GForgePassUser extends _PassUser {

    function _GForgePassUser($UserName='',$prefs=false) {
        if ($prefs) $this->_prefs = $prefs;        
        if (!isset($this->_prefs->_method))
           _PassUser::_PassUser($UserName);
        if ($UserName) $this->_userid = $UserName;
        $this->_authmethod = 'GForge';
        
        // Is this double check really needed? 
        // It is not expensive so we keep it for now.
        if ($this->userExists())
            return $this;
        else 
            return $GLOBALS['ForbiddenUser'];
    }

    function userExists() {
    	global $group_id;

		// Mapping (phpWiki vs GForge) performed is:
		//     ANON  for non logged or non member
		//     USER  for member of the project.
		//     ADMIN for member having admin rights
		if (session_loggedin()){

			// Get project object (if error => ANON)
			$project =& group_get_object($group_id);
			
			if (!$project || !is_object($project)) {
		       	$this->_level = WIKIAUTH_ANON;
				return false;
			} elseif ($project->isError()) {
		       	$this->_level = WIKIAUTH_ANON;
				return false;
			}

			$member = false ;
			$user = session_get_user();
			$perm =& $project->getPermission($user);
			if (!$perm || !is_object($perm)) {
		       	$this->_level = WIKIAUTH_ANON;
				return false;
			} elseif (!$perm->isError()) {
				$member = $perm->isMember();
			}

			if ($member) {			
				$this->_userid = $user->getRealName();
				if ($perm->isAdmin()) {
					$this->_level = WIKIAUTH_ADMIN;
				} else {
        			$this->_level = WIKIAUTH_USER;
				}
        		return $this;
        	}
		}
       	$this->_level = WIKIAUTH_ANON;
       	return false;
    }

    function checkPass($submitted_password) {
        return $this->userExists() 
            ? ($this->isAdmin() ? WIKIAUTH_ADMIN : WIKIAUTH_USER)
            : WIKIAUTH_ANON;
    }

    function mayChangePass() {
        return false;
    }
}

// $Log: GForge.php,v $
//
// Revision 1.1  2006/11/01 10:43:58  aljeux
// seperate PassUser methods into seperate dir (memory usage)
// fix WikiUser (old) overlarge data session
// remove wikidb arg from various page class methods, use global ->_dbi instead
// ...
//

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>