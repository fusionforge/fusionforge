<?php
/**
 * Project Admin page to show audit trail for group
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
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


require_once('pre.php');
require_once('www/project/admin/project_admin_utils.php');

$group_id = getIntFromRequest('group_id');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

project_admin_header(array('title'=>$Language->getText('project_admin_history','title'),'group'=>$group_id));

?>

<p><?php echo $Language->getText('project_admin_history','info') ?>.</p>
<?php
echo show_grouphistory($group_id);

project_admin_footer(array());
?>
