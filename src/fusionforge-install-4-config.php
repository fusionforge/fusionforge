#!/usr/bin/php
<?php
/**
 * FusionForge Installation final step
 *
 * Copyright 2010-2011, Roland Mas
 *
 * This file is part of FusionForge
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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


require_once 'install-common.inc' ;

// Set up admin user

show(" * Enter the Admin Username (fforgeadmin): ");
if (getenv('FFORGE_ADMIN_USER')) {
	$admin_user = getenv('FFORGE_ADMIN_USER');
} else {
	$admin_user = trim(fgets($STDIN));
	
	if (strlen($admin_user) == 0) {
		$admin_user = 'fforgeadmin';
	}
}
show(" ...using '$admin_user'");

if (getenv('FFORGE_ADMIN_PASSWORD')) {
	$bad_pwd = false;
	$pwd1 = getenv('FFORGE_ADMIN_PASSWORD');
} else {
	$retries = 0;
	$bad_pwd = true;
	$pwd1 = '';
	$pwd2 = '';
	$error = '';
	while ($bad_pwd && $retries < 5) {
		if ($bad_pwd && $retries > 0) {
			show(' * ' . $error);
		}
		$pwd1 = readMasked(" * Enter the Site Admin Password:");
		$error = validatePassword($pwd1);
		if ($error != '') {
			$bad_pwd = true;
		} else {
			$pwd2 = readMasked(" * Please enter it again: \n");
			if ($pwd1 == $pwd2) {
				$bad_pwd = false;
			} else {
				$error = 'Passwords don\'t match. Please try again.';
			}
		}
		$retries++;
	}
}

if ($bad_pwd) {
	show('Passwords didn\'t match! Aborting.');
	die();
} else {
	$pw_md5 = md5($pwd1);
	$pw_crypt = crypt($pwd1);
	$pw_crypt = str_replace('$', '\\\\\\$', $pw_crypt);
	run("su - postgres -c \"psql $gforge_db -c \\\"INSERT INTO users (user_name, realname, firstname, lastname, email, user_pw, unix_pw, status, theme_id) VALUES ('$admin_user', 'Forge Admin', 'Forge', 'Admin', 'root@localhost.localdomain', '$pw_md5', '$pw_crypt', 'A', 1); INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (currval('users_pk_seq'), 1, 'A'); INSERT INTO pfo_user_role (user_id, role_id) VALUES (currval('users_pk_seq'), 3)\\\"\"");

}

// Set up config

if (is_file("/etc/gforge/local.inc")) {
	system('PATH=/opt/gforge/utils/:$PATH migrate-to-ini-files.sh') ;
}
system('PATH=/opt/gforge/utils/:$PATH manage-apache-config.sh install') ;
system('for i in /etc/gforge/httpd.conf.d/*.generated ; do mv $i ${i%%.generated} ; done') ;
