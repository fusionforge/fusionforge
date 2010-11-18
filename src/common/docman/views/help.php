<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $group_id; // id of the group
global $g; //id of the doc_group

echo '<div class="docmanDivIncluded">';
plugin_hook ("blocks", "doc help");
if ($g->useWebdav()) {
	echo '<p>'. _('Documents parsing is also available thru webdav') .'</p>';
	echo '<p>'. util_make_link('/docman/view.php/'.$group_id.'/webdav',_('Direct Webdav URL')) .'</p>';
}
echo '</div>';
?>
