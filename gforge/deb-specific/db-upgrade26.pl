#!/usr/bin/perl -w
#
# $Id$
#
# Debian-specific script to upgrade the database between releases
# Roland Mas <lolando@debian.org>

use DBI ;
use strict ;
use diagnostics ;

use vars qw/$dbh @reqlist/ ;

use vars qw/$sys_default_domain $sys_cvs_host $sys_download_host
    $sys_shell_host $sys_users_host $sys_docs_host $sys_lists_host
    $sys_dns1_host $sys_dns2_host $FTPINCOMING_DIR $FTPFILES_DIR
    $sys_urlroot $sf_cache_dir $sys_name $sys_themeroot
    $sys_news_group $sys_dbhost $sys_dbname $sys_dbuser $sys_dbpasswd
    $sys_ldap_base_dn $sys_ldap_host $admin_login $admin_password
    $server_admin $domain_name $newsadmin_groupid $skill_list/ ;

sub is_lesser ( $$ ) ;
sub is_greater ( $$ ) ;
sub debug ( $ ) ;
sub parse_sql_file ( $ ) ;

require ("/usr/lib/sourceforge/lib/include.pl") ; # Include all the predefined functions 

debug "You'll see some debugging info during this installation." ;
debug "Do not worry unless told otherwise." ;

&db_connect ;

debug "Connected to the database OK." ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($query, $sth, @array, $version, $action) ;

    # Do we have at least the basic schema?

    $query = "SELECT count(*) from pg_class where relname = 'groups'";
    debug $query ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    # Create Sourceforge database

    if ($array [0] == 0) {
	$action = "installation" ;
	debug "Creating initial Sourceforge database from files." ;

	my @filelist = qw{ /usr/lib/sourceforge/db/sf-2.6-complete.sql } ;
#	my @filelist = qw{ /usr/lib/sourceforge/db/SourceForge.sql
#			       /usr/lib/sourceforge/db/trove_defaults.sql
#			       /usr/lib/sourceforge/db/init-extra.sql } ;
	# TODO: user_rating.sql
			      
	foreach my $file (@filelist) {
	    debug "Processing $file" ;
	    @reqlist = @{ &parse_sql_file ($file) } ;
	    
 	    foreach my $s (@reqlist) {
 		$query = $s ;
		debug $query ;
  		$sth = $dbh->prepare ($query) ;
  		$sth->execute () ;
  		$sth->finish () ;
 	    }
 	}
	@reqlist = () ;

	debug "Adding local data." ;

	
	do "/etc/sourceforge/local.pl" or die "Cannot read /etc/sourceforge/local.pl" ;

	my ($login, $pwd, $md5pwd, $email, $shellbox, $noreplymail, $date) ;

	$login = $admin_login ;
	$pwd = $admin_password ;
	$md5pwd=qx/echo -n $pwd | md5sum/ ;
	chomp $md5pwd ;
	$email = $server_admin ;
	$shellbox = $domain_name ;
	$noreplymail="noreply\@$domain_name" ;
	$date = time () ;
	
 	@reqlist = (
 	    "INSERT INTO groups (group_id, group_name, homepage, is_public, status, unix_group_name, unix_box,
			http_domain, short_description, cvs_box, license, register_purpose,
			license_other, register_time, rand_hash, use_mail, use_survey,
			use_forum, use_pm, use_cvs, use_news, 
			type, use_docman, 
			new_task_address, send_all_tasks,
			use_pm_depend_box)
 	    VALUES (1, 'Site Admin', '$domain_name/admin/', 1, 'A', 'siteadmin', 'shell1', 
			NULL, NULL, 'cvs1', 'website', NULL,
			NULL, 0, 0, 1, 0,
			0, 0, 0, 1,
			1, 1,
			'', 0,
			0)",
 	    "INSERT INTO groups (group_id, group_name, homepage, is_public, status, unix_group_name, unix_box,
			http_domain, short_description, cvs_box, license, register_purpose,
			license_other, register_time, rand_hash, use_mail, use_survey,
			use_forum, use_pm, use_cvs, use_news, 
			type, use_docman, 
			new_task_address, send_all_tasks,
			use_pm_depend_box)
 	    VALUES ($newsadmin_groupid, 'Site News Admin', '$domain_name/news/', 0, 'A', 'newsadmin', 'shell1',
			NULL, NULL, 'cvs1', 'website', NULL,
			NULL, 0, 0, 1, 0,
			0, 0, 0, 1,
			1, 0,
			'', 0,
			0)",
 	    "INSERT INTO users (user_id, user_name, email, user_pw)  
 		    VALUES (100,'None','$noreplymail','*********')", 
 	    "INSERT INTO users VALUES (101,'$login','$email','$md5pwd','Sourceforge admin','A','/bin/bash','','N',2000,'$shellbox',$date,'',1,0,NULL,NULL,0,'','GMT', 1, 0)", 
 	    "INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (101, 1, 'A')",
 	    "INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (101, $newsadmin_groupid, 'A')",
