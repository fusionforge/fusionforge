#!/usr/bin/perl -w
#
# $Id$
#
# Debian-specific script to upgrade the database between releases
# Roland Mas <lolando@debian.org>

use strict ;
use diagnostics ;

use DBI ;
use MIME::Base64 ;
use HTML::Entities ;

use vars qw/$dbh @reqlist/ ;
use vars qw/$sys_default_domain $sys_cvs_host $sys_download_host
    $sys_shell_host $sys_users_host $sys_docs_host $sys_lists_host
    $sys_dns1_host $sys_dns2_host $FTPINCOMING_DIR $FTPFILES_DIR
    $sys_urlroot $sf_cache_dir $sys_name $sys_themeroot
    $sys_news_group $sys_dbhost $sys_dbname $sys_dbuser $sys_dbpasswd
    $sys_ldap_base_dn $sys_ldap_host $admin_login $admin_password
    $server_admin $domain_name $newsadmin_groupid $statsadmin_groupid
    $skill_list/ ;

sub is_lesser ( $$ ) ;
sub is_greater ( $$ ) ;
sub debug ( $ ) ;
sub parse_sql_file ( $ ) ;

require ("/usr/lib/sourceforge/lib/include.pl") ; # Include all the predefined functions 

debug "You'll see some debugging info during this installation." ;
debug "Do not worry unless told otherwise." ;

&db_connect ;

