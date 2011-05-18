<?php
// $Id: WikiGroup.php 8071 2011-05-18 14:56:14Z vargenau $'
/*
 * Copyright (C) 2003, 2004 $ThePhpWikiProgrammingTeam
 * Copyright (C) 2010 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if (!defined('GROUP_METHOD') or
    !in_array(GROUP_METHOD,
              array('NONE','WIKIPAGE','DB','FILE','LDAP')))
    trigger_error(_("No or unsupported GROUP_METHOD defined"), E_USER_WARNING);

/* Special group names for ACL */
define('GROUP_EVERY',        _("Every"));
define('GROUP_ANONYMOUS',    _("Anonymous Users"));
define('GROUP_BOGOUSER',    _("Bogo Users"));
define('GROUP_SIGNED',        _("Signed Users"));
define('GROUP_AUTHENTICATED',    _("Authenticated Users"));
define('GROUP_ADMIN',        _("Administrators"));
define('GROUP_OWNER',        _("Owner"));
define('GROUP_CREATOR',           _("Creator"));

/**
 * WikiGroup is an abstract class to provide the base functions for determining
 * group membership for a specific user. Some functions are user independent.
 *
 * Limitation: For the current user only. This must be fixed to be able to query
 * for membership of any user.
 *
 * WikiGroup is an abstract class with three functions:
 * <ol><li />Provide the static method getGroup with will return the proper
 *         subclass.
 *     <li />Provide an interface for subclasses to implement.
 *     <li />Provide fallover methods (with error msgs) if not impemented in subclass.
 * </ol>
 * Do not ever instantiate this class. Use: $group = &WikiGroup::getGroup();
 * This will instantiate the proper subclass.
 *
 * @author Joby Walker <zorloc@imperium.org>
 * @author Reini Urban
 */
class WikiGroup{
    /** User name */
    var $username = '';
    /** User object if different from current user */
    var $user;
    /** The global WikiRequest object */
    //var $request;
    /** Array of groups $username is confirmed to belong to */
    var $membership;
    /** boolean if not the current user */
    var $not_current = false;

    /**
     * Initializes a WikiGroup object which should never happen.  Use:
     * $group = &WikiGroup::getGroup();
     * @param object $request The global WikiRequest object -- ignored.
     */
    function WikiGroup($not_current = false) {
        $this->not_current = $not_current;
        //$this->request =& $GLOBALS['request'];
    }

    /**
     * Gets the current username from the internal user object
     * and erases $this->membership if is different than
     * the stored $this->username
     * @return string Current username.
     */
    function _getUserName(){
        global $request;
        $user = (!empty($this->user)) ? $this->user : $request->getUser();
        $username = $user->getID();
        if ($username != $this->username) {
            $this->membership = array();
            $this->username = $username;
        }
        if (!$this->not_current)
           $this->user = $user;
        return $username;
    }

    /**
     * Static method to return the WikiGroup subclass used in this wiki.  Controlled
     * by the constant GROUP_METHOD.
     * @param object $request The global WikiRequest object.
     * @return object Subclass of WikiGroup selected via GROUP_METHOD.
     */
    function getGroup($not_current = false){
        switch (GROUP_METHOD){
            case "NONE":
                return new GroupNone($not_current);
                break;
            case "WIKIPAGE":
                return new GroupWikiPage($not_current);
                break;
            case "DB":
                if ($GLOBALS['DBParams']['dbtype'] == 'ADODB') {
                    return new GroupDB_ADODB($not_current);
                } elseif ($GLOBALS['DBParams']['dbtype'] == 'SQL') {
                    return new GroupDb_PearDB($not_current);
                } else {
                    trigger_error("GROUP_METHOD = DB: Unsupported dbtype "
                                  . $GLOBALS['DBParams']['dbtype'],
                                  E_USER_ERROR);
                }
                break;
            case "FILE":
                return new GroupFile($not_current);
                break;
            case "LDAP":
                return new GroupLDAP($not_current);
                break;
            default:
                trigger_error(_("No or unsupported GROUP_METHOD defined"), E_USER_WARNING);
                return new WikiGroup($not_current);
        }
    }

