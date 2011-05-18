<?php // -*-php-*- $Id: wikiadmin.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
 * Copyright (C) 2009-2010 Marc-Etienne Vargenau, Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Wiki Configurator ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once forge_get_config('plugins_path').'wiki/common/WikiPlugin.class.php';
require_once forge_get_config('plugins_path').'wiki/common/wikiconfig.class.php';

$user = session_get_user(); // get the session user

if (!$user || !is_object($user)) {
    exit_error(_('Invalid User'),'home');
} else if ( $user->isError()) {
    exit_error($user->getErrorMessage(),'home');
} else if ( !$user->isActive()) {
    exit_error(_('User not active'),'home');
}

$type = getStringFromRequest('type');
$group_id = getIntFromRequest('group_id');
$pluginname = 'wiki';
$config = getArrayFromRequest('config');

if (!$type) {
    exit_missing_param('',array(_('No TYPE specified')),'home');
} elseif (!$group_id) {
    exit_missing_param('',array(_('No ID specified')),'home');
} else {
    if ($type == 'admin_post') {
        $group = group_get_object($group_id);
        if ( !$group) {
            exit_no_group();
        }
        if (!($group->usesPlugin($pluginname))) { //check if the group has the wiki plugin active
            exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
        }
        $userperm = $group->getPermission($user); //we'll check if the user belongs to the group
        if ( !$userperm->IsMember()) {
            exit_permission_denied(_('You are not a member of this project'),'home');
        }
        //only project admin can access here
        if ( $userperm->isAdmin() ) {

            $wc = new WikiConfig($group_id);

            foreach ($wc->getWikiConfigNames() as $c) {
                if ( ! array_key_exists($c, $config)) {
                    $config[$c] = 0;
                }
            }

            foreach ($config as $config_name => $config_value) {
                $r = $wc->updateWikiConfig($config_name, $config_value);
                if (!$r) exit_error("Error", $wc->getErrorMessage());
            }

            $type = 'admin';
            $feedback = _('Configuration saved.');
        } else {
            exit_permission_denied(_('You are not a project Admin'),'home');
        }
    }
    if ($type == 'admin') {
        $group = group_get_object($group_id);
        if ( !$group) {
            exit_no_group();
        }
        if ( ! ($group->usesPlugin ($pluginname)) ) {//check if the group has the plugin active
            exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
        }
        $userperm = $group->getPermission($user); //we'll check if the user belongs to the group
        if ( !$userperm->IsMember()) {
            exit_permission_denied(_('You are not a member of this project'),'home');
        }
        //only project admin can access here
        if ( $userperm->isAdmin() ) {
            site_project_header(array('title' => _("Configuration for your project's wiki"),
                                      'pagename' => $pluginname,
                                      'group'    => $group_id,
                                      'toptab'   => 'wiki',
                                      'sectionvals' => array(group_getname($group_id))));

            $wc = new WikiConfig($group_id);

            print "<table>\n";
            print "<tr>\n";
            print "<td>\n";
            print "<fieldset>\n";
            print "<legend>"._('Wiki Configuration')."</legend>\n";
            print "<form action=\"/wiki/wikiadmin.php\" method=\"post\">\n";
            print "<input type=\"hidden\" name=\"group_id\" value=\"$group_id\" />\n";
            print "<input type=\"hidden\" name=\"pluginname\" value=\"$pluginname\" />\n";
            print "<input type=\"hidden\" name=\"type\" value=\"admin_post\" />\n";

            print '<table class="listing">';
            print "\n<thead>\n<tr>\n<th>".
                    _("Parameter").
                    "</th>" .
                    "<th>".
                    _("Value").
                    "</th>\n" .
                    "</tr>\n</thead>\n";

            foreach ($wc->getWikiConfigNames() as $c) {
                $checked = $wc->getWikiConfig($c) ? ' checked="checked"' : '';
                $desc = $wc->getWikiConfigDescription($c);

                print "<tr>\n<td>$desc</td>\n" .
                      "<td align=\"center\">" .
                      "<input type=\"checkbox\" name=\"config[$c]\" value=\"1\"$checked /></td>\n" .
                      "</tr>\n";
            }
            print "</table>\n";
            print "<p align=\"right\"><input type=\"submit\" value=\"" .
                            _("Save Configuration").
                            "\" /></p>";
            print "</form>\n";
            print "</fieldset>\n";
            print "</td>\n";
            print "</tr>\n";
            print "</table>\n";
        } else {
            exit_permission_denied(_('You are not a project Admin'),'home');
        }
    }
}

site_project_footer(array());

?>
