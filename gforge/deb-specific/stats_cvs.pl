#!/usr/bin/perl
#/**
#  *
#  * stats_cvs.pl - NIGHTLY SCRIPT
#  *
#  * Recurses through the /cvsroot directory tree and parses each projects
#  * '~/CVSROOT/history' file, and create and fill the sql table with 
#  * checkouts, commits, and adds to each project.
#  *
#  * @version   $Id$
#  *
#  */

# For the files
#use strict;
use Time::Local;
use POSIX qw( strftime );

# For the database
use DBI;
require("/usr/lib/sourceforge/lib/include.pl");
my $cvsroot = "/var/lib/sourceforge/chroot/cvsroot";
my $verbose = 1;
$|=0 if $verbose;
$|++;

sub drop_tables {
	my ($sql);
	$sql = "DROP TABLE deb_cvs_dump";
	$dbh->do( $sql );
	$sql = "DROP TABLE deb_cvs_group";
	$dbh->do( $sql );
}

sub create_dump_table {
	my ($sql);
	$sql = "CREATE TABLE deb_cvs_dump (
		type char(1),
		year integer NOT NULL,
		month integer NOT NULL,
		day integer NOT NULL,
		time integer NOT NULL,
		cvsuser text,
		cvsgroup text
	)";
	$dbh->do( $sql );
}

sub dump_history {
	my ($year, $month, $day, $day_begin, $day_end);
	
	print "Running tree at $cvsroot/\n";
	
	chdir( "$cvsroot" ) || die("Unable to make $cvsroot the working directory.\n");
	
	foreach $group ( glob("*") ) {
		next if ( ! -d "$group" );
		my ($cvs_co, $cvs_commit, $cvs_add, %usr_commit, %usr_add );
		print "Parsing $group/\n";
	
		open(HISTORY, "< $cvsroot/$group/CVSROOT/history") or print "E::Unable to open history for $group\n";
		while ( <HISTORY> ) {
			my ($time_parsed, $type, $cvstime, $user, $curdir, $module, $rev, $file );
	 
			## Split the cvs history entry into it's 6 fields.
			($cvstime,$user,$curdir,$module,$rev,$file) = split(/\|/, $_, 6 );
	
			## log commits  $type eq "M" 
			## log adds  $type eq "A"
			## log checkouts  $type eq "O" 
			$type = substr($cvstime, 0, 1);
			$time_parsed = hex( substr($cvstime, 1, 8) );
			$year	= strftime("%Y", gmtime( $time_parsed ) );
			$month	= strftime("%m", gmtime( $time_parsed ) );
			$day	= strftime("%d", gmtime( $time_parsed ) );
			$sql = "INSERT INTO deb_cvs_dump 
			(type,year,month,day,time,cvsuser,cvsgroup)
			VALUES ('$type','$year','$month','$day','$time_parsed','$user','$group')";
			
			#print "$sql";
			$dbh->do( $sql );
		}
		close( HISTORY );
	}
}

sub parse_history {
	my ($sql);
	$sql = "
	CREATE TABLE deb_cvs_group AS
        	SELECT agg.cvsgroup,agg.year,agg.month,agg.day,agg.total AS total,c.commits AS commits,a.adds AS adds,ch.checkouts AS checkouts,e.errors AS errors
        	FROM (
        		SELECT cvsgroup,year,month,day,COUNT(*) AS total
        		FROM deb_cvs_dump
        		GROUP BY year,month,day,cvsgroup
		) agg
		LEFT JOIN (
        	SELECT cvsgroup,COUNT(*) AS commits
        	FROM deb_cvs_dump
		WHERE type='A'
        	GROUP BY year,month,day,cvsgroup
		) c USING (cvsgroup)
		LEFT JOIN (
        	SELECT cvsgroup,COUNT(*) AS adds
        	FROM deb_cvs_dump
		WHERE type='M'
        	GROUP BY year,month,day,cvsgroup
		) a USING (cvsgroup)
		LEFT JOIN (
        	SELECT cvsgroup,COUNT(*) AS checkouts
        	FROM deb_cvs_dump
		WHERE type='O'
        	GROUP BY year,month,day,cvsgroup
		) ch USING (cvsgroup)
		LEFT JOIN (
        	SELECT cvsgroup,COUNT(*) AS errors
        	FROM deb_cvs_dump
		WHERE type='E'
        	GROUP BY year,month,day,cvsgroup
		) e USING (cvsgroup)
	";
	$dbh->do( $sql );
}

sub print_stats {
	my ($sql,$res,$temp);
	$sql = "SELECT * FROM deb_cvs_group";
	$res = $dbh->prepare($sql);
	$res->execute();
	while ( my ($cvsgroup, $year, $month, $day, $total, $commits, $adds, $checkouts, $errors) = $res->fetchrow()) {
		print "$cvsgroup $year $month $day $total=$commits+$adds+$checkouts+$errors\n";
	}
}

#############
# main      #
#############
&db_connect;
#&drop_tables;
#&create_dump_table;
#&dump_history;
#&parse_history;
&print_stats;

