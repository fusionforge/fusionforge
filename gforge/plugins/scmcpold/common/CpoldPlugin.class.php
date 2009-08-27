<?php
/** FusionForge Bazaar plugin
 *
 * Copyright 2009, Roland Mas
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

class CpoldPlugin extends SCMPlugin {
	function CpoldPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmcpold';
		$this->text = 'CPOLD';
		$this->hooks[] = 'scm_page';
		$this->hooks[] = 'scm_admin_update';
		$this->hooks[] = 'scm_admin_page';
 		$this->hooks[] = 'scm_stats';
		$this->hooks[] = 'scm_plugin';
		$this->hooks[] = 'scm_createrepo';
		
		require_once $gfconfig.'plugins/scmcpold/config.php' ;
		
		$this->default_cpold_server = $default_cpold_server ;
		$this->enabled_by_default = $enabled_by_default ;
		$this->cpold_root = $cpold_root;
		
		$this->register () ;
	}
	
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
