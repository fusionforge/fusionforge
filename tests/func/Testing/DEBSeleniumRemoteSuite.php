<?php

require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class DEBSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

		system("scp -r ../tests/preseed root@".HOST.":/root/");
		system("ssh root@".HOST." 'cat /root/preseed/* | LANG=C debconf-set-selections'");

		if (getenv('DEBMIRROR')) {
			system("ssh root@".HOST." 'echo \"deb ".getenv('DEBMIRROR')." ".getenv('DIST')." main\" > /etc/apt/sources.list'");
		}
		system("ssh root@".HOST." 'echo \"deb file:/debian ".getenv('DIST')." main\" > /etc/apt/sources.list.d/fusionforge.list'");
		system("scp -r ".getenv('WORKSPACE')."/build/debian root@".HOST.":/");
		system("gpg --export --armor | ssh root@".HOST." apt-key add -");

		sleep(5);
		
		system("ssh root@".HOST." 'apt-get update'");
		system("ssh root@".HOST." 'UCF_FORCE_CONFFNEW=yes DEBIAN_FRONTEND=noninteractive LANG=C apt-get -y --force-yes install postgresql-contrib fusionforge-plugin-forumml fusionforge-full'");
		system("ssh root@".HOST." 'LANG=C a2dissite default'");
		system("ssh root@".HOST." 'LANG=C invoke-rc.d apache2 reload'");
		system("ssh root@".HOST." 'LANG=C touch /tmp/fusionforge-use-pfo-rbac'");
		system("scp ../tests/func/db_reload.sh root@".HOST.":");
		system("ssh root@".HOST." '(echo [core];echo use_ssl=no) > /etc/gforge/config.ini.d/zzz-builbot.ini'");
		system("ssh root@".HOST." 'su - postgres -c \"pg_dump -Fc ".DB_NAME."\" > /root/dump'") ;
		system("ssh root@".HOST." 'invoke-rc.d cron stop'");
	}
}
?>
