<?php

/**
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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

require_once 'lib/Template.php';
/**
 * Used to debug auth problems and settings.
 * This plugin is only testing purposes.
 * if DEBUG is false, only admin can call it, which is of no real use.
 *
 * Warning! This may display db and user passwords in cleartext.
 */
class WikiPlugin_DebugAuthInfo
    extends WikiPlugin
{
    function getDescription()
    {
        return _("Display general and user specific auth information.");
    }

    function getDefaultArguments()
    {
        return array('userid' => '');
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        $args = $this->getArgs($argstr, $request);
        extract($args);
        if (empty($userid) or $userid == $request->_user->UserName()) {
            $user = $request->_user;
            $userid = $user->UserName();
        } else {
            $user = WikiUser($userid);
        }
        if (!$user->isAdmin() and !(DEBUG && _DEBUG_LOGIN)) {
            $request->_notAuthorized(WIKIAUTH_ADMIN);
            $this->disabled("! user->isAdmin");
        }

        $html = HTML(HTML::h3(fmt("General Auth Settings")));
        $table = HTML::table(array('class' => 'bordered'));
        $table->pushContent($this->show_hash("AUTH DEFINES",
            $this->buildConstHash(
                array("ENABLE_USER_NEW", "ALLOW_ANON_USER",
                    "ALLOW_ANON_EDIT", "ALLOW_BOGO_LOGIN",
                    "REQUIRE_SIGNIN_BEFORE_EDIT", "ALLOW_USER_PASSWORDS",
                    "PASSWORD_LENGTH_MINIMUM", "USE_DB_SESSION"))));
        if ((defined('ALLOW_LDAP_LOGIN') && ALLOW_LDAP_LOGIN) or in_array("LDAP", $GLOBALS['USER_AUTH_ORDER']))
            $table->pushContent($this->show_hash("LDAP DEFINES",
                $this->buildConstHash(array("LDAP_AUTH_HOST", "LDAP_BASE_DN"))));
        if ((defined('ALLOW_IMAP_LOGIN') && ALLOW_IMAP_LOGIN) or in_array("IMAP", $GLOBALS['USER_AUTH_ORDER']))
            $table->pushContent($this->show_hash("IMAP DEFINES", array("IMAP_AUTH_HOST" => IMAP_AUTH_HOST)));
        if (defined('AUTH_USER_FILE') or in_array("File", $GLOBALS['USER_AUTH_ORDER']))
            $table->pushContent($this->show_hash("AUTH_USER_FILE",
                $this->buildConstHash(array("AUTH_USER_FILE",
                    "AUTH_USER_FILE_STORABLE"))));
        if (defined('GROUP_METHOD'))
            $table->pushContent($this->show_hash("GROUP_METHOD",
                $this->buildConstHash(array("GROUP_METHOD", "AUTH_GROUP_FILE", "GROUP_LDAP_QUERY"))));
        $table->pushContent($this->show_hash("\$USER_AUTH_ORDER[]", $GLOBALS['USER_AUTH_ORDER']));
        $table->pushContent($this->show_hash("USER_AUTH_POLICY", array("USER_AUTH_POLICY" => USER_AUTH_POLICY)));
        $DBParams = $GLOBALS['DBParams'];
        $DBParams['dsn'] = class_exists('WikiDB_SQL') ? WikiDB_SQL::view_dsn($DBParams['dsn']) : '';
        $table->pushContent($this->show_hash("\$DBParams[]", $DBParams));
        $DBAuthParams = $GLOBALS['DBAuthParams'];
        if (isset($DBAuthParams['auth_dsn']) and class_exists('WikiDB_SQL'))
            $DBAuthParams['auth_dsn'] = WikiDB_SQL::view_dsn($DBAuthParams['auth_dsn']);
        else
            $DBAuthParams['auth_dsn'] = '';
        unset($DBAuthParams['dummy']);
        $table->pushContent($this->show_hash("\$DBAuthParams[]", $DBAuthParams));
        $html->pushContent($table);
        $html->pushContent(HTML(HTML::h3(fmt("Personal Auth Settings for “%s”", $userid))));
        if (!$user) {
            $html->pushContent(HTML::p(fmt("No userid")));
        } else {
            $table = HTML::table(array('class' => 'bordered'));
            //$table->pushContent(HTML::tr(HTML::td(array('colspan' => 2))));
            $userdata = obj2hash($user, array('_dbi', '_request', 'password', 'passwd'));
            if (isa($user, "_FilePassUser")) {
                foreach ($userdata['_file']->users as $u => $p) {
                    $userdata['_file']->users[$u] = "<hidden>";
                }
            }
            $table->pushContent($this->show_hash("User: Object of " . get_class($user), $userdata));
            if (ENABLE_USER_NEW) {
                $group = &$request->getGroup();
                $groups = $group->getAllGroupsIn();
                $groupdata = obj2hash($group, array('_dbi', '_request', 'password', 'passwd'));
                unset($groupdata['request']);
                $table->pushContent($this->show_hash("Group: Object of " . get_class($group), $groupdata));
                $groups = $group->getAllGroupsIn();
                $groupdata = array('getAllGroupsIn' => $groups);
                foreach ($groups as $g) {
                    $groupdata["getMembersOf($g)"] = $group->getMembersOf($g);
                    $groupdata["isMember($g)"] = $group->isMember($g);
                }
                $table->pushContent($this->show_hash("Group Methods: ", $groupdata));
            }
            $html->pushContent($table);
        }
        return $html;
    }

    private function show_hash($heading, $hash, $depth = 0)
    {
        static $seen = array();
        static $max_depth = 0;
        $rows = array();
        $max_depth++;
        if ($max_depth > 35) return $heading;

        if ($heading)
            $rows[] = HTML::tr(array(
                    'style' => 'color:#000;background-color:#ffcccc'),
                HTML::td(array('colspan' => 2,
                        'style' => 'color:#000'),
                    $heading));
        if (is_object($hash))
            $hash = obj2hash($hash);
        if (!empty($hash)) {
            ksort($hash);
            foreach ($hash as $key => $val) {
                if (is_object($val)) {
                    $heading = "Object of " . get_class($val);
                    if ($depth > 3) $val = $heading;
                    elseif ($heading == "Object of wikidb_sql") $val = $heading; elseif (substr($heading, 0, 13) == "Object of db_") $val = $heading; elseif (!isset($seen[$heading])) {
                        //if (empty($seen[$heading])) $seen[$heading] = 1;
                        $val = HTML::table(array('class' => 'bordered'),
                            $this->show_hash($heading, obj2hash($val), $depth + 1));
                    } else {
                        $val = $heading;
                    }
                } elseif (is_array($val)) {
                    $heading = $key . "[]";
                    if ($depth > 3) $val = $heading;
                    elseif (!isset($seen[$heading])) {
                        //if (empty($seen[$heading])) $seen[$heading] = 1;
                        $val = HTML::table(array('class' => 'bordered'),
                            $this->show_hash($heading, $val, $depth + 1));
                    } else {
                        $val = $heading;
                    }
                }
                $rows[] = HTML::tr(HTML::td(array('align' => 'right',
                            'bgcolor' => '#ccc',
                            'style' => 'color:#000000'),
                        HTML(HTML::raw('&nbsp;'), $key,
                            HTML::raw('&nbsp;'))),
                    HTML::td(array('bgcolor' => '#fff',
                            'style' => 'color:#000000'),
                        $val ? $val : HTML::raw('&nbsp;'))
                );
                //if (empty($seen[$key])) $seen[$key] = 1;
            }
        }
        return $rows;
    }

    private function buildConstHash($constants)
    {
        $hash = array();
        foreach ($constants as $c) {
            $hash[$c] = defined($c) ? constant($c) : '<empty>';
            if ($hash[$c] === false) $hash[$c] = 'false';
            elseif ($hash[$c] === true) $hash[$c] = 'true';
        }
        return $hash;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