# 	    "INSERT INTO bug_category (bug_category_id, group_id, category_name) VALUES (100,1,'None')",
# 	    "INSERT INTO bug_group (bug_group_id, group_id, group_name) VALUES (100,1,'None')",
# 	    "INSERT INTO bug (bug_id,group_id,status_id,category_id,bug_group_id,submitted_by,assigned_to,resolution_id)
# 		    VALUES (100,1,100,100,100,100,100,100)",
# 	    "INSERT INTO patch_category (patch_category_id, group_id, category_name) VALUES (100,1,'None')",
# 	    "INSERT INTO patch (group_id,patch_status_id,patch_category_id,submitted_by,assigned_to)
# 		    VALUES (1,100,100,100,100)",
 	    "INSERT INTO project_group_list (group_project_id,group_id) VALUES (1,1)",
 	    "INSERT INTO project_task (group_project_id,created_by,status_id)
 		    VALUES (1,100,100)",
# 	    "INSERT INTO support_category VALUES ('100','1','None')"
 		    ) ;

 	foreach my $s (@reqlist) {
 	    $query = $s ;
	    debug $query ;
 	    $sth = $dbh->prepare ($query) ;
 	    $sth->execute () ;
 	    $sth->finish () ;
 	}
	@reqlist = () ;

#	debug "Initialising sequences." ;
#
#	@filelist = qw{ /usr/lib/sourceforge/db/init-sequences.sql } ;
#			      
#	foreach my $file (@filelist) {
#	    debug "Processing $file" ;
#	    @reqlist = @{ &parse_sql_file ($file) } ;
#	    
# 	    foreach my $s (@reqlist) {
# 		$query = $s ;
#  		$sth = $dbh->prepare ($query) ;
#  		$sth->execute () ;
#  		$sth->finish () ;
# 	    }
# 	} 
#	@reqlist = () ;
#
	debug "Inserting skills." ;

	foreach my $skill (split /;/, $skill_list) {
	    push @reqlist, "INSERT INTO people_skill (name) VALUES ('$skill')" ;
	}

 	foreach my $s (@reqlist) {
 	    $query = $s ;
	    debug $query ;
 	    $sth = $dbh->prepare ($query) ;
 	    $sth->execute () ;
 	    $sth->finish () ;
 	}
	@reqlist = () ;

 	debug "Committing." ;
 	$dbh->commit () ;
    } else {
	$action = "upgrade" ;
    }

#    # Do we have the metadata table?
#
#    $query = "SELECT count(*) from pg_class where relname = 'debian_meta_data'";
#    $sth = $dbh->prepare ($query) ;
#    $sth->execute () ;
#    @array = $sth->fetchrow_array () ;
#    $sth->finish () ;
#
#    # Let's create this table if we have it not
#
#    if ($array [0] == 0) {
#	debug "Creating debian_meta_data table." ;
#	$query = "CREATE TABLE debian_meta_data (key varchar primary key, value text not null)" ;
#	$sth = $dbh->prepare ($query) ;
#	$sth->execute () ;
#	$sth->finish () ;
#	
#	# Now table should exist, let's enter its first value in it
#
#	debug "Inserting first data into debian_meta_data table." ;
#	$query = "INSERT INTO debian_meta_data (key, value) VALUES ('db-version', '2.5-7+just+before+8')" ;
#	$sth = $dbh->prepare ($query) ;
#	$sth->execute () ;
#	$sth->finish () ;
#
#	debug "Committing." ;
#	$dbh->commit () ;
#    }
#
#    # Now we have the metadata table with at least the "db-version" key
#    # We can continue our work based on the associated value
#    
#    $query = "select value from debian_meta_data where key = 'db-version'" ;
#    $sth = $dbh->prepare ($query) ;
#    $sth->execute () ;
#    @array = $sth->fetchrow_array () ;
#    $sth->finish () ;
#    
#    $version = $array [0] ;
#
#    # $version is the last successfully installed version
#    
#    if (is_lesser $version, "2.5-8") {
#	debug "Found version $version lesser than 2.5-8, adding row to people_job_category." ;
#	$query = "INSERT INTO people_job_category VALUES (100, 'Undefined', 0)" ;
#	$sth = $dbh->prepare ($query) ;
#	$sth->execute () ;
#	$sth->finish () ;
#
#	debug "Updating debian_meta_data table." ;
#	$query = "UPDATE debian_meta_data SET value = '2.5-8' where key = 'db-version'" ;
#	$sth = $dbh->prepare ($query) ;
#	$sth->execute () ;
#	$sth->finish () ;
#
#	debug "Committing." ;
#	$dbh->commit () ;
#    }
#
#    debug "It seems your database $action went well and smoothly.  That's cool." ;
#    debug "Please enjoy using Debian Sourceforge." ;
#    
#    # There should be a commit at the end of every block above.
#    # If there is not, then it might be symptomatic of a problem.
#    # For safety, we roll back.
#    $dbh->rollback ();
};

