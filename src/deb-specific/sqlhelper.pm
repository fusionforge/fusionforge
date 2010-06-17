# A few SQL helper functions
#
### AUTHOR/COPYRIGHT
# This file is copyright 2004 Roland Mas <99.roland.mas@aist.enst.fr>.
#
# This is Free Software; you can redistribute it and/or modify it under the
# terms of the GNU General Public License version 2, as published by the
# Free Software Foundation.
#
### USAGE
# drop_view_if_exists ("view_name") ;
# drop_table_if_exists ("table_name") ;
# drop_index_if_exists ("index_name") ;
# drop_sequence_if_exists ("sequence_name") ;
# remove_plugin_from_groups ("plugin_name") ;
# remove_plugin_from_users ("plugin_name") ;
#
### BUGS
# * No real bugs known -- yet
#
### TODO

use strict ;
use Sort::Versions;

use subs qw/ &get_plugin_id &remove_plugin_from_groups
    &remove_plugin_from_users &drop_table_if_exists
    &drop_index_if_exists &drop_sequence_if_exists
    &drop_view_if_exists &bump_sequence_to &update_plugin_db_version
    &get_plugin_db_version &debug &create_plugin_metadata_table
    &is_lesser &is_greater &db_connect &db_disconnect / ;

sub get_plugin_id ( $$ ) ;
sub remove_plugin_from_groups ( $$ ) ;
sub remove_plugin_from_users ( $$ ) ;
sub table_exists ( $$ ) ;
sub view_exists ( $$ ) ;
sub drop_table_if_exists ( $$ ) ;
sub drop_index_if_exists ( $$ ) ;
sub drop_sequence_if_exists ( $$ ) ;
sub drop_view_if_exists ( $$ ) ;
sub bump_sequence_to ( $$$ ) ;
sub update_plugin_db_version ( $$$ ) ;
sub get_plugin_db_version ( $$ ) ;
sub create_plugin_metadata_table ( $$$ ) ;
sub is_lesser ( $$ ) ;
sub is_greater ( $$ ) ;
sub debug ( $ ) ;
sub db_connect ( ) ;
sub db_disconnect ( ) ;

sub table_exists ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $tname = shift or die  "Not enough arguments" ;
    my $query = "SELECT count(*) FROM pg_class WHERE relname='$tname' AND relkind='r'" ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    if ($array [0] != 0) {
	return 1 ;
    } else {
	return 0 ;
    }
}

sub drop_table_if_exists ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $tname = shift or die  "Not enough arguments" ;

    if (&table_exists ($dbh, $tname)) {
	# debug "Dropping table $tname" ;
	my $query = "DROP TABLE $tname" ;
	# debug $query ;
	my $sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }
}

sub drop_sequence_if_exists ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $sname = shift or die  "Not enough arguments" ;
    my $query = "SELECT count(*) FROM pg_class WHERE relname='$sname' AND relkind='S'" ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    if ($array [0] != 0) {
	# debug "Dropping sequence $sname" ;
	$query = "DROP SEQUENCE $sname" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }
}

sub drop_index_if_exists ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $iname = shift or die  "Not enough arguments" ;
    my $query = "SELECT count(*) FROM pg_class WHERE relname='$iname' AND relkind='i'" ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    if ($array [0] != 0) {
	# debug "Dropping index $iname" ;
	$query = "DROP INDEX $iname" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }
}

sub view_exists ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $vname = shift or die  "Not enough arguments" ;
    my $query = "SELECT count(*) FROM pg_class WHERE relname='$vname' AND relkind='v'" ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    if ($array [0] != 0) {
	return 1 ;
    } else {
	return 0 ;
    }
}

sub drop_view_if_exists ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $vname = shift or die  "Not enough arguments" ;

    if (&view_exists ($dbh, $vname)) {
	# debug "Dropping view $vname" ;
	my $query = "DROP VIEW $vname" ;
	# debug $query ;
	my $sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }
}

sub bump_sequence_to ( $$$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $seqname = shift or die "Not enough arguments" ;
    my $targetvalue = shift or die "Not enough arguments" ;

    my ($sth, @array) ;

    do {
	my $query = "select nextval ('$seqname')" ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;
    } until $array[0] >= $targetvalue ;
}

sub get_plugin_id ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $pluginname = shift or die "Not enough arguments" ;
    
    my $pluginid = -1 ;
    
    my $query = "SELECT plugin_id FROM plugins WHERE plugin_name = '$pluginname'" ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    if (my @array = $sth->fetchrow_array ()) {
	$pluginid = $array [0] ;
    }
    $sth->finish () ;
    
    return $pluginid ;
}

sub remove_plugin_from_groups ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $pluginid = shift or die "Not enough arguments" ;
    
    my $query = "DELETE FROM group_plugin WHERE plugin_id = $pluginid" ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    $sth->finish () ;
}

sub remove_plugin_from_users ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $pluginid = shift or die "Not enough arguments" ;
    
    my $query = "DELETE FROM user_plugin WHERE plugin_id = $pluginid" ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    $sth->finish () ;
}

sub update_plugin_db_version ( $$$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $pluginname = shift or die "Not enough arguments" ;
    my $v = shift or die "Not enough arguments" ;

    my $tablename = "plugin_" .$pluginname . "_meta_data" ;

    debug "Updating $tablename table." ;
    my $query = "UPDATE $tablename SET value = '$v' WHERE key = 'db-version'" ;
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    $sth->finish () ;
}

sub get_plugin_db_version ( $$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $pluginname = shift or die "Not enough arguments" ;

    my $tablename = "plugin_" .$pluginname . "_meta_data" ;

    my $query = "SELECT value FROM $tablename WHERE key = 'db-version'" ;
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    my $version = $array [0] ;

    return $version ;
}

sub debug ( $ ) {
    my $v = shift ;
    chomp $v ;
    print STDERR "$v\n" ;
}

sub create_plugin_metadata_table ( $$$ ) {
    my $dbh = shift or die "Not enough arguments" ;
    my $pluginname = shift or die "Not enough arguments" ;
    my $v = shift || "0" ;

    my $tablename = "plugin_" .$pluginname . "_meta_data" ;
    # Do we have the metadata table?

    my $query = "SELECT count(*) FROM pg_class WHERE relname = '$tablename' and relkind = 'r'";
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    # Let's create this table if we have it not

    if ($array [0] == 0) {
	debug "Creating $tablename table." ;
	$query = "CREATE TABLE $tablename (key varchar primary key, value text not null)" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }

    $query = "SELECT count(*) FROM $tablename WHERE key = 'db-version'";
    # debug $query ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    # Empty table?  We'll have to fill it up a bit

    if ($array [0] == 0) {
	debug "Inserting first data into $tablename table." ;
	$query = "INSERT INTO $tablename (key, value) VALUES ('db-version', '$v')" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }
}

sub is_lesser ( $$ ) {
    my $v1 = shift || 0 ;
    my $v2 = shift || 0 ;

    return (versioncmp($v1, $v2) < 0) ;
}

sub is_greater ( $$ ) {
    my $v1 = shift || 0 ;
    my $v2 = shift || 0 ;

    return (versioncmp($v1, $v2) > 0) ;
}

1 ;
