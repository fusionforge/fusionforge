<?php
 
class SeleniumRemoteSuite extends PHPUnit_Framework_TestSuite
{
	protected function setUp()
	{
		system("cd scripts; ./start_vm.sh centos52.local");
		system("scp ../../build/packages/fusionforge-*allinone.tar.bz2 root@centos52:");
		system("ssh root@centos52 'tar jxf fusionforge-*allinone.tar.bz2'");
		system("ssh root@centos52 'cd fusionforge-*; FFORGE_RPM_REPO=http://buildbot.fusionforge.org/job/fusionforge-Branch_5_0-full/ws/build/packages/ FFORGE_DB=fforge FFORGE_USER=gforge FFORGE_ADMIN_USER=ffadmin FFORGE_ADMIN_PASSWORD=ffadmin ./install.sh centos52.local'");

		system("scp -r ../tests root@centos52:/opt");

		system("scp /usr/share/php/PHPUnit/Extensions/SeleniumTestCase/*pend.php root@centos52:/opt/tests");
		system("scp /usr/share/php/PHPUnit/Extensions/SeleniumTestCase/phpunit_coverage.php root@centos52:/opt/gforge/www");
		//system("ssh root@centos52 'perl -spi -e \'s!^auto_prepend_file.*!auto_prepend_file=/opt/tests/prepend.php!\' /etc/php.ini');
		//system("ssh root@centos52 'perl -spi -e \'s!^auto_append_file.*!auto_append_file=/opt/tests/append.php!\' /etc/php.ini');

		system("ssh root@centos52 'service crond stop'");
	}

	protected function tearDown()
	{
		system("cd scripts; ./stop_vm.sh centos52.local");
	}
}
?>