if ($@) {
    warn "Transaction aborted because $@" ;
    debug "Transaction aborted because $@" ;
    $dbh->rollback ;
    debug "Please report this bug on the Debian bug-tracking system." ;
    debug "Please include the previous messages as well to help debugging." ;
    debug "You should not worry too much about this," ;
    debug "your DB is still in a consistent state and should be usable." ;
    exit 1 ;
}

$dbh->rollback ;
$dbh->disconnect ;

sub is_lesser ( $$ ) {
    my $v1 = shift || 0 ;
    my $v2 = shift || 0 ;

    my $rc = system "dpkg --compare-versions $v1 lt $v2" ;
    
    return (! $rc) ;
}

sub is_greater ( $$ ) {
    my $v1 = shift || 0 ;
    my $v2 = shift || 0 ;

    my $rc = system "dpkg --compare-versions $v1 gt $v2" ;
    
    return (! $rc) ;
}

sub debug ( $ ) {
    my $v = shift ;
    chomp $v ;
    print STDERR "$v\n" ;
}

sub parse_sql_file ( $ ) {
    my $f = shift ;
    open F, $f || die "Could not open file $f: $!\n" ;

    # This is a state machine to parse potentially complex SQL files
    # into individual SQL requests/statements
    
    my %states = ('INIT' => 0,
		  'SCAN' => 1,
		  'SQL_SCAN' => 2,
		  'IN_SQL' => 3,
		  'END_SQL' => 4,
		  'QUOTE_SCAN' => 5,
		  'IN_QUOTE' => 6,
		  'START_COPY' => 7,
		  'IN_COPY' => 8,
		  'ERROR' => 666,
		  'DONE' => 999) ;
    my ($state, $l, $par_level, $chunk, $rest, $sql, @sql_list, $copy_table, $copy_rest, @copy_data) ;

    # Init the state machine

    $state = $states{INIT} ;
    
    # my $n = 0 ;
    
  STATE_LOOP: while ($state != $states{DONE}) { # State machine main loop
      debug "State = $state" ;
    STATE_SWITCH: {		# State machine step processing
	$state == $states{INIT} && do {
	    $par_level = 0 ;
	    $l = $sql = $chunk = $rest = "" ;	 
	    @sql_list = () ;
	    $copy_table = $copy_rest = "" ;
	    @copy_data = () ;
	    
	    $state = $states{SCAN} ;
	    last STATE_SWITCH ;
	} ;			# End of INIT state
	
	$state == $states{SCAN} && do {
	  SCAN_STATE_SWITCH: {
	      ( ($l eq "") or ($l =~ /^\s*$/) or ($l =~ /^--/) ) && do {
		  debug "SCAN -- \$l = <$l>" ;
		  $l = <F> ;
		  unless ($l) {
		      debug "Detected end of file." ;
		      $state = $states{DONE} ;
		      last SCAN_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{SCAN} ;
		  last SCAN_STATE_SWITCH ;
	      } ;

	      ( ($l =~ m/\s*copy\s+\"[\w_]+\"\s+from\s+stdin\s*;/i) 
		or ($l =~ m/\s*copy\s+[\w_]+\s+from\s+stdin\s*;/i) ) && do {
		    # Nothing to do
		    
		    $state = $states{START_COPY} ;
		    last SCAN_STATE_SWITCH ;
		} ;
	      
	      ( 1 ) && do {
		  debug "SCAN -- \$l = <$l>" ;
		  $sql = "" ;

		  $state = $states{SQL_SCAN} ;
		  last SCAN_STATE_SWITCH ;
	      } ;

	      die "Unknown event in SCAN state" ;
	  }			# SCAN_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of SCAN state
	
	$state == $states{SQL_SCAN} && do {
	  SQL_SCAN_STATE_SWITCH: {
	      ( ($l eq "") or ($l =~ /^\s*$/) or ($l =~ /^--/) ) && do {
		  debug "SQLSCAN -- \$l = <$l>" ;
		  $l = <F> ;
		  unless ($l) {
		      debug "End of file detected during an SQL statement." ;

		      $state = $states{ERROR} ;
		      last SQL_SCAN_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{SQL_SCAN} ;
		  last SQL_SCAN_STATE_SWITCH ;
	      } ;

	      ( 1 ) && do {
		  debug "SQLSCAN -- \$l = <$l>" ;
		  ($chunk, $rest) = ($l =~ /^([^()\';-]*)(.*)/) ;
		  $sql .= $chunk ;
		  
		  $state = $states{IN_SQL} ;
		  last SQL_SCAN_STATE_SWITCH ;
	      } ;
	      
	      die "Unknown event in SQL_SCAN state" ;
	  }			# SQL_SCAN_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of SQL_SCAN state
	
	$state == $states{IN_SQL} && do {
	  IN_SQL_STATE_SWITCH: {
	      ($rest =~ /^\(/) && do {
		  $par_level += 1 ;
		  $sql .= '(' ;
		  $rest = substr $rest, 1 ;
		  $l = $rest ;
		  
		  last IN_SQL_STATE_SWITCH ;
	      } ;

	      ( ($rest =~ /^\)/) and ($par_level > 0) ) && do {
		  $par_level -= 1 ;
		  $sql .= ')' ;
		  $rest = substr $rest, 1 ;
		  $l = $rest ;
		  
		  last IN_SQL_STATE_SWITCH ;
	      } ;

	      ($rest =~ /^\)/) && do {
		  debug "Detected ')' without any matching '('." ;
		  
		  $state = $states{ERROR} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;

	      ($rest =~ /^--/) && do {
		  $rest = "" ;
		  $l = $rest ;
		  
		  $state = $states{SQL_SCAN} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;

	      ($rest =~ /^-[^-]/) && do {
		  $sql .= '-' ;
		  $rest = substr $rest, 1 ;
		  $l = $rest ;
		  
		  last IN_SQL_STATE_SWITCH ;
	      } ;

	      ( ($rest =~ /^;/) and ($par_level == 0) ) && do {
		  $sql .= ';' ;
		  $rest = substr $rest, 1 ;
		  
		  $state = $states{END_SQL} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;

	      ($rest =~ /^;/) && do {
		  debug "Detected ';' within a parenthesis." ;
		  
		  $state = $states{ERROR} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;

	      ($rest eq "") && do {
		  $l = $rest ;
		  $sql .= " " ;

		  $state = $states{SQL_SCAN} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;

	      ($rest =~ /^\'/) && do {
		  $sql .= '\'' ;
		  $rest = substr $rest, 1 ;
		  
		  $state = $states{IN_QUOTE} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;
	      
	      die "Unknown event in IN_SQL state" ;
	  }			# IN_SQL_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_SQL state

	$state == $states{END_SQL} && do {
	  END_SQL_STATE_SWITCH: {
	      ($sql =~ /^\s*$/) && do {
		  debug "END_SQL -- \$sql = <$sql>" ;
		  debug "Empty request." ;
		  $sql = "" ;
		  $l = $rest ;

		  $state = $states{SQL_SCAN} ;
		  last END_SQL_STATE_SWITCH ;
	      } ;

	      ( 1 ) && do {
		  debug "END_SQL -- \$sql = <$sql>" ;
		  push @sql_list, $sql ;
		  $sql = "" ;
		  $l = $rest ;

		  $state = $states{SQL_SCAN} ;
		  last END_SQL_STATE_SWITCH ;
	      } ;

	  }			# END_SQL_STATE_SWITCH
	      last STATE_SWITCH ;
	} ;			# End of END_SQL state

	$state == $states{QUOTE_SCAN} && do {
	  QUOTE_SCAN_STATE_SWITCH: {
	      ($l eq "") && do {
		  $sql .= "\n" ;
		  $l = <F> ;
		  unless ($l) {
		      debug "Detected end of file inside a quoted string." ;
		      $state = $states{ERROR} ;
		      last QUOTE_SCAN_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  last QUOTE_SCAN_STATE_SWITCH ;
	      } ;

	      ( 1 ) && do {
		  ($chunk, $rest) = ($l =~ /^([^\\\']*)(.*)/) ;
		  $sql .= $chunk ;
		  
		  $state = $states{IN_QUOTE} ;
		  last QUOTE_SCAN_STATE_SWITCH ;
	      } ;

	  }			# QUOTE_SCAN_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of QUOTE_SCAN state
	
	$state == $states{IN_QUOTE} && do {
	  IN_QUOTE_STATE_SWITCH: {
	      ($rest =~ /^\'/) && do {
		  $sql .= '\'' ;
		  $rest = substr $rest, 1 ;
		  
		  $state = $states{IN_SQL} ;
		  last IN_QUOTE_STATE_SWITCH ;
	      } ;

	      ($rest =~ /^\\\'/) && do {
		  $sql .= '\\\'' ;
		  $rest = substr $rest, 2 ;
		  
		  last IN_QUOTE_STATE_SWITCH ;
	      } ;

	      ($rest =~ /^\\[^\\]/) && do {
		  $sql .= '\\' ;
		  $rest = substr $rest, 1 ;
		  
		  last IN_QUOTE_STATE_SWITCH ;
	      } ;

	      ($rest eq "") && do {
		  # Nothing to do
		  
		  $state = $states{QUOTE_SCAN} ;
		  last IN_QUOTE_STATE_SWITCH ;
	      } ;

	      ( 1 ) && do {
		  debug "Unknown event in IN_QUOTE state." ;
		  $state = $states{ERROR} ;
		  last IN_QUOTE_STATE_SWITCH ;
	      } ;

	  }			# IN_QUOTE_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_QUOTE state

	$state == $states{START_COPY} && do {
	  START_COPY_STATE_SWITCH: {
	      ($l =~ m/\s*copy\s+\"[\w_]+\"\s+from\s+stdin\s*;/i) && do {
		  ($copy_table, $copy_rest) = ($l =~ /\s*copy\s+\"([\w_]+)\"\s+from\s+stdin\s*;(.*)/i) ;
		  $l = <F> ;
		  unless ($l) {
		      debug "Detected end of file within a COPY statement." ;
		      $state = $states{ERROR} ;
		      last START_COPY_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{IN_COPY} ;
		  last START_COPY_STATE_SWITCH ;
	      } ;

	      ($l =~ m/\s*copy\s+[\w_]+\s+from\s+stdin\s*;/i) && do {
		  ($copy_table, $copy_rest) = ($l =~ /\s*copy\s+([\w_]+)\s+from\s+stdin\s*;(.*)/i) ;
		  $l = <F> ;
		  unless ($l) {
		      debug "Detected end of file within a COPY statement." ;
		      $state = $states{ERROR} ;
		      last START_COPY_STATE_SWITCH ;
		  }
		  chomp $l ;

		  $state = $states{IN_COPY} ;
		  last START_COPY_STATE_SWITCH ;
	      } ;
     
	      ( 1 ) && do {
		  debug "Unknown event in START_COPY state." ;
		  $state = $states{ERROR} ;
		  last START_COPY_STATE_SWITCH ;
	      } ;

	  }			# START_COPY_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of START_COPY state

	$state == $states{IN_COPY} && do {
	  IN_COPY_SWITCH: {
	      ($l =~ /^\\\.$/) && do {
		  $l = $copy_rest ;

		  $state = $states{SCAN} ;
		  last IN_COPY_STATE_SWITCH ;
	      } ;
	      
	      ( 1 ) && do {
		  @copy_data = split /\t/, $l ;
		  @copy_data = map { s/\'/\\\'/g } @copy_data ;
		  @copy_data = map { "'" . $_ . "'" } @copy_data ;
		  $sql = "INSERT INTO \"$copy_table\" VALUES (" ;
		  $sql .= join (", ", @copy_data) ;
		  $sql .= ")" ;
		  push @sql_list, $sql ;
		  $l = <F> ;
		  unless ($l) {
		      debug "Detected end of file within a COPY statement." ;
		      $state = $states{ERROR} ;
		      last IN_COPY_STATE_SWITCH ;
		  }
		  chomp $l ;

		  last IN_COPY_STATE_SWITCH ;
	      } ;

	  }			# IN_COPY_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_COPY state

	$state == $states{DONE} && do {
	    debug "End of file detected." ;

	    last STATE_SWITCH ;
	} ;			# End of DONE state

	$state == $states{ERROR} && do {
	    debug "Reached the ERROR state.  Dying." ;
	    die "State machine is buggy." ;
	    
	    last STATE_SWITCH ;
	} ;			# End of ERROR state

	( 1 ) && do {
	    debug "State machine went in an unknown state...  Redirecting to ERROR." ;
	    $state = $states{ERROR} ;
	    last STATE_SWITCH ;
	} ;

    }				# STATE_SWITCH
  }				# STATE_LOOP

    close F ;
    return \@sql_list ;
}
