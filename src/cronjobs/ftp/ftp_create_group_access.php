#! /usr/bin/php
<?php
/**
 *
 * Fabio Bertagnin nov 2005
 * fbertatnin@mail.transiciel.com
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

require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';


$users = array();

$chroot_dir = forge_get_config('chroot');
$ftp_dir = forge_get_config('ftp_upload_dir')."/pub/";
$home_dir = $chroot_dir.forge_get_config('homedir_prefix')."/";

$res_db = db_query_params ('SELECT user_id FROM users WHERE unix_status=$1',
			   array ('A'));
if ($res_db) {
        while($e = db_fetch_array($res_db)) {
                $users[] = user_get_object ($e['user_id']) ;
        }
}

foreach ($users as $u) {
        $dir = "$home_dir".$u->getUnixName()."/pub";
        if (is_dir("$home_dir".$u->getUnixName())) {
                foreach ($u->getGroups() as $project) {
			$g = $project->getUnixName() ;
                        if (is_dir("$ftp_dir"."$g")) {
                                if (is_dir("$dir/$g")) {
                                        $cmd = "/bin/umount $dir/$g";
                                        $res = execute($cmd);
                                        $cmd = "/bin/rmdir $dir/$g";
                                        $res = execute($cmd);
                                }
                                if (!is_dir($dir)) {
                                        $cmd = "/bin/mkdir $dir";
                                        $res = execute($cmd);
                                }
                                $cmd = "/bin/mkdir $dir/$g";
                                $res = execute($cmd);
                                $cmd = "/bin/mount --bind $ftp_dir"."$g $dir/$g";
                                $res = execute($cmd);
                                echo "allow ".$u->getUnixName()." to access at $dir/$g\n";
                        }
                }
        }
}

function print_debug($text)
{
        echo "$text\n";
}

function execute($cmd)
{
        // print_debug ("cmd= ".$cmd);
        $res = shell_exec($cmd);
        // print_debug ("res= ".$res);
        return $res;
}
?>
