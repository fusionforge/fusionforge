<?php

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Syntax test class.
 *
 * @package   DeprecationsTests
 * @author    Roland Mas <lolando@debian.org>
 * @copyright 2009 Roland Mas
 * @license   http://www.opensource.org/licenses/gpl-license.php  GPL License
 */
class Deprecations_Tests extends PHPUnit_Framework_TestCase
{
	/**
	 * Check that no code uses db_query() or db_mquery()
	 */
	public function testdb_query()
	{
		$output = `cd .. ; find gforge tests -name '*.php' -type f | xargs pcregrep -l '\bdb_m?query\b' \
					   | grep -v ^tests/code/deprecations/DeprecationsTest.php \
					   | grep -v ^gforge/db/upgrade-db.php \
					   | grep -v ^gforge/www/include/database-oci8.php \
					   | grep -v ^gforge/common/include/database-pgsql.php \
					   | grep -v ^gforge/common/include/database-mysql.php`;
		$this->assertEquals('', $output);
	}
	
	/**
	 * Check that no code uses configuration items from global variables
	 */
	public function testconfig_vars()
	{
		$vars = array ('sys_name',
			       'sys_user_reg_restricted',
			       'sys_project_reg_restricted',
			       'sys_default_domain',
			       'sys_scm_tarballs_path',
			       'sys_scm_snapshots_path',
			       'sys_theme',
			       'sys_lang',
			       'sys_default_timezone',
			       'sys_default_country_code',
			       'sys_use_scm',
			       'sys_use_tracker',
			       'sys_use_forum',
			       'sys_use_pm',
			       'sys_use_docman',
			       'sys_use_news',
			       'sys_use_mail',
			       'sys_use_survey',
			       'sys_use_frs',
			       'sys_use_fti',
			       'sys_use_ftp',
			       'sys_use_trove',
			       'sys_use_snippet',
			       'sys_use_ssl',
			       'sys_use_people',
			       'sys_use_shell',
			       'sys_use_ratings',
			       'sys_use_ftpuploads',
			       'sys_use_manual_uploads',
			       'sys_use_gateways',
			       'sys_use_project_vhost',
			       'sys_use_project_database',
			       'sys_use_project_multimedia',
			       'sys_download_host',
			       'sys_shell_host',
			       'sys_users_host',
			       'sys_lists_host',
			       'sys_scm_host',
			       'sys_forum_return_domain',
			       'sys_chroot',
			       'sys_upload_dir',
			       'sys_ftp_upload_dir',
			       'sys_ftp_upload_host',
			       'sys_apache_user',
			       'sys_apache_group',
			       'sys_require_unique_email',
			       'sys_bcc_all_email_address',
			       'sys_themeroot',
			       'sys_force_login',
			       'sys_custom_path',
			       'sys_plugins_path',
			       'sys_use_jabber',
			       'sys_jabber_user',
			       'sys_jabber_server',
			       'sys_jabber_port'
			) ;

		$pattern = implode ('|', $vars) ;
		
		$output = `cd .. ; find gforge tests -name '*.php' -type f | xargs pcregrep -n '\\$($pattern)\b(?! *=[^=])' \
					   | grep -v ^gforge/common/include/config-vars.php`;
		$this->assertEquals('', $output, "Found deprecated \$var for var in ($pattern):");

		$output = `cd .. ; find gforge tests -name '*.php' -type f | xargs pcregrep -n '\\\$GLOBALS\\[.?($pattern).?\\](?! *=[^=])' \
					   | grep -v ^gforge/common/include/config-vars.php`;
		$this->assertEquals('', $output, "Found deprecated \$GLOBALS['\$var'] for var in ($pattern):");
		
	}
	
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

}
