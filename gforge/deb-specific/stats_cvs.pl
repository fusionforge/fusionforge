#!/usr/bin/perl
#/**
#  *
#  * stats_cvs.pl - NIGHTLY SCRIPT
#  *
#  * Recurses through the /cvsroot directory tree and parses each projects
#  * '~/CVSROOT/history' file, and create and fill the sql table with 
#  * modified, and added to each project.
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
	
			## log modified  $type eq "M" 
			## log added  $type eq "A"
			## log others  $type neq "A"  neq "M"
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
# CVS doc says the meaning of the code letters.
#
#Letter          Meaning
#======          =========================================================
#O               Checkout
#T               Tag
#F               Release
#W               Update (no user file, remove from entries file)
#U               Update (file overwrote unmodified user file)
#G               Update (file was merged successfully into modified user file)
#C               Update (file was merged, but conflicts w/ modified user file)
#M               Commit (from modified file)
#A               Commit (an added file)
#R               Commit (the removal of a file)
#E               Export
	$sql = "
	CREATE TABLE deb_cvs_group AS
        	SELECT agg.cvsgroup,agg.year,agg.month,agg.day,agg.total AS total,m.modified AS modified,a.added AS added,o.others AS others
        	FROM (
        		SELECT cvsgroup,year,month,day,COUNT(*) AS total
        		FROM deb_cvs_dump
        		GROUP BY year,month,day,cvsgroup
		) agg
		LEFT JOIN (
        	SELECT cvsgroup,year,month,day,COUNT(*) AS modified
        	FROM deb_cvs_dump
		WHERE type='M'
        	GROUP BY year,month,day,cvsgroup
		) m USING (cvsgroup,year,month,day)
		LEFT JOIN (
        	SELECT cvsgroup,year,month,day,COUNT(*) AS added
        	FROM deb_cvs_dump
		WHERE type='A'
        	GROUP BY year,month,day,cvsgroup
		) a USING (cvsgroup,year,month,day)
		LEFT JOIN (
        	SELECT cvsgroup,year,month,day,COUNT(*) AS others
        	FROM deb_cvs_dump
		WHERE type!='A' and type!='M' 
        	GROUP BY year,month,day,cvsgroup
		) o USING (cvsgroup,year,month,day)
	";
	$dbh->do( $sql );
}

sub print_stats {
	my ($sql,$res,$temp);
	$sql = "SELECT * FROM deb_cvs_group order by year, month, day";
	$res = $dbh->prepare($sql);
	$res->execute();
	while ( my ($cvsgroup, $year, $month, $day, $total, $modified, $added, $others) = $res->fetchrow()) {
		print "$cvsgroup $year $month $day $total=$modified+$added+$others\n";
	}
	print "-----------------------------------------------------\n";
	$sql = "SELECT cvsgroup, SUM(modified), SUM(added) FROM deb_cvs_group group by cvsgroup";
	$res = $dbh->prepare($sql);
	$res->execute();
	while ( my ($cvsgroup, $modified, $added) = $res->fetchrow()) {
		print "$cvsgroup $modified $added\n";
	}
	print "-----------------------------------------------------\n";
}

#############
# main      #
#############
&db_connect;
&drop_tables;
&create_dump_table;
&dump_history;
&parse_history;
&print_stats;