    /** ACL PagePermissions will need those special groups based on the User status only.
     *  translated
     */
    function specialGroup($group){
        return in_array($group,$this->specialGroups());
    }
    /** untranslated */
    function _specialGroup($group){
        return in_array($group,$this->_specialGroups());
    }
    /** translated */
    function specialGroups(){
        return array(
                     GROUP_EVERY,
                     GROUP_ANONYMOUS,
                     GROUP_BOGOUSER,
                     GROUP_SIGNED,
                     GROUP_AUTHENTICATED,
                     GROUP_ADMIN,
                     GROUP_OWNER,
                     GROUP_CREATOR);
    }
    /** untranslated */
    function _specialGroups(){
        return array(
                     "_EVERY",
                     "_ANONYMOUS",
                     "_BOGOUSER",
                     "_SIGNED",
                     "_AUTHENTICATED",
                     "_ADMIN",
                     "_OWNER",
                     "_CREATOR");
    }

    /**
     * Determines if the current user is a member of a group.
     *
     * This method is an abstraction.  The group is ignored, an error is sent, and
     * false (not a member of the group) is returned.
     * @param string $group Name of the group to check for membership (ignored).
     * @return boolean True if user is a member, else false (always false).
     */
    function isMember($group){
        if (isset($this->membership[$group]))
            return $this->membership[$group];
        if ($this->specialGroup($group)) {
            return $this->isSpecialMember($group);
        } else {
            trigger_error(__sprintf("Method '%s' not implemented in this GROUP_METHOD %s",
                                    'isMember', GROUP_METHOD),
                          E_USER_WARNING);
        }
        return false;
    }

    function isSpecialMember($group){
        global $request;

        if (isset($this->membership[$group]))
            return $this->membership[$group];
        $user = (!empty($this->user)) ? $this->user : $request->getUser();
        switch ($group) {
            case GROUP_EVERY:
                return $this->membership[$group] = true;
            case GROUP_ANONYMOUS:
                return $this->membership[$group] = ! $user->isSignedIn();
            case GROUP_BOGOUSER:
                return $this->membership[$group] = (isa($user,'_BogoUser')
                                                    and $user->_level >= WIKIAUTH_BOGO);
            case GROUP_SIGNED:
                return $this->membership[$group] = $user->isSignedIn();
            case GROUP_AUTHENTICATED:
                return $this->membership[$group] = $user->isAuthenticated();
            case GROUP_ADMIN:
                return $this->membership[$group] = (isset($user->_level)
                                                    and $user->_level == WIKIAUTH_ADMIN);
            case GROUP_OWNER:
            case GROUP_CREATOR:
                return false;
            default:
                trigger_error(__sprintf("Undefined method %s for special group %s",
                                        'isMember',$group),
                              E_USER_WARNING);
        }
        return false;
    }

    /**
     * Determines all of the groups of which the current user is a member.
     *
     * This method is an abstraction.  An error is sent and an empty
     * array is returned.
     * @return array Array of groups to which the user belongs (always empty).
     */
    function getAllGroupsIn(){
        trigger_error(__sprintf("Method '%s' not implemented in this GROUP_METHOD %s",
                                'getAllGroupsIn', GROUP_METHOD),
                      E_USER_WARNING);
        return array();
    }

    function _allUsers() {
        static $result = array();
        if (!empty($result))
            return $result;

        global $request;
        /* WikiPage users: */
        $dbh =& $request->_dbi;
        $page_iter = $dbh->getAllPages();
        $users = array();
        while ($page = $page_iter->next()) {
            if ($page->isUserPage())
                $users[] = $page->_pagename;
        }

        /* WikiDB users from prefs (not from users): */
        if (ENABLE_USER_NEW)
            $dbi = _PassUser::getAuthDbh();
        else
            $dbi = false;

        if ($dbi and $dbh->getAuthParam('pref_select')) {
            //get prefs table
            $sql = preg_replace('/SELECT .+ FROM/i','SELECT userid FROM',
                                $dbh->getAuthParam('pref_select'));
            //don't strip WHERE, only the userid stuff.
            $sql = preg_replace('/(WHERE.*?)\s+\w+\s*=\s*["\']\$userid[\'"]/i','\\1 AND 1', $sql);
            $sql = str_replace('WHERE AND 1','',$sql);
            if (isa($dbi, 'ADOConnection')) {
                $db_result = $dbi->Execute($sql);
                foreach ($db_result->GetArray() as $u) {
                    $users = array_merge($users,array_values($u));
                }
            } elseif (isa($dbi, 'DB_common')) { // PearDB
                $users = array_merge($users, $dbi->getCol($sql));
            }
        }

        /* WikiDB users from users: */
        // Fixme: don't strip WHERE, only the userid stuff.
        if ($dbi and $dbh->getAuthParam('auth_user_exists')) {
            //don't strip WHERE, only the userid stuff.
            $sql = preg_replace('/(WHERE.*?)\s+\w+\s*=\s*["\']\$userid[\'"]/i','\\1 AND 1',
                                $dbh->getAuthParam('auth_user_exists'));
            $sql = str_replace('WHERE AND 1','', $sql);
            if (isa($dbi, 'ADOConnection')) {
                $db_result = $dbi->Execute($sql);
                foreach ($db_result->GetArray() as $u) {
                   $users = array_merge($users, array_values($u));
                }
            } elseif (isa($dbi, 'DB_common')) {
                $users = array_merge($users, $dbi->getCol($sql));
            }
        }

        // remove empty and duplicate users
        $result = array();
        foreach ($users as $u) {
            if (empty($u) or in_array($u,$result)) continue;
            $result[] = $u;
        }
        return $result;
    }

