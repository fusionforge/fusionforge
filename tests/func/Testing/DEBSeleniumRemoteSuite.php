<?php

require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class DEBSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

///		system("scp -r ../tests root@".HOST.":/usr/share");
///		system("ssh root@".HOST." 'ln -s gforge /usr/share/src'");
		
///		system("scp -rp ~/fusionforge_repo root@".HOST.":");
///		system("scp -rp ".dirname(__FILE__)."/../../../src/rpm-specific/dag-rpmforge.repo root@".HOST.":/etc/yum.repos.d/");

//		system("scp -rp ".dirname(__FILE__)."/../../../src/rpm-specific/fusionforge-ci.repo root@".HOST.":/etc/yum.repos.d/");
		if (getenv('FFORGE_DEB_REPO')) {
			system("ssh root@".HOST." 'echo \"deb ".getenv('FFORGE_DEB_REPO')." ".getenv('DIST')." main\" > /etc/apt/sources.list.d/fusionforge.list'");
		}

		sleep(5);
		
		system("ssh root@".HOST." 'apt-get update'");
		system("ssh root@".HOST." 'UCF_FORCE_CONFFNEW=yes LANG=C apt-get -y --force-yes install fusionforge-minimal'");
	}
}
?>
