#!/usr/bin/perl
#
# $Id$
#
# cvs_dump.pl - script to dump data from the database 
#		       inspired from sourceforge scripts
# Christian Bayle <bayle@debian.org>
#
use DBI;
use Sys::Hostname;

require("/usr/lib/gforge/lib/include.pl");  # Include all the predefined functions

my $group_array = ();
my $verbose = 0;
my $cvs_file = $file_dir . "/dumps/cvs_dump";

if($verbose) {print ("\nConnecting to database");}

if ( "$sys_dbname" ne "gforge" || "$sys_dbuser" ne "gforge" ) {
$dbh ||= DBI->connect("DBI:Pg:dbname=$sys_dbname","$sys_dbuser","$sys_dbpasswd");
} else {
$dbh ||= DBI->connect("DBI:Pg:dbname=$sys_dbname");
}
die "Cannot connect to database: $!" if ( ! $dbh );

if($verbose) {print ("\nGetting group list");}
# Dump the Groups Table information
#$query = "SELECT group_id,unix_group_name,status,use_scm,enable_pserver,enable_anonscm FROM groups WHERE unix_group_name !=''";
$query= "SELECT groups.group_id,groups.unix_group_name,groups.status,groups.use_scm,groups.enable_pserver,groups.enable_anonscm FROM groups,group_plugin,plugins WHERE groups.unix_group_name !='' AND groups.group_id=group_plugin.group_id AND group_plugin.plugin_id=plugins.plugin_id AND plugins.plugin_name='scmcvs'";

# AND cvs_box=$hostname to be added for multi-cvs server support

$c = $dbh->prepare($query);
$c->execute();

if($verbose) {print ("\nGetting user list per group");}
while(my ($group_id, $group_name, $status, $use_scm, $enable_pserver, $enable_anonscm) = $c->fetchrow()) {

	my $new_query = "SELECT users.user_name AS user_name FROM users,user_group WHERE users.user_id=user_group.user_id AND cvs_flags=1 AND group_id=$group_id";
	my $d = $dbh->prepare($new_query);
	$d->execute();

	my $user_list = "";
	
	while($user_name = $d->fetchrow()) {
	   $user_list .= "$user_name,";
	}

	$grouplist = "$group_name:$status:$group_id:$use_scm:$enable_pserver:$enable_anonscm:$user_list\n";
	$grouplist =~ s/,$//;

	push @group_array, $grouplist;
}

# Now write out the files (not necessary, but can give info in case of problems)
if($verbose) {print ("\nWriting list");}
write_array_file($cvs_file, @group_array);
