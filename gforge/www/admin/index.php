<?php
/**
  * SourceForge Site Admin main page
  *
  * This pages lists all global administration facilities for the
  * site, including user/group properties editing, maintanance of
  * site meta-information (Trove maps, metadata for file releases),
  * etc.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  */


require_once('pre.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

site_admin_header(array('title'=>$Language->getText('admin_index','title')));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');


?>

<p><strong><?php echo $Language->getText('admin_index','user_maintaince'); ?></strong></p>
<ul>
	<li><a href="userlist.php"><?php echo $Language->getText('admin_index','display_full_user_list'); ?></a>&nbsp;&nbsp;</li>
	<li><?php echo $Language->getText('admin_index','display_user_beginning_with') ?>
<?php
	for ($i=0; $i < count($abc_array); $i++) {
		echo "<a href=\"search.php?usersearch=1&amp;search=$abc_array[$i]%\">$abc_array[$i]</a>|";
	}
?>
	<br />
		<form name="usersrch" action="search.php" method="post">
		Search <em>(userid, username, realname, email)</em>:
		<input type="text" name="search" />
		<input type="hidden" name="substr" value="1" />
		<input type="hidden" name="usersearch" value="1" />
		<input type="submit" value="<?php echo $Language->getText('admin_index','get'); ?>" />
		</form>
	</li>
</ul>
<p>
<strong><?php echo $Language->getText('admin_index','group_maintaince'); ?></strong>
</p>
<ul>
	<li><a href="grouplist.php"><?php echo $Language->getText('admin_index','display_full_group'); ?></a></li>

	<li><?php echo $Language->getText('admin_index','display_groups_beginning_with'); ?>
<?php
	for ($i=0; $i < count($abc_array); $i++) {
		echo "<a href=\"search.php?groupsearch=1&amp;search=$abc_array[$i]%\">$abc_array[$i]</a>|";
	}
?>
	<br />
		<form name="gpsrch" action="search.php" method="post">
		Search <em>(groupid, group unix name, full name)</em>:
		<input type="text" name="search" />
		<input type="hidden" name="substr" value="1" />
		<input type="hidden" name="groupsearch" value="1" />
		<input type="submit" value="<?php echo $Language->getText('admin_index','get'); ?>" />
		</form>
	</li>
</ul>
<ul>
	<li><a href="/register/"><?php echo $Language->getText('admin_index','register_new_project'); ?></a></li>
	<li><?php echo $Language->getText('admin_index','groups_with'); ?> <a href="approve-pending.php"><strong>P</strong> <?php echo $Language->getText('admin_index','pending_status'); ?></a> <em><?php echo $Language->getText('admin_index','new_project_approval'); ?></em></li>
	<li><?php echo $Language->getText('admin_index','groups_with'); ?> <a href="search.php?groupsearch=1&amp;search=%&amp;status=D"><strong>D</strong> <?php echo $Language->getText('admin_index','deleted_status'); ?></a></li>
	<li><a href="search.php?groupsearch=1&amp;search=%&amp;is_public=0"><?php echo $Language->getText('admin_index','private_groups'); ?></a></li>
</ul>

<p>
<strong><?php echo $Language->getText('admin_index','news'); ?></strong>
</p>
<ul>
	<li><a href="/news/admin/"><?php echo $Language->getText('admin_index','approve_reject'); ?></a> <?php echo $Language->getText('admin_index','front_page_news'); ?></li>
</ul>

<p>
<strong><?php echo $Language->getText('admin_index','stats'); ?></strong>
</p>
<ul>
	<li><a href="/stats/"><?php echo $Language->getText('admin_index','site_wide_stats'); ?></a></li>
</ul>

<p>
<strong><?php echo $Language->getText('admin_index','trove_project_tree'); ?></strong>
</p>
<ul>
	<li><a href="trove/trove_cat_list.php"><?php echo $Language->getText('admin_index','display_trove_map'); ?></a></li>
	<li><a href="trove/trove_cat_add.php"><?php echo $Language->getText('admin_index','add_to_trove_map'); ?></a></li>
</ul>

<p><strong><?php echo $Language->getText('admin_index','site_utilities'); ?></strong></p>
<ul>
	<li><a href="massmail.php"><?php echo $Language->getText('admin_index','mail_engine',array($GLOBALS['sys_name'])); ?></a></li>
	<li><a href="unsubscribe.php"><?php echo $GLOBALS['sys_name']; ?> <?php echo $Language->getText('admin_index','site_mailing_maintaince'); ?></a></li>
	<li><a href="edit_supported_languages.php"><?php echo $Language->getText('admin_index','add_delete_edit_laguage'); ?></a></li>
	<li><a href="edit_frs_filetype.php"><?php echo $Language->getText('admin_index','add_delete_edit_file_types'); ?></a></li>
	<li><a href="edit_frs_processor.php"><?php echo $Language->getText('admin_index','add_edit_delete_processors'); ?></a></li>
	<li><a href="edit_frs_theme.php"><?php echo $Language->getText('admin_index','add_edit_delete_themes'); ?></a></li>
	<li><a href="loadtabfiles.php"><?php echo $Language->getText('admin_index','translation_file_tool'); ?></a></li>
</ul>

<p>
<strong><?php echo $Language->getText('admin_index','global_admin_tools_mass_insert'); ?></strong>
</p>
<ul>
	<li><a href="vhost.php"><?php echo $Language->getText('admin_index','virtual_host_admin_tool'); ?></a></li>
	<li><a href="database.php"><?php echo $Language->getText('admin_index','project_database_administration'); ?></a></li>
</ul>

<p><strong><?php echo $Language->getText('admin_index','quick_site_statistic'); ?></strong></p>

<?php

$res=db_query("SELECT count(*) AS count FROM users WHERE status='A'");
$row = db_fetch_array($res);
print "<p>Active site users: <strong>$row[count]</strong></p>";

$res=db_query("SELECT count(*) AS count FROM groups");
$row = db_fetch_array($res);
print "<br />Registered projects: <strong>$row[count]</strong>";

$res=db_query("SELECT count(*) AS count FROM groups WHERE status='A'");
$row = db_fetch_array($res);
print "<br />Active projects: <strong>$row[count]</strong>";

$res=db_query("SELECT count(*) AS count FROM groups WHERE status='P'");
$row = db_fetch_array($res);
print "<br />Pending projects: <strong>$row[count]</strong>";

site_admin_footer(array());

?>
