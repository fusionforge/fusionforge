<?php

require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class DEBSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

		system("scp -r ../tests/preseed root@".HOST.":/root/");
		system("ssh root@".HOST." 'debconf-set-selections < /root/preseed/*'");

		if (getenv('FFORGE_DEB_REPO')) {
			system("ssh root@".HOST." 'echo \"deb ".getenv('FFORGE_DEB_REPO')." ".getenv('DIST')." main\" > /etc/apt/sources.list.d/fusionforge.list'");
		}

		sleep(5);
		
		system("ssh root@".HOST." 'apt-get update'");
		system("ssh root@".HOST." 'UCF_FORCE_CONFFNEW=yes LANG=C apt-get -y --force-yes install postgresql-contrib fusionforge-full'");
		system("ssh root@".HOST." 'a2dissite default'");
		system("ssh root@".HOST." 'invoke-rc.d apache2 reload'");
	}
}
?>