# debug "Connected to the database OK." ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($query, $sth, @array, $version, $action, $path) ;

    # Do we have at least the basic schema?

    $query = "SELECT count(*) from pg_class where relname = 'groups'";
    # debug $query ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    # Create Sourceforge database

    if ($array [0] == 0) {
	# Installing SF 2.6 from scratch
	$action = "installation" ;
	debug "Creating initial Sourceforge database from files." ;

	&create_metadata_table ("2.5.9999") ;

	debug "Updating debian_meta_data table." ;
	$query = "INSERT INTO debian_meta_data (key, value) VALUES ('current-path', 'scratch-to-2.6')" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
	debug "Committing." ;
	$dbh->commit () ;

    } else {
	$action = "upgrade" ;

	$version = &get_db_version ;
	if (is_lesser $version, "2.5.9999") {
	    debug "Upgrading your database scheme from 2.5" ;
	    
	    debug "Updating debian_meta_data table." ;
	    $query = "INSERT INTO debian_meta_data (key, value) VALUES ('current-path', '2.5-to-2.6')" ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	    debug "Committing." ;
	    $dbh->commit () ;
	}
    }

    $query = "SELECT count(*) from debian_meta_data where key = 'current-path'";
    # debug $query ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    if ($array[0] == 0) {
	$path = "" ;
    } else {
	$query = "SELECT value from debian_meta_data where key = 'current-path'";
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;
	
	$path = $array[0] ;
    }

  PATH_SWITCH: {
      ($path eq 'scratch-to-2.6') && do {
	  $version = &get_db_version ;
	  if (is_lesser $version, "2.5.9999.1+global+data+done") {
	      my @filelist = qw{ /usr/lib/sourceforge/db/sf-2.6-complete.sql } ;
	      # TODO: user_rating.sql

	      foreach my $file (@filelist) {
		  debug "Processing $file" ;
		  @reqlist = @{ &parse_sql_file ($file) } ;
		  
		  foreach my $s (@reqlist) {
		      $query = $s ;
		      # debug $query ;
		      $sth = $dbh->prepare ($query) ;
		      $sth->execute () ;
		      $sth->finish () ;
		  }
	      }
	      @reqlist = () ;
	      
	      &update_db_version ("2.5.9999.1+global+data+done") ;
	      debug "Committing." ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  if (is_lesser $version, "2.5.9999.2+local+data+done") {
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
			  "UPDATE groups SET homepage = '$domain_name/admin/' where group_id = 1",
			  "UPDATE groups SET homepage = '$domain_name/news/' where group_id = 2",
			  "UPDATE groups SET homepage = '$domain_name/stats/' where group_id = 3",
			  "UPDATE groups SET homepage = '$domain_name/peerrating/' where group_id = 4",
			  "UPDATE users SET email = '$noreplymail' where user_id = 100",
			  "INSERT INTO users VALUES (101,'$login','$email','$md5pwd','Sourceforge admin','A','/bin/bash','','N',2000,'$shellbox',$date,'',1,0,NULL,NULL,0,'','GMT', 1, 0)", 
			  "SELECT setval ('\"users_pk_seq\"', 102, 'f')",
			  "INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (101, 1, 'A')",
			  "INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (101, 2, 'A')",
			  "INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (101, 3, 'A')",
			  "INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (101, 4, 'A')"
			  ) ;
	      
	      foreach my $s (@reqlist) {
		  $query = $s ;
		  # debug $query ;
		  $sth = $dbh->prepare ($query) ;
		  $sth->execute () ;
		  $sth->finish () ;
	      }
	      @reqlist = () ;
	      
	      &update_db_version ("2.5.9999.2+local+data+done") ;
	      debug "Committing." ;
	      $dbh->commit () ;
	  }
	  
	  $version = &get_db_version ;
	  if (is_lesser $version, "2.5.9999.3+skills+done") {
	      debug "Inserting skills." ;
	      
	      foreach my $skill (split /;/, $skill_list) {
		  push @reqlist, "INSERT INTO people_skill (name) VALUES ('$skill')" ;
	      }
	      
	      foreach my $s (@reqlist) {
		  $query = $s ;
		  # debug $query ;
		  $sth = $dbh->prepare ($query) ;
		  $sth->execute () ;
		  $sth->finish () ;
	      }
	      @reqlist = () ;
	      
	      &update_db_version ("2.5.9999.3+skills+done") ;
	      debug "Committing." ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  if (is_lesser $version, "2.6-0") {
	      debug "Updating debian_meta_data table." ;
	      $query = "DELETE FROM debian_meta_data WHERE key = 'current-path'" ;
	      # debug $query ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;
	      
	      &update_db_version ("2.6-0") ;
	      debug "Committing." ;
	      $dbh->commit () ;
	  }

	  last PATH_SWITCH ;
      } ;

      ($path eq '2.5-to-2.6') && do {
	  
	  $version = &get_db_version ;
	  if (is_lesser $version, "2.5.9999.1+data+upgraded") {
	      debug "Upgrading your database scheme from 2.5" ;

	      @reqlist = @{ &parse_sql_file ("/usr/lib/sourceforge/db/sf2.5-to-sf2.6.sql") } ;
	      foreach my $s (@reqlist) {
		  $query = $s ;
		  # debug $query ;
		  $sth = $dbh->prepare ($query) ;
		  $sth->execute () ;
		  $sth->finish () ;
	      }
	      @reqlist = () ;

	      &update_db_version ("2.5.9999.1+data+upgraded") ;
	      debug "Committing." ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  if (is_lesser $version, "2.5.9999.2+artifact+transcoded") {
	      debug "Transcoding the artifact data fields" ;

	      $query = "SELECT id,bin_data FROM artifact_file ORDER BY id ASC" ;
	      # debug $query ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      while (@array = $sth->fetchrow_array) {
		  my $query2 = "UPDATE artifact_file SET bin_data='" ;
		  $query2 .= encode_base64 (decode_entities ($array [1])) ;
		  $query2 .= "' WHERE id=" ;
		  $query2 .= $array [0] ;
		  $query2 .= "" ;
		  # debug $query2 ;
		  my $sth2 =$dbh->prepare ($query2) ;
		  $sth2->execute () ;
		  $sth2->finish () ;
	      }
	      $sth->finish () ;

	      &update_db_version ("2.5.9999.2+artifact+transcoded") ;
	      debug "Committing." ;
	      $dbh->commit () ;
	  }


	  $version = &get_db_version ;
	  if (is_lesser $version, "2.6-0") {
	      debug "Updating debian_meta_data table." ;
	      $query = "DELETE FROM debian_meta_data WHERE key = 'current-path'" ;
	      # debug $query ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;
	      
	      &update_db_version ("2.6-0") ;
	      debug "Committing." ;
	      $dbh->commit () ;
	  }

	  last PATH_SWITCH ;
      } ;
  }
    
    debug "It seems your database $action went well and smoothly.  That's cool." ;
    debug "Please enjoy using Debian Sourceforge." ;
    
    # There should be a commit at the end of every block above.
    # If there is not, then it might be symptomatic of a problem.
    # For safety, we roll back.
    $dbh->rollback ();
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
    my ($state, $l, $par_level, $chunk, $rest, $sql, @sql_list, $copy_table, $copy_rest, @copy_data, @copy_data_tmp, $copy_field) ;

    # Init the state machine

    $state = $states{INIT} ;
    
    # my $n = 0 ;
    
  STATE_LOOP: while ($state != $states{DONE}) { # State machine main loop
      # debug "STATE_LOOP: state = $state" ;
    STATE_SWITCH: {		# State machine step processing
	$state == $states{INIT} && do {
	    # debug "State = INIT" ;
	    $par_level = 0 ;
	    $l = $sql = $chunk = $rest = "" ;	 
	    @sql_list = () ;
	    $copy_table = $copy_rest = "" ;
	    @copy_data = @copy_data_tmp = () ;
	    $copy_field = "" ;
	    
	    $state = $states{SCAN} ;
	    last STATE_SWITCH ;
	} ;			# End of INIT state
	
	$state == $states{SCAN} && do {
	    # debug "State = SCAN" ;
	  SCAN_STATE_SWITCH: {
	      ( ($l eq "") or ($l =~ /^\s*$/) or ($l =~ /^--/) ) && do {
		  $l = <F> ;
		  unless ($l) {
		      $state = $states{DONE} ;
		      last SCAN_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  last SCAN_STATE_SWITCH ;
	      } ;

	      ( ($l =~ m/\s*copy\s+\"[\w_]+\"\s+from\s+stdin\s*;/i) 
		or ($l =~ m/\s*copy\s+[\w_]+\s+from\s+stdin\s*;/i) ) && do {
		    # Nothing to do
		    
		    $state = $states{START_COPY} ;
		    last SCAN_STATE_SWITCH ;
		} ;
	      
	      ( 1 ) && do {
		  $sql = "" ;

		  $state = $states{SQL_SCAN} ;
		  last SCAN_STATE_SWITCH ;
	      } ;

	  }			# SCAN_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of SCAN state
	
	$state == $states{SQL_SCAN} && do {
	    # debug "State = SQL_SCAN" ;
	  SQL_SCAN_STATE_SWITCH: {
	      ( ($l eq "") or ($l =~ /^\s*$/) or ($l =~ /^--/) ) && do {
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
		  ($chunk, $rest) = ($l =~ /^([^()\';-]*)(.*)/) ;
		  $sql .= $chunk ;
		  
		  $state = $states{IN_SQL} ;
		  last SQL_SCAN_STATE_SWITCH ;
	      } ;
	      
	  }			# SQL_SCAN_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of SQL_SCAN state
	
	$state == $states{IN_SQL} && do {
	    # debug "State = IN_SQL" ;
	  IN_SQL_STATE_SWITCH: {
	      ($rest =~ /^\(/) && do {
		  $par_level += 1 ;
		  $sql .= '(' ;
		  $rest = substr $rest, 1 ;
		  $l = $rest ;

		  $state = $states{SQL_SCAN} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;

	      ( ($rest =~ /^\)/) and ($par_level > 0) ) && do {
		  $par_level -= 1 ;
		  $sql .= ')' ;
		  $rest = substr $rest, 1 ;
		  $l = $rest ;
		  
		  $state = $states{SQL_SCAN} ;
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
		  
		  $state = $states{SQL_SCAN} ;
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
		  $l = $rest ;
		  
		  $state = $states{IN_QUOTE} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;
	      
	      ( 1 ) && do {
		  debug "Unknown event in IN_SQL state" ;
		  $state = $states{ERROR} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;
	  }			# IN_SQL_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_SQL state

	$state == $states{END_SQL} && do {
	    # debug "State = END_SQL" ;
	  END_SQL_STATE_SWITCH: {
	      ($sql =~ /^\s*$/) && do {
		  $sql = "" ;
		  $l = $rest ;

		  $state = $states{SCAN} ;
		  last END_SQL_STATE_SWITCH ;
	      } ;

	      ( 1 ) && do {
		  push @sql_list, $sql ;
		  $sql = "" ;
		  $l = $rest ;

		  $state = $states{SCAN} ;
		  last END_SQL_STATE_SWITCH ;
	      } ;

	  }			# END_SQL_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of END_SQL state

	$state == $states{QUOTE_SCAN} && do {
	    # debug "State = QUOTE_SCAN" ;
	  QUOTE_SCAN_STATE_SWITCH: {
	      ($rest eq "") && do {
		  $sql .= "\n" ;
		  $l = <F> ;
		  unless ($l) {
		      debug "Detected end of file inside a quoted string." ;
		      $state = $states{ERROR} ;
		      last QUOTE_SCAN_STATE_SWITCH ;
		  }
		  chomp $l ;
		  $rest = $l ;
		  
		  last QUOTE_SCAN_STATE_SWITCH ;
	      } ;

	      ( 1 ) && do {
		  ($chunk, $rest) = ($l =~ /^([^\\\']*)(.*)/) ;
		  # debug "chunk = $chunk" ;
		  # debug "rest = $rest" ;
		  $sql .= $chunk ;
		  
		  $state = $states{IN_QUOTE} ;
		  last QUOTE_SCAN_STATE_SWITCH ;
	      } ;

	  }			# QUOTE_SCAN_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of QUOTE_SCAN state
	
	$state == $states{IN_QUOTE} && do {
	    # debug "State = IN_QUOTE" ;
	  IN_QUOTE_STATE_SWITCH: {
	      ($rest =~ /^\'/) && do {
		  $sql .= '\'' ;
		  $rest = substr $rest, 1 ;
		  $l = $rest ;
		  
		  $state = $states{SQL_SCAN} ;
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

	      ($rest =~ /^\\$/) && do {
		  $sql .= "\n" ;
		  $rest = substr $rest, 1 ;
		  
		  last IN_QUOTE_STATE_SWITCH ;
	      } ;

	      ( 1 ) && do {
		  $l = $rest ;
		  
		  $state = $states{QUOTE_SCAN} ;
		  last IN_QUOTE_STATE_SWITCH ;
	      } ;

	  }			# IN_QUOTE_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_QUOTE state

	$state == $states{START_COPY} && do {
	    # debug "State = START_COPY" ;
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
	    # debug "State = IN_COPY" ;
	  IN_COPY_STATE_SWITCH: {
	      ($l =~ /^\\\.$/) && do {
		  $l = $copy_rest ;

		  $state = $states{SCAN} ;
		  last IN_COPY_STATE_SWITCH ;
	      } ;
	      
	      ( 1 ) && do {
		  @copy_data = () ;
		  @copy_data_tmp = split /\t/, $l ;
		  foreach $copy_field (@copy_data_tmp) {
		      if ($copy_field eq '\N') {
			  $copy_field = 'NULL' ;
		      } else {
			  $copy_field =~ s/\'/\\\'/g ;
			  $copy_field = "'" . $copy_field . "'" ;
		      }
		      push @copy_data, $copy_field ;
		  }
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
	    # debug "State = DONE" ;
	    last STATE_SWITCH ;
	} ;			# End of DONE state

	$state == $states{ERROR} && do {
	    # debug "State = ERROR" ;
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

sub create_metadata_table ( $ ) {
    my $v = shift || "2.5-7+just+before+8" ;
    # Do we have the metadata table?

    my $query = "SELECT count(*) from pg_class where relname = 'debian_meta_data'";
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    # Let's create this table if we have it not

    if ($array [0] == 0) {
	debug "Creating debian_meta_data table." ;
	$query = "CREATE TABLE debian_meta_data (key varchar primary key, value text not null)" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }
    
    $query = "SELECT count(*) from debian_meta_data where key = 'db-version'";
    # debug $query ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    # Empty table?  We'll have to fill it up a bit

    if ($array [0] == 0) {
	debug "Inserting first data into debian_meta_data table." ;
	$query = "INSERT INTO debian_meta_data (key, value) VALUES ('db-version', '$v')" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }
}

sub update_db_version ( $ ) {
    my $v = shift or die "Not enough arguments" ;

    debug "Updating debian_meta_data table." ;
    my $query = "UPDATE debian_meta_data SET value = '$v' where key = 'db-version'" ;
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    $sth->finish () ;
}

sub get_db_version () {
    my $query = "select value from debian_meta_data where key = 'db-version'" ;
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;
    
    my $version = $array [0] ;

    return $version ;
}
