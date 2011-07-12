<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../../src/common/include/config.php';

/**
 * Simple tests for the config library.
 *
 * @package   Tests
 * @author    Roland Mas <lolando@debian.org>
 * @copyright 2009 Roland Mas
 * @license   GPL License
 */
class Config_Tests extends PHPUnit_Framework_TestCase
{
	/**
	 * test basic config getting
	 */
	public function testBasicConfig()
	{
		forge_define_config_item ('forge_name', 'core', 'default') ;
		forge_define_config_item ('user_registration_restricted', 'core', true) ;
		forge_set_config_item_bool ('user_registration_restricted', 'core') ;

		$this->assertEquals('default', forge_get_config ('forge_name'));
		$this->assertEquals('default', forge_get_config ('forge_name', 'core'));
		$this->assertTrue(forge_get_config ('user_registration_restricted'));

		forge_read_config_file (dirname(__FILE__) . '/../../../src/etc/config.ini-fhs') ;
		forge_read_config_file (dirname(__FILE__) . '/../../../src/etc/config.ini.d/defaults.ini') ;

		$this->assertEquals('FusionForge', forge_get_config ('forge_name'));
		$this->assertEquals('FusionForge', forge_get_config ('forge_name', 'core'));
		$this->assertEquals('', forge_get_config ('user_registration_restricted'));

		$arr = forge_get_config_array ('forge_name', array ('user_registration_restricted', 'core')) ;
		$this->assertEquals('FusionForge', $arr[0]);
		$this->assertFalse($arr[1]);

		forge_set_vars_from_config ('forge_name', array ('user_registration_restricted', 'core')) ;
		global $forge_name, $core__user_registration_restricted ;
		$this->assertEquals('FusionForge', $forge_name);
		$this->assertFalse($core__user_registration_restricted);

		forge_read_config_dir (dirname(__FILE__) . '/../../../src/etc/config.ini.d') ;

		$this->assertEquals('anonsvn', forge_get_config ('anonsvn_login', 'scmsvn'));
		$this->assertEquals('/var/lib/gforge/chroot/scmrepos/svn', forge_get_config ('repos_path', 'scmsvn'));
	}

	/**
	 * test mock config system
	 */
	public function testMockConfig()
	{
		MockConfig::insinuate () ;
		forge_define_config_item ('forge_name', 'core', 'default') ;

		$this->assertEquals('core/forge_name', forge_get_config ('forge_name'));
		$this->assertEquals('core/forge_name', forge_get_config ('forge_name', 'core'));

		MockConfig::cleanup () ;
		forge_define_config_item ('forge_name', 'core', 'default') ;

		$this->assertEquals('default', forge_get_config ('forge_name'));
	}

}

class MockConfig extends FusionForgeConfig {
	public function insinuate () {
		parent::$instance = NULL ;
		self::get_instance () ;
	}

	public function cleanup () {
		parent::$instance = NULL ;
	}

	static public function get_instance () {
		if (parent::$instance == NULL) {
			parent::$instance = new MockConfig () ;
		}
		return parent::$instance ;
	}

	public function get_value ($section, $var) {
		return "$section/$var" ;
	}
}
