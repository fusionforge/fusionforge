<?php
/**
 * Test account utilities
 *
 * Copyright (C) 2015  Inria (Sylvain Beucler)
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/../../../src/common/include/account.php';
require_once dirname(__FILE__) . '/../../../src/common/include/utils.php';
require_once dirname(__FILE__) . '/../../../src/common/include/config.php';

class Account_Tests extends PHPUnit\Framework\TestCase
{
	public function test_account_gensalt()
	{
		// default to md5crypt
		forge_define_config_item('unix_cipher', 'core', '');
		$salt = account_gensalt();
		$this->assertSame('$1$', substr($salt, 0, 3));

		// ab
		forge_reset_config_item('unix_cipher', 'core', 'DES');
		$salt = account_gensalt();
		$this->assertSame(2, strlen($salt));
		$this->assertMatchesRegularExpression(',[./0-9a-zA-Z]+,', $salt);

		// $1$abcdefgh
		forge_reset_config_item('unix_cipher', 'core', 'MD5');
		$salt = account_gensalt();
		$this->assertSame('$1$', substr($salt, 0, 3));
		$this->assertSame(8, strlen(explode('$', $salt)[2]));
		$this->assertMatchesRegularExpression(',[./0-9a-zA-Z]+,', explode('$', $salt)[2]);

		// $5$rounds=5000$abcdefghij123456
		forge_reset_config_item('unix_cipher', 'core', 'SHA256');
		$salt = account_gensalt();
		$this->assertSame('$5$', substr($salt, 0, 3));
		$this->assertMatchesRegularExpression('/rounds=[0-9]+/', explode('$', $salt)[2]);
		$this->assertSame(16, strlen(explode('$', $salt)[3]));
		$this->assertMatchesRegularExpression(',[./0-9a-zA-Z]+,', explode('$', $salt)[3]);

		// $6$rounds=5000$abcdefghij123456
		forge_reset_config_item('unix_cipher', 'core', 'SHA512');
		$salt = account_gensalt();
		$this->assertSame('$6$', substr($salt, 0, 3));
		$this->assertMatchesRegularExpression('/rounds=[0-9]+/', explode('$', $salt)[2]);
		$this->assertSame(16, strlen(explode('$', $salt)[3]));
		$this->assertMatchesRegularExpression(',[./0-9a-zA-Z]+,', explode('$', $salt)[3]);
		$this->assertLessThanOrEqual(128, strlen($salt));  // or else update the DB schema

		// $2y$10$abcdefghij123456789012
		// Note that only 2 bits from the last char are used
		forge_reset_config_item('unix_cipher', 'core', 'Blowfish');
		$salt = account_gensalt();
		$this->assertEquals('Blowfish', forge_get_config('unix_cipher'));
		$this->assertSame('$2y$', substr($salt, 0, 4));
		$cost = explode('$', $salt, 4)[2];
		$this->assertStringMatchesFormat('%d', $cost);
		$this->assertGreaterThanOrEqual(4, intval($cost));
		$this->assertLessThanOrEqual(31, intval($cost));
		$this->assertSame(22, strlen(explode('$', $salt)[3]));
		$this->assertMatchesRegularExpression(',[./0-9a-zA-Z]+,', explode('$', $salt)[3]);
	}
}