    /**
     * Determines all of the members of a particular group.
     *
     * This method is an abstraction.  The group is ignored, an error is sent,
     * and an empty array is returned
     * @param string $group Name of the group to get the full membership list of.
     * @return array Array of usernames that have joined the group (always empty).
     */
    function getMembersOf($group){
        if ($this->specialGroup($group)) {
            return $this->getSpecialMembersOf($group);
        }
        trigger_error(__sprintf("Method '%s' not implemented in this GROUP_METHOD %s",
                                'getMembersOf', GROUP_METHOD),
                      E_USER_WARNING);
        return array();
    }

    function getSpecialMembersOf($group) {
        //$request = &$this->request;
        $all = $this->_allUsers();
        $users = array();
        switch ($group) {
        case GROUP_EVERY:
            return $all;
        case GROUP_ANONYMOUS:
            return $users;
        case GROUP_BOGOUSER:
            foreach ($all as $u) {
                if (isWikiWord($u)) $users[] = $u;
            }
            return $users;
        case GROUP_SIGNED:
            foreach ($all as $u) {
                $user = WikiUser($u);
                if ($user->isSignedIn()) $users[] = $u;
            }
            return $users;
        case GROUP_AUTHENTICATED:
            foreach ($all as $u) {
                $user = WikiUser($u);
                if ($user->isAuthenticated()) $users[] = $u;
            }
            return $users;
        case GROUP_ADMIN:
            foreach ($all as $u) {
                $user = WikiUser($u);
                if (isset($user->_level) and $user->_level == WIKIAUTH_ADMIN)
                    $users[] = $u;
            }
            return $users;
        case GROUP_OWNER:
        case GROUP_CREATOR:
            // this could get complex so just return an empty array
            return false;
        default:
            trigger_error(__sprintf("Unknown special group '%s'", $group),
                          E_USER_WARNING);
        }
    }

    /**
     * Add the current or specified user to a group.
     *
     * This method is an abstraction.  The group and user are ignored, an error
     * is sent, and false (not added) is always returned.
     * @param string $group User added to this group.
     * @param string $user Username to add to the group (default = current user).
     * @return bool On true user was added, false if not.
     */
    function setMemberOf($group, $user = false){
        trigger_error(__sprintf("Method '%s' not implemented in this GROUP_METHOD %s",
                                'setMemberOf', GROUP_METHOD),
                      E_USER_WARNING);
        return false;
    }

    /**
     * Remove the current or specified user to a group.
     *
     * This method is an abstraction.  The group and user are ignored, and error
     * is sent, and false (not removed) is always returned.
     * @param string $group User removed from this group.
     * @param string $user Username to remove from the group (default = current user).
     * @return bool On true user was removed, false if not.
     */
    function removeMemberOf($group, $user = false){
        trigger_error(__sprintf("Method '%s' not implemented in this GROUP_METHOD %s",
                                'removeMemberOf', GROUP_METHOD),
                      E_USER_WARNING);
        return false;
    }
}

/**
 * GroupNone disables all Group funtionality
 *
 * All of the GroupNone functions return false or empty values to indicate failure or
 * no results.  Use GroupNone if group controls are not desired.
 * @author Joby Walker <zorloc@imperium.org>
 */
class GroupNone extends WikiGroup{

