#!/usr/bin/perl
#
# $Id$
#
# ccase_dump_update.pl - script to dump data from the database 
#	                 and update Clear Case consequently
#		         inspired from sourceforge scripts
# Christian Bayle <bayle@debian.org>
# Roland Mas <lolando@debian.org>
#
use DBI;
use Sys::Hostname;

require("/usr/lib/gforge/lib/include.pl"); # Include all the predefined functions
require($db_include); # Include global configuration variables
require("/etc/gforge/plugins/scmccase/config.pl"); # Include plugin config vars

my $verbose = 0;

if($verbose) {print ("\nConnecting to database");}
&db_connect;

$query = "SELECT plugin_id FROM plugins WHERE plugin_name = 'scmccase'" ;
$c = $dbh->prepare ($query) ;
$c->execute () ;
my ($plugin_id) = $c->fetchrow () ;
$c->finish () ;

if($verbose) {print ("\nGetting group list");}
$query = "SELECT groups.group_id, unix_group_name, status
          FROM groups, group_plugin, plugin_scmccase_group_usage
          WHERE groups.group_id = group_plugin.group_id
          AND group_plugin.plugin_id = $plugin_id
          AND plugin_scmccase_group_usage.group_id = groups.group_id
          AND plugin_scmccase_group_usage.ccase_host = '$this_server'" ;

$c = $dbh->prepare($query);
$c->execute();

open LSVOB, "$cleartool lsvob $group_name |" ;
while ($ls = <LSVOB>) {
    chomp $ls ;
    push @vobs, $ls ;
}
close LSVOB ;

while(my ($group_id, $group_name, $status) = $c->fetchrow()) {

    $ccase_uid = $group_id + $anoncvs_uid_add;
    $ccase_gid = $group_id + $gid_add;

    $group_exists = (-d $grpdir_prefix . $group_name);
    
    $vob_tag = $tag_pattern ;
    $vob_tag =~ s/GROUPNAME/$group_name/g ;

    $ccase_exists = 0 ;
    foreach $ls (@vobs) {
	if ($ls =~ /\s$vob_tag\s/) {
	    $ccase_exists = 1  ;
	}
    }

    # CCASE repository creation
    if ($group_exists && !$ccase_exists && $status eq 'A') {
	if($verbose){print("Creating a Clear Case VOB for $group_name with VOB-tag $vob_tag\n");}
	
	# Firce create the VOB
	system("$cleartool mkvob --tag $vob_tag --comment 'Clear Case VOB for project $group_name'");
	
	# Set group ownership, anonymous group user
	system("$cleartool protectvob --force --chgrp $group_name $vob_tag");
    }
}
