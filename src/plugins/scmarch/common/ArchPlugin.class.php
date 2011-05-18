<?php
/** FusionForge Arch plugin
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

forge_define_config_item ('default_server', 'scmarch', forge_get_config ('web_host')) ;
forge_define_config_item ('repos_path', 'scmarch', forge_get_config('chroot').'/scmrepos/arch') ;

class ArchPlugin extends SCMPlugin {
	function ArchPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmarch';
		$this->text = 'Arch';
		$this->hooks[] = 'scm_generate_snapshots' ;

		$this->register () ;
	}
	
	function getDefaultServer() {
		return forge_get_config('default_server', 'scmarch') ;
	}

	function getBlurb () {
		return _('<p>Documentation for GNU Arch (sometimes referred to as "tla") is available <a href="http://www.gnu.org/software/gnu-arch/">here</a>.</p>') ;
	}

	function createOrUpdateRepo ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
				
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		$repo = forge_get_config('repos_path', 'scmarch') . '/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project->getUnixName() ;

		$repo_exists = false ;
		if (!$repo_exists) {
			system ("mkdir -p $repo") ;
			system ("find $repo -type d | xargs chmod g+s") ;
		}

		system ("chgrp -R $unix_group $repo") ;
		if ($project->enableAnonSCM()) {
			system ("chmod -R g+wX,o+rX-w $repo") ;
		} else {
			system ("chmod -R g+wX,o-rwx $repo") ;
		}
	}

	function generateSnapshots ($params) {



		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		$group_name = $project->getUnixName() ;

		$snapshot = forge_get_config('scm_snapshots_path').'/'.$group_name.'-scm-latest.tar'.util_get_compressed_file_extension();
		$tarball = forge_get_config('scm_tarballs_path').'/'.$group_name.'-scmroot.tar'.util_get_compressed_file_extension();

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			unlink ($snapshot) ;
			unlink ($tarball) ;
			return false;
		}

		$toprepo = forge_get_config('repos_path', 'scmarch') ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		if (!is_dir ($repo) || !is_file ("$repo/format")) {
			unlink ($snapshot) ;
			unlink ($tarball) ;
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		system ("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball") ;
		chmod ("$tmp/tarball", 0644) ;
		copy ("$tmp/tarball", $tarball) ;
		unlink ("$tmp/tarball") ;
		system ("rm -rf $tmp") ;
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
