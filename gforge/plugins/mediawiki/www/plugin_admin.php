<?php
/*-
 * MediaWiki Plugin for FusionForge
 *
 * Copyright © 2010
 *      Thorsten Glaser <t.glaser@tarent.de>
 * All rights reserved.
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *-
 * Admin page for the plugin
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';

$user = session_get_user();
if (!$user || !is_object($user) || $user->isError() || !$user->isActive())
	exit_error("Invalid User", "Cannot process your request for this user.");

$gid = getIntFromRequest("group_id", -1);
if ($gid == -1)
	$group = false;
else
	$group = group_get_object($gid);
if (!$group)
	exit_error("Invalid Project", "Nonexistent Project");
if (!$group->usesPlugin("mediawiki"))
	exit_error("Invalid Project", "Project does not use MediaWiki Plugin");

$userperm = $group->getPermission($user);
if (!$userperm->IsMember())
	exit_error("Access Denied", "You are not a member of this project");
if (!$userperm->IsAdmin())
	exit_error("Access Denied", "You are not an admin of this project");

site_project_header(array(
	"title" => "MediaWiki Plugin Admin",
	"pagename" => "MediaWiki Project Admin",
	"sectionvals" => array($group->getPublicName()),
	"toptab" => "admin",
	"group" => $gid,
    ));

echo "<p>Dummy page.</p>\n";

site_project_footer(array());
?>
