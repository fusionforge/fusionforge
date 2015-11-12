<?php
/**
 * Copyright (C) 2014 Roland Mas
 * Copyright (C) 2015  Inria (Sylvain Beucler)
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

require_once dirname(dirname(__FILE__)).'/SeleniumForge.php';

class SSHTest extends FForge_SeleniumTestCase
{
	// Needs to be member of a project
	public $fixture = 'projecta';

	function testSSH()
	{
		$this->loadAndCacheFixture();
		$this->switchUser(FORGE_ADMIN_USERNAME);

		$this->uploadSshKey();

		// Run the cronjobs
		$this->waitSystasks();

		$verbose = 0;
		$v = '';
		if ($verbose) {
			system("echo 'Trying SSH' 1>&2", $ret);
			$v = "-v";
		}
		// Using 'PGPASSFILE= <command>' work-around to avoid random stalling
		// Cf. https://fusionforge.org/plugins/mediawiki/wiki/fusionforge/index.php/User_accounts#nscd
		system("ssh $v ".FORGE_ADMIN_USERNAME."@".HOST." PGPASSFILE= id", $ret);
		$this->assertEquals(0, $ret);
		if ($verbose) {
			system("echo 'End of SSH run' 1>&2", $ret);
		}
	}
}
