<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../../gforge/common/include/config.php';

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

		$this->assertEquals('default', forge_get_config ('forge_name'));
		$this->assertEquals('default', forge_get_config ('forge_name', 'core'));
		$this->assertTrue(forge_get_config ('user_registration_restricted'));

		forge_read_config_file (dirname(__FILE__) . '/../../../gforge/etc/config.ini') ;

		$this->assertEquals('FusionForge', forge_get_config ('forge_name'));
		$this->assertEquals('FusionForge', forge_get_config ('forge_name', 'core'));
		$this->assertEquals('', forge_get_config ('user_registration_restricted'));

		$arr = forge_get_config_array ('forge_name', array ('user_registration_restricted', 'core')) ;
		$this->assertEquals('FusionForge', $arr[0]);
		$this->assertFalse(!!$arr[1]);

		forge_set_vars_from_config ('forge_name', array ('user_registration_restricted', 'core')) ;
		global $forge_name, $core__user_registration_restricted ;
		$this->assertEquals('FusionForge', $forge_name);
		$this->assertFalse(!!$core__user_registration_restricted);

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
		
	public function get_instance () {
		if (parent::$instance == NULL) {
			parent::$instance = new MockConfig () ;
		}
		return parent::$instance ;
	}
	
	public function get_value ($section, $var) {
		return "$section/$var" ;
	}
}