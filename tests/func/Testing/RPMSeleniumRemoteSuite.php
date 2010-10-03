<?php

require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class RPMSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

		system("scp -r ../tests root@".HOST.":/usr/share");
		system("ssh root@".HOST." 'ln -s gforge /usr/share/src'");
		
		system("scp -rp ~/fusionforge_repo root@".HOST.":");
		system("scp -rp ".dirname(__FILE__)."/../../../src/rpm-specific/dag-rpmforge.repo root@".HOST.":/etc/yum.repos.d/");

//		system("scp -rp ".dirname(__FILE__)."/../../../src/rpm-specific/fusionforge-ci.repo root@".HOST.":/etc/yum.repos.d/");
		if (getenv('FFORGE_RPM_REPO')) {
			system("ssh root@".HOST." 'cd /etc/yum.repos.d/; wget ".getenv('FFORGE_RPM_REPO')."/fusionforge.repo'");
		}

		sleep(5);
		
		system("ssh root@".HOST." 'yum install -y fusionforge fusionforge-scmsvn fusionforge-online_help fusionforge-extratabs fusionforge-ldapextauth fusionforge-scmgit fusionforge-blocks'");
	}
}
?>
