<?php
 
require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class TarSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

		/*
		system("scp -r ../tests root@".HOST.":/opt");
		system("scp ../tests/func/db_reload.sh root@".HOST.":/root");

		if (getenv('BUILDRESULT')) {
			system("scp ".getenv('BUILDRESULT')."/fusionforge-*.tar.bz2 root@".HOST.":");
		} else {
			system("scp ../../build/packages/fusionforge-*.tar.bz2 root@".HOST.":");
		}
		system("ssh root@".HOST." 'tar jxf fusionforge-*.tar.bz2'");

		if (is_file("/tmp/timedhosts.txt")) {
			system("scp -p /tmp/timedhosts.txt root@".HOST.":/var/cache/yum/timedhosts.txt");
		}

		system("ssh root@".HOST." 'cd fusionforge-*; FFORGE_RPM_REPO=http://buildbot.fusionforge.org/job/fusionforge-trunk-build-and-test-rpm/ws/build/packages/ FFORGE_DB=fforge FFORGE_USER=gforge FFORGE_ADMIN_USER=ffadmin FFORGE_ADMIN_PASSWORD=ffadmin ./install.sh centos52.local'");

		system("scp -p root@".HOST.":/var/cache/yum/timedhosts.txt /tmp/timedhosts.txt");
		system("ssh root@".HOST." '(echo [core];echo use_ssl=no) > /etc/gforge/config.ini.d/zzz-builbot.ini'");
		system("ssh root@".HOST." 'cd /opt/tests/func; CONFIGURED=true CONFIG_PHP=config.php.buildbot DB_NAME=".DB_NAME." php db_reload.php'");
                system("ssh root@".HOST." 'su - postgres -c \"pg_dump -Fc ".DB_NAME."\" > /root/dump'") ;

		system("ssh root@".HOST." 'service crond stop'");
		*/
	}
}
?>
