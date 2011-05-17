<?php
/**
 * FusionForge globalsearch plugin
 *
 * Copyright 2003-2004, GForge, LLC
 * Copyright 2007-2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';

session_require_global_perm ('forge_admin');
include_once(forge_get_config('plugins_path').'/globalsearch/common/globalsearch_edit_utils.php');

$HTML->header(array('title'=>_('Edit associated forges for global search')));

echo '<h3>'._('Edit associated forges for global search').'</h3>
<p><a href="/admin/">'._("Site Admin Home").'</a></p>';

$function = getStringFromRequest ('function');
$id = getIntFromRequest ('id');

switch ($function) {
        case 'add' : {
                globalsearch_admin_table_add ();
                break;
        }
        case 'postadd' : {
                globalsearch_admin_table_postadd ();
                break;
        }
        case 'confirmdelete' : {
                globalsearch_admin_table_confirmdelete ($id);
                break;
        }
        case 'delete' : {
                globalsearch_admin_table_delete ($id);
                break;
        }
        case 'edit' : {
                globalsearch_admin_table_edit ($id);
                break;
        }
        case 'postedit' : {
                globalsearch_admin_table_postedit ($id);
                break;
        }
}

echo globalsearch_admin_table_show ();

$HTML->footer(array());

?>