    /**
     * Constructor
     *
     * Ignores the parameter provided.
     * @param object $request The global WikiRequest object - ignored.
     */
    function GroupNone() {
        //$this->request = &$GLOBALS['request'];
        return;
    }

    /**
     * Determines if the current user is a member of a group.
     *
     * The group is ignored and false (not a member of the group) is returned.
     * @param string $group Name of the group to check for membership (ignored).
     * @return boolean True if user is a member, else false (always false).
     */
    function isMember($group){
        if ($this->specialGroup($group)) {
            return $this->isSpecialMember($group);
        } else {
            return false;
        }
    }

    /**
     * Determines all of the groups of which the current user is a member.
     *
     * The group is ignored and an empty array (a member of no groups) is returned.
     * @param string $group Name of the group to check for membership (ignored).
     * @return array Array of groups to which the user belongs (always empty).
     */
    function getAllGroupsIn(){
        return array();
    }

    /**
     * Determines all of the members of a particular group.
     *
     * The group is ignored and an empty array (a member of no groups) is returned.
     * @param string $group Name of the group to check for membership (ignored).
     * @return array Array of groups user belongs to (always empty).
     */
    function getMembersOf($group){
        return array();
    }

}

/**
 * GroupWikiPage provides group functionality via pages within the Wiki.
 *
 * GroupWikiPage is the Wiki way of managing a group.  Every group will have
 * a page. To modify the membership of the group, one only needs to edit the
 * membership list on the page.
 * @author Joby Walker <zorloc@imperium.org>
 */
class GroupWikiPage extends WikiGroup{

    /**
     * Constructor
     *
     * Initializes the three superclass instance variables
     * @param object $request The global WikiRequest object.
     */
    function GroupWikiPage() {
        //$this->request = &$GLOBALS['request'];
        $this->username = $this->_getUserName();
        //$this->username = null;
        $this->membership = array();
    }

    /**
     * Determines if the current user is a member of a group.
     *
     * To determine membership in a particular group, this method checks the
     * superclass instance variable $membership to see if membership has
     * already been determined.  If not, then the group page is parsed to
     * determine membership.
     * @param string $group Name of the group to check for membership.
     * @return boolean True if user is a member, else false.
     */
    function isMember($group){
        if (isset($this->membership[$group])) {
            return $this->membership[$group];
        }
        global $request;
        $group_page = $request->getPage($group);
        if ($this->_inGroupPage($group_page)) {
            $this->membership[$group] = true;
            return true;
        }
        $this->membership[$group] = false;
        // Let grouppages override certain defaults, such as members of admin
        if ($this->specialGroup($group)) {
            return $this->isSpecialMember($group);
        }
        return false;
    }

