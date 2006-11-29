#! /usr/bin/php4 -f
<?php
/**
 * 
 * Fabio Bertagnin nov 2005
 * fbertatnin@mail.transiciel.com
 *
 * @version   $Id: 06_IMPROVSFTP_80_ftp_improvement.dpatch,v 1.1 2006/01/11 17:02:45 fabio Exp $
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require ('squal_pre.php');
require ('common/include/cron_utils.php');


$users = array();

$chroot_dir = $sys_chroot;
$ftp_dir = $sys_ftp_upload_dir."/pub/";
$home_dir = $chroot_dir.$homedir_prefix."/";


$SQL = "SELECT groups.group_id, group_name, unix_group_name, ";
$SQL .= "user_group.user_id, ";
$SQL .= "users.user_name ";
$SQL .= "FROM groups ";
$SQL .= "JOIN user_group ON user_group.group_id = groups.group_id ";
$SQL .= "JOIN users ON users.user_id = user_group.user_id ";
$SQL .= "ORDER BY group_id ";
$res_db = db_query($SQL);
if ($res_db)
{
        while($e = db_fetch_array($res_db))
        {
                $users["$e[user_id]"]["user_name"] = "$e[user_name]";
                $users["$e[user_id]"]["user_groups"][] = "$e[unix_group_name]";
        }
}
foreach ($users as $u)
{
        $dir = "$home_dir"."$u[user_name]"."/pub";
        //$cmd = "cd $dir";
        //$res = execute($cmd);
        if (is_dir("$home_dir"."$u[user_name]"))
        {
                foreach ($u["user_groups"] as $g)
                {
                        if (is_dir("$ftp_dir"."$g"))
                        {
                                if (is_dir("$dir/$g"))
                                {
                                        $cmd = "/bin/umount $dir/$g";
                                        $res = execute($cmd);
                                        $cmd = "/bin/rmdir $dir/$g";
                                        $res = execute($cmd);
                                }
                                if (!is_dir($dir))
                                {
                                        $cmd = "/bin/mkdir $dir";
                                        $res = execute($cmd);
                                }
                                $cmd = "/bin/mkdir $dir/$g";
                                $res = execute($cmd);
                                $cmd = "/bin/mount --bind $ftp_dir"."$g $dir/$g";
                                $res = execute($cmd);
                                echo "allow $u[user_name] to access at $dir/$g\n";
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
