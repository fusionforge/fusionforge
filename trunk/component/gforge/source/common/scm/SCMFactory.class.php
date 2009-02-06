<?php
/**
 * FusionForge source control management
 *
 * Copyright 2003-2004, Tim Perdue/GForge, LLC
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'include/PluginManager.class.php';

class SCMFactory extends Error {

	/**
	 * The scms array.
	 *
	 * @var  array  scms.
	 */
	var $scms;
	var $fetched_rows;

	/**
	 *  Constructor.
	 *
	 *	@return	boolean	success.
	 */
	function SCMFactory() {
		global $sys_use_scm;

		$this->Error();
		if (!$sys_use_scm) {
			$this->setError('SCMFactory::sys_use_scm');
			return false;
		}
		return true;
	}

	/**
	 *	getSCMs - get an array of Plugin SCM objects.
	 *
	 *	@return	array	The array of SCM objects.
	 */
	function &getSCMs() {
		$scm_plugins = array();
		if ($this->scms) {
			return $this->scms;
		}
		$hookParams['scm_plugins']=& $scm_plugins;
		plugin_hook("scm_plugin", $hookParams);
		$this->scms= $scm_plugins;
		return $this->scms;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