    /**
    * Private method to take a WikiDB_Page and parse to determine if the
    * current_user is a member of the group.
    * @param object $group_page WikiDB_Page object for the group's page
    * @return boolean True if user is a member, else false.
    * @access private
    */
    function _inGroupPage($group_page, $strict=false){
        $group_revision = $group_page->getCurrentRevision();
        if ($group_revision->hasDefaultContents()) {
            $group = $group_page->getName();
            if ($strict) trigger_error(sprintf(_("Group page '%s' does not exist"), $group),
                                       E_USER_WARNING);
            return false;
        }
        $contents = $group_revision->getContent();
        $match = '/^\s*[\*\#]+\s*\[?' . $this->username . '\]?(\s|$)/';
        foreach ($contents as $line){
            if (preg_match($match, $line)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines all of the groups of which the current user is a member.
     *
     * Checks the root Group page ('CategoryGroup') for the list of all groups,
     * then checks each group to see if the current user is a member.
     * @param string $group Name of the group to check for membership.
     * @return array Array of groups to which the user belongs.
     */
    function getAllGroupsIn(){
        $membership = array();

        $specialgroups = $this->specialGroups();
        foreach ($specialgroups as $group) {
            $this->membership[$group] = $this->isSpecialMember($group);
        }

        global $request;
        $dbh = &$request->_dbi;
        $master_page = $request->getPage(CATEGORY_GROUP_PAGE);
        $master_list = $master_page->getLinks(true);
        while ($group_page = $master_list->next()){
            $group = $group_page->getName();
            $this->membership[$group] = $this->_inGroupPage($group_page);
        }
        foreach ($this->membership as $g => $bool) {
            if ($bool) $membership[] = $g;
        }
        return $membership;
    }

    /**
     * Determines all of the members of a particular group.
     *
     * Checks a group's page to return all the current members.  Currently this
     * method is disabled and triggers an error and returns an empty array.
     * @param string $group Name of the group to get the full membership list of.
     * @return array Array of usernames that have joined the group (always empty).
     */
    function getMembersOf($group){
        if ($this->specialGroup($group))
            return $this->getSpecialMembersOf($group);

        $group_page = $GLOBALS['request']->getPage($group);
        $group_revision = $group_page->getCurrentRevision();
        if ($group_revision->hasDefaultContents()) {
            trigger_error(sprintf(_("Group %s does not exist"),$group), E_USER_WARNING);
            return array();
        }
        $contents = $group_revision->getContent();
        // This is not really a reliable way to check if a string is a username. But better than nothing.
        $match = '/^(\s*[\*\#]+\s*\[?)(\w+)(\]?\s*)$/';
        $members = array();
        foreach ($contents as $line){
            if (preg_match($match, $line, $matches)){
                $members[] = $matches[2];
            }
        }
        return $members;
    }
}

/**
 * GroupDb is configured by $DbAuthParams[] statements
 *
 * Fixme: adodb
 * @author ReiniUrban
 */
class GroupDb extends WikiGroup {

    var $_is_member, $_group_members, $_user_groups;

    /**
     * Constructor
     *
     * @param object $request The global WikiRequest object. ignored
     */
    function GroupDb() {
        global $DBAuthParams, $DBParams;
        //$this->request = &$GLOBALS['request'];
        $this->username = $this->_getUserName();
        $this->membership = array();

        if (empty($DBAuthParams['group_members']) or
            empty($DBAuthParams['user_groups']) or
            empty($DBAuthParams['is_member'])) {
            trigger_error(_("No or not enough GROUP_DB SQL statements defined"),
                          E_USER_WARNING);
            return new GroupNone();
        }
        // FIXME: This only works with ENABLE_USER_NEW
        if (empty($this->user)) {
            // use _PassUser::prepare instead
            if (isa($request->getUser(),'_PassUser'))
                $user = $request->getUser();
            else
                $user = new _PassUser($this->username);
        } elseif (!isa($this->user, '_PassUser')) {
            $user = new _PassUser($this->username);
        } else {
            $user =& $this->user;
        }
        if (isa($this->user, '_PassUser')) { // TODO: safety by Charles Corrigan
            $this->_is_member = $user->prepare($DBAuthParams['is_member'],
                                           array('userid','groupname'));
            $this->_group_members = $user->prepare($DBAuthParams['group_members'],'groupname');
            $this->_user_groups = $user->prepare($DBAuthParams['user_groups'],'userid');
            $this->dbh = $user->_auth_dbi;
        }
    }
}

/**
 * PearDB methods
 *
 * @author ReiniUrban
 */
class GroupDb_PearDB extends GroupDb {

    /**
     * Determines if the current user is a member of a database group.
     *
     * @param string $group Name of the group to check for membership.
     * @return boolean True if user is a member, else false.
     */
    function isMember($group) {
        if (isset($this->membership[$group])) {
            return $this->membership[$group];
        }
        $dbh = & $this->dbh;
        $db_result = $dbh->query(sprintf($this->_is_member,
                                         $dbh->quote($this->username),
                                         $dbh->quote($group)));
        if ($db_result->numRows() > 0) {
            $this->membership[$group] = true;
            return true;
        }
        $this->membership[$group] = false;
        // Let override certain defaults, such as members of admin
        if ($this->specialGroup($group))
            return $this->isSpecialMember($group);
        return false;
    }

    /**
     * Determines all of the groups of which the current user is a member.
     *
     * then checks each group to see if the current user is a member.
     * @param string $group Name of the group to check for membership.
     * @return array Array of groups to which the user belongs.
     */
    function getAllGroupsIn(){
        $membership = array();

        $dbh = & $this->dbh;
        $db_result = $dbh->query(sprintf($this->_user_groups, $dbh->quote($this->username)));
        if ($db_result->numRows() > 0) {
            while (list($group) = $db_result->fetchRow(DB_FETCHMODE_ORDERED)) {
                $membership[] = $group;
                $this->membership[$group] = true;
            }
        }

        $specialgroups = $this->specialGroups();
        foreach ($specialgroups as $group) {
            if ($this->isMember($group)) {
                $membership[] = $group;
            }
        }
        return $membership;
    }

    /**
     * Determines all of the members of a particular group.
     *
     * Checks a group's page to return all the current members.  Currently this
     * method is disabled and triggers an error and returns an empty array.
     * @param string $group Name of the group to get the full membership list of.
     * @return array Array of usernames that have joined the group.
     */
    function getMembersOf($group){

        $members = array();
        $dbh = & $this->dbh;
        $db_result = $dbh->query(sprintf($this->_group_members,$dbh->quote($group)));
        if ($db_result->numRows() > 0) {
            while (list($userid) = $db_result->fetchRow(DB_FETCHMODE_ORDERED)) {
                $members[] = $userid;
            }
        }
        // add certain defaults, such as members of admin
        if ($this->specialGroup($group))
            $members = array_merge($members, $this->getSpecialMembersOf($group));
        return $members;
    }
}

/**
 * ADODB methods
 *
 * @author ReiniUrban
 */
class GroupDb_ADODB extends GroupDb {

    /**
     * Determines if the current user is a member of a database group.
     *
     * @param string $group Name of the group to check for membership.
     * @return boolean True if user is a member, else false.
     */
    function isMember($group) {
        if (isset($this->membership[$group])) {
            return $this->membership[$group];
        }
        $dbh = & $this->dbh;
        $rs = $dbh->Execute(sprintf($this->_is_member,$dbh->qstr($this->username),
                                    $dbh->qstr($group)));
        if ($rs->EOF) {
            $rs->Close();
        } else {
            if ($rs->numRows() > 0) {
                $this->membership[$group] = true;
                $rs->Close();
                return true;
            }
        }
        $this->membership[$group] = false;
        if ($this->specialGroup($group))
            return $this->isSpecialMember($group);

        return false;
    }

    /**
     * Determines all of the groups of which the current user is a member.
     * then checks each group to see if the current user is a member.
     *
     * @param string $group Name of the group to check for membership.
     * @return array Array of groups to which the user belongs.
     */
    function getAllGroupsIn(){
        $membership = array();

        $dbh = & $this->dbh;
        $rs = $dbh->Execute(sprintf($this->_user_groups, $dbh->qstr($this->username)));
        if (!$rs->EOF and $rs->numRows() > 0) {
            while (!$rs->EOF) {
                $group = reset($rs->fields);
                $membership[] = $group;
                $this->membership[$group] = true;
                $rs->MoveNext();
            }
        }
        $rs->Close();

        $specialgroups = $this->specialGroups();
        foreach ($specialgroups as $group) {
            if ($this->isMember($group)) {
                $membership[] = $group;
            }
        }
        return $membership;
    }

    /**
     * Determines all of the members of a particular group.
     *
     * @param string $group Name of the group to get the full membership list of.
     * @return array Array of usernames that have joined the group.
     */
    function getMembersOf($group){
        $members = array();
        $dbh = & $this->dbh;
        $rs = $dbh->Execute(sprintf($this->_group_members,$dbh->qstr($group)));
        if (!$rs->EOF and $rs->numRows() > 0) {
            while (!$rs->EOF) {
                $members[] = reset($rs->fields);
                $rs->MoveNext();
            }
        }
        $rs->Close();
        // add certain defaults, such as members of admin
        if ($this->specialGroup($group))
            $members = array_merge($members, $this->getSpecialMembersOf($group));
        return $members;
    }
}

/**
 * GroupFile is configured by AUTH_GROUP_FILE
 * groupname: user1 user2 ...
 *
 * @author ReiniUrban
 */
class GroupFile extends WikiGroup {

    /**
     * Constructor
     *
     * @param object $request The global WikiRequest object.
     */
    function GroupFile(){
        //$this->request = &$GLOBALS['request'];
        $this->username = $this->_getUserName();
        //$this->username = null;
        $this->membership = array();

        if (!defined('AUTH_GROUP_FILE')) {
            trigger_error(sprintf(_("%s: not defined"), "AUTH_GROUP_FILE"),
                          E_USER_WARNING);
            return false;
        }
        if (!file_exists(AUTH_GROUP_FILE)) {
            trigger_error(sprintf(_("Cannot open AUTH_GROUP_FILE %s"), AUTH_GROUP_FILE),
                          E_USER_WARNING);
            return false;
        }
        require_once('lib/pear/File_Passwd.php');
        $this->_file = new File_Passwd(AUTH_GROUP_FILE,false,AUTH_GROUP_FILE.".lock");
    }

    /**
     * Determines if the current user is a member of a group.
     *
     * To determine membership in a particular group, this method checks the
     * superclass instance variable $membership to see if membership has
     * already been determined.  If not, then the group file is parsed to
     * determine membership.
     * @param string $group Name of the group to check for membership.
     * @return boolean True if user is a member, else false.
     */
    function isMember($group) {
        //$request = $this->request;
        //$username = $this->username;
        if (isset($this->membership[$group])) {
            return $this->membership[$group];
        }

        if (is_array($this->_file->users)) {
          foreach ($this->_file->users as $g => $u) {
            $users = explode(' ',$u);
            if (in_array($this->username,$users)) {
                $this->membership[$group] = true;
                return true;
            }
          }
        }
        $this->membership[$group] = false;
        if ($this->specialGroup($group))
            return $this->isSpecialMember($group);
        return false;
    }

    /**
     * Determines all of the groups of which the current user is a member.
     *
     * then checks each group to see if the current user is a member.
     * @param string $group Name of the group to check for membership.
     * @return array Array of groups to which the user belongs.
     */
    function getAllGroupsIn(){
        //$username = $this->_getUserName();
        $membership = array();

        if (is_array($this->_file->users)) {
          foreach ($this->_file->users as $group => $u) {
            $users = explode(' ',$u);
            if (in_array($this->username,$users)) {
                $this->membership[$group] = true;
                $membership[] = $group;
            }
          }
        }

        $specialgroups = $this->specialGroups();
        foreach ($specialgroups as $group) {
            if ($this->isMember($group)) {
                $this->membership[$group] = true;
                $membership[] = $group;
            }
        }
        return $membership;
    }

    /**
     * Determines all of the members of a particular group.
     *
     * Return all the current members.
     * @param string $group Name of the group to get the full membership list of.
     * @return array Array of usernames that have joined the group.
     */
    function getMembersOf($group){
        $members = array();
        if (!empty($this->_file->users[$group])) {
            $members = explode(' ',$this->_file->users[$group]);
        }
        if ($this->specialGroup($group)) {
            $members = array_merge($members, $this->getSpecialMembersOf($group));
        }
        return $members;
    }
}

/**
 * Ldap is configured in index.php
 *
 * @author ReiniUrban
 */
class GroupLdap extends WikiGroup {

    /**
     * Constructor
     *
     * @param object $request The global WikiRequest object.
     */
    function GroupLdap(){
        //$this->request = &$GLOBALS['request'];
        $this->username = $this->_getUserName();
        $this->membership = array();

        if (!defined("LDAP_AUTH_HOST")) {
            trigger_error(sprintf(_("%s not defined"), "LDAP_AUTH_HOST"),
                          E_USER_WARNING);
            return false;
        }
        // We should ignore multithreaded environments, not generally windows.
        // CGI does work.
        if (! function_exists('ldap_connect') and (!isWindows() or isCGI())) {
            // on MacOSX >= 4.3 you'll need PHP_SHLIB_SUFFIX instead.
            dl("ldap".defined('PHP_SHLIB_SUFFIX') ? PHP_SHLIB_SUFFIX : DLL_EXT);
            if (! function_exists('ldap_connect')) {
                trigger_error(_("No LDAP in this PHP version"), E_USER_WARNING);
                return false;
            }
        }
        if (!defined("LDAP_BASE_DN"))
            define("LDAP_BASE_DN",'');
        $this->base_dn = LDAP_BASE_DN;
        // if no users ou (organizational unit) is defined,
        // then take out the ou= from the base_dn (if exists) and append a default
        // from users and group
        if (!LDAP_OU_USERS)
            if (strstr(LDAP_BASE_DN, "ou="))
                $this->base_dn = preg_replace("/(ou=\w+,)?()/","\$2", LDAP_BASE_DN);

        if (!isset($this->user) or !isa($this->user, '_LDAPPassUser'))
            $this->_user = new _LDAPPassUser('LdapGroupTest'); // to have a valid username
        else
            $this->_user =& $this->user;
    }

    /**
     * Determines if the current user is a member of a group.
     * Not ready yet!
     *
     * @param string $group Name of the group to check for membership.
     * @return boolean True if user is a member, else false.
     */
    function isMember($group) {
        if (isset($this->membership[$group])) {
            return $this->membership[$group];
        }
        //$request = $this->request;
        //$username = $this->_getUserName();
        $this->membership[$group] = in_array($this->username,$this->getMembersOf($group));
        if ($this->membership[$group])
            return true;
        if ($this->specialGroup($group))
            return $this->isSpecialMember($group);
    }

    /**
     * Determines all of the groups of which the current user is a member.
     *
     * @param string $group Name of the group to check for membership.
     * @return array Array of groups to which the user belongs.
     */
    function getAllGroupsIn(){
        //$request = &$this->request;
        //$username = $this->_getUserName();
        $membership = array();

        $specialgroups = $this->specialGroups();
        foreach ($specialgroups as $group) {
            if ($this->isMember($group)) {
                $this->membership[$group] = true;
                $membership[] = $group;
            }
        }

        // must be a valid LDAP server, and username must not contain a wildcard
        if ($ldap = $this->_user->_init()) {
            $st_search = LDAP_SEARCH_FIELD ? LDAP_SEARCH_FIELD."=".$this->username
                               : "uid=".$this->username;
            $sr = ldap_search($ldap, (LDAP_OU_USERS ? LDAP_OU_USERS : "ou=Users")
                              .($this->base_dn ? ",".$this->base_dn : ''),
                              $st_search);
            if (!$sr) {
         $this->_user->_free();
                return $this->membership;
            }
            $info = ldap_get_entries($ldap, $sr);
            if (empty($info["count"])) {
         $this->_user->_free();
                return $this->membership;
            }
            for ($i = 0; $i < $info["count"]; $i++) {
                if ($info[$i]["gidNumber"]["count"]) {
                    $gid = $info[$i]["gidnumber"][0];
                    $sr2 = ldap_search($ldap, (LDAP_OU_GROUP ? LDAP_OU_GROUP : "ou=Groups")
                                       .($this->base_dn ? ",".$this->base_dn : ''),
                                       "gidNumber=$gid");
                    if ($sr2) {
                        $info2 = ldap_get_entries($ldap, $sr2);
                        if (!empty($info2["count"]))
                            $membership[] =  $info2[0]["cn"][0];
                    }
                }
            }
        } else {
            trigger_error(fmt("Unable to connect to LDAP server %s", LDAP_AUTH_HOST),
                          E_USER_WARNING);
        }
        $this->_user->_free();
        //ldap_close($ldap);
        $this->membership = $membership;
        return $membership;
    }

    /**
     * Determines all of the members of a particular group.
     *
     * Return all the members of the given group. LDAP just returns the gid of each user
     * @param string $group Name of the group to get the full membership list of.
     * @return array Array of usernames that have joined the group.
     */
    function getMembersOf($group){
        $members = array();
        if ($ldap = $this->_user->_init()) {
            $base_dn = (LDAP_OU_GROUP ? LDAP_OU_GROUP : "ou=Groups")
                .($this->base_dn ? ",".$this->base_dn : '');
            $sr = ldap_search($ldap, $base_dn, "cn=$group");
            if ($sr)
                $info = ldap_get_entries($ldap, $sr);
            else {
                $info = array('count' => 0);
                trigger_error("LDAP_SEARCH: base=\"$base_dn\" \"(cn=$group)\" failed", E_USER_NOTICE);
            }
            $base_dn = (LDAP_OU_USERS ? LDAP_OU_USERS : "ou=Users")
                .($this->base_dn ? ",".$this->base_dn : '');
            for ($i = 0; $i < $info["count"]; $i++) {
                $gid = $info[$i]["gidNumber"][0];
                //uid=* would be better probably
                $sr2 = ldap_search($ldap, $base_dn, "gidNumber=$gid");
                if ($sr2) {
                    $info2 = ldap_get_entries($ldap, $sr2);
                    for ($j = 0; $j < $info2["count"]; $j++) {
                        $members[] = $info2[$j]["cn"][0];
                    }
                } else {
                    trigger_error("LDAP_SEARCH: base=\"$base_dn\" \"(gidNumber=$gid)\" failed", E_USER_NOTICE);
                }
            }
        }
        $this->_user->_free();
        //ldap_close($ldap);

        if ($this->specialGroup($group)) {
            $members = array_merge($members, $this->getSpecialMembersOf($group));
        }
        return $members;
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
