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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

class globalSearchPlugin extends Plugin {
        function globalSearchPlugin () {
                $this->Plugin() ;
                $this->name = "globalsearch" ;
                $this->hooks[] = "site_admin_option_hook" ;
        }

        function CallHook ($hookname, $params) {
                global $Language, $G_SESSION, $HTML, $group_id;

                if ($hookname == "site_admin_option_hook") {
                        print '<li><a href="/plugins/globalsearch/edit_assoc_sites.php">'._("Admin Associated Sites").'</a></li>';
                }
        }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
