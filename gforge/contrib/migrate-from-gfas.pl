#! /usr/bin/perl -w

# GForge AS database is gfas
# FusionForge will be fusionforge

# /etc/gforge/gforge.conf: db_name → fusionforge
# gforge-config

# su - postgres -c 'dropdb fusionforge'
# /usr/lib/gforge/bin/install-db.sh configure

use DBI ;
use Data::Dumper ;
# use strict ;

use vars qw/$dbhAS $dbhFF $map @arrayAS $sthAS $sthFF/ ;

$dbhAS = DBI->connect("DBI:Pg:dbname=gfas;host=localhost","gforge","$ENV{DB_PW}") ;
$dbhFF = DBI->connect("DBI:Pg:dbname=fusionforge;host=localhost","gforge","$ENV{DB_PW}") ;

$dbhFF->begin_work ;

sub migrate_with_mapping ( $$$;$ ) {
    my $tsrc = shift ;
    my $tdest = shift ;
    my $mapping = shift ;
    my $where = shift || "" ;

    my @scols = keys %$mapping ;
    
    my $sql1 = "SELECT " . join (", ", @scols) . " FROM $tsrc $where" ;
    my $sth1 = $dbhAS->prepare ($sql1) ;
    # print Dumper $sql1 ;

    my $sql2 = "INSERT INTO $tdest (" . join (", ", map { $mapping->{$_} } @scols)
	. ") VALUES (" . join (", ", map { "?" } @scols) . ")" ;
    my $sth2 = $dbhFF->prepare ($sql2) ;
    # print Dumper $sql2 ;

    $sth1->execute ;
    while (my @arr = $sth1->fetchrow_array) {
	unless ($sth2->execute (@arr)) {
	    print Dumper \@arr ;
	    return 0;
	}
    }
    $sth1->finish ; $sth2->finish ;

    return 1 ;
}

# Migrate users
$map = {
    'user_id' => 'user_id',
    'unix_name' => 'user_name',
    'email' => 'email',
    'status' => 'status',
    'password_md5' => 'user_pw',
    'password_crypt' => 'unix_pw',
    'extract (epoch from create_date)::integer' => 'add_date',
    'confirm_hash' => 'confirm_hash',
    'email_new' => 'email_new',
    'timezone' => 'timezone',
    'address' => 'address',
    'address2' => 'address2',
    'phone' => 'phone',
    'fax' => 'fax',
    'title' => 'title',
    'firstname' => 'firstname',
    'lastname' => 'lastname',
    'ccode' => 'ccode',
    'language_id' => 'language',
} ;

migrate_with_mapping ('public.user', 'users', $map, "where unix_name not in ('admin', 'None')") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$dbhFF->do ("update users set status='A' where status='2'") ;
$dbhFF->do ("update users set status='N' where status='1'") ;
$dbhFF->do ("update users set status='P' where status='0'") ;
# Order matters!
$dbhFF->do ("update users set language = 23 where language = 4") ;
$dbhFF->do ("update users set language =  4 where language = 2") ;
$dbhFF->do ("update users set language =  2 where language = 9") ;
$dbhFF->do ("update users set language = 22 where language = 5") ;
$dbhFF->do ("update users set language = 11 where language = 6") ;
$dbhFF->do ("update users set language =  6 where language = 8") ;
$dbhFF->do ("update users set language =  8 where language = 7") ;
$dbhFF->do ("update users set language =  7 where language = 3") ;

$dbhFF->commit ;
