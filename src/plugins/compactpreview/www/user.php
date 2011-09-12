<?php

/**
 * This file is (c) Copyright 2010 by Sabri LABBENE, Institut
 * TELECOM
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the HELIOS
 * project with financial support of its funders.
 *
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';

$user = getStringFromRequest('user');

$user_obj = user_get_object_by_name($user);

require_once $gfwww.'include/user_profile.php';

$user_real_name = $user_obj->getRealName();
$user_id = $user_obj->getID();

?>
<html>
<head>
<title>User: <?php echo $user_real_name;?> (Identifier: <?php echo $user_id;?>)</title>
</head>
<body>


<?php
$compact = true;
echo user_personal_information($user_obj, $compact);
?>

</body>
</html>

<?php

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
