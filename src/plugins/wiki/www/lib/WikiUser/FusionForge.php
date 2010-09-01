<?php //-*-php-*-
// rcs_id('$Id: FusionForge.php 7663 2010-08-31 15:23:17Z vargenau $');
/*
 * Copyright (C) 2006 Alain Peyrat
 *
 * This file is part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/** Call the FusionForge functions to get the username
 *
 */
class _FusionForgePassUser extends _PassUser {

    var $_is_external = 0;

    function _FusionForgePassUser($UserName='',$prefs=false) {
        if ($prefs) $this->_prefs = $prefs;
        if (!isset($this->_prefs->_method))
           _PassUser::_PassUser($UserName);
        if ($UserName) $this->_userid = $UserName;
        $this->_authmethod = 'FusionForge';

        // Is this double check really needed?
        // It is not expensive so we keep it for now.
        if ($this->userExists())
            return $this;
        else
            return $GLOBALS['ForbiddenUser'];
    }

    function userExists() {
            global $group_id;

        // Mapping (PhpWiki vs FusionForge) performed is:
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
                $this->_is_external = $user->getIsExternal();
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

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
