<?php
/**
 * Add users to new 'xxx_scmro' groups
 * Copyright (C) 2015  Inria (Sylvain Beucler)
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/system/pgsql.class.php';
require_once $gfcommon.'include/cron_utils.php';

if ($SYS->sysRegenUserGroups()) {
	echo "SUCCESS\n";
} else {
	echo "ERROR\n";
	exit(1);
}
cron_reload_nscd();
