<?php
 
class SeleniumRemoteSuite extends PHPUnit_Framework_TestSuite
{
	protected function setUp()
	{
		system("cd scripts; ./start_vm.sh ".HOST);

		//system("scp /usr/share/php/PHPUnit/Extensions/SeleniumTestCase/*pend.php root@centos52:/opt/tests");
		//system("scp /usr/share/php/PHPUnit/Extensions/SeleniumTestCase/phpunit_coverage.php root@centos52:/opt/gforge/www");
		//system("ssh root@centos52 'perl -spi -e \'s!^auto_prepend_file.*!auto_prepend_file=/opt/tests/prepend.php!\' /etc/php.ini');
		//system("ssh root@centos52 'perl -spi -e \'s!^auto_append_file.*!auto_append_file=/opt/tests/append.php!\' /etc/php.ini');

	}

	protected function tearDown()
	{
		system("scp root@".HOST.":/var/log/httpd/error_log /tmp/centos52_error_log");
		system("scp root@".HOST.":/var/log/httpd/access_log /tmp/centos52_access_log");
		system("cd scripts; ./stop_vm.sh ".HOST);
	}
}
?>
