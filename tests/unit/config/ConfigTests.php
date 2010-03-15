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
		$c = FusionForgeConfig::get_instance () ;
		fusionforge_define_config_item ('forge_name', 'core', 'default') ;

		$this->assertEquals('default', fusionforge_get_config ('forge_name'));
		$this->assertEquals('default', fusionforge_get_config ('forge_name', 'core'));

		fusionforge_read_config_file (dirname(__FILE__) . '/../../../gforge/etc/fusionforge.ini') ;

		$this->assertEquals('FusionForge', fusionforge_get_config ('forge_name'));
		$this->assertEquals('FusionForge', fusionforge_get_config ('forge_name', 'core'));
	}

}
