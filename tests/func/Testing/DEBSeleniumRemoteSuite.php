<?php

require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class DEBSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

		system("scp -r ../tests/preseed root@".HOST.":/root/");
		system("ssh root@".HOST." 'cat /root/preseed/* | LANG=C debconf-set-selections'");

		if (getenv('FFORGE_DEB_REPO')) {
			system("ssh root@".HOST." 'echo \"deb ".getenv('FFORGE_DEB_REPO')." ".getenv('DIST')." main\" > /etc/apt/sources.list.d/fusionforge.list'");
		}

		sleep(5);
		
		system("ssh root@".HOST." 'apt-get update'");
		system("ssh root@".HOST." 'UCF_FORCE_CONFFNEW=yes DEBIAN_FRONTEND=noninteractive LANG=C apt-get -y --force-yes install postgresql-contrib fusionforge-full'");
		system("ssh root@".HOST." 'LANG=C a2dissite default'");
		system("ssh root@".HOST." 'LANG=C invoke-rc.d apache2 reload'");
		system("ssh root@".HOST." 'LANG=C touch /tmp/fusionforge-use-pfo-rbac'");
		system("ssh root@".HOST." 'LANG=C touch /tmp/fusionforge-use-pfo-rbac'");
		system("scp ../tests/func/db_reload.sh root@".HOST.":");
		system("ssh root@".HOST.". 'su - postgres -c \"pg_dump -Fc gforge\" > /root/dump'") ;
	}
}
?>
