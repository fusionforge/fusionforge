#!/usr/bin/perl -w
#
# Debian-specific script to upgrade the database between releases

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

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($query, $sth, @array, $version, $action) ;

    # Do we have at least the basic schema?

    $query = "SELECT count(*) from pg_class where relname = 'groups'";
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
	
# 	@reqlist = (
# 	    "INSERT INTO groups (group_id, group_name, homepage, is_public, status, unix_group_name, unix_box,
#                     http_domain, short_description, cvs_box, license, register_purpose,
#                     license_other, register_time, use_bugs, rand_hash, use_mail, use_survey,
#                     use_patch, use_forum, use_pm, use_cvs, use_news, use_support, new_bug_address,
#                     new_patch_address, new_support_address, type, use_docman, send_all_bugs,
#                     send_all_patches, send_all_support, new_task_address, send_all_tasks,
#                     use_bug_depend_box, use_pm_depend_box)
# 	    VALUES (1, 'Site Admin', '$domain_name/admin/', 1, 'A', 'siteadmin', 'shell1', 
# 	    	    NULL, NULL, 'cvs1', 'website', NULL, NULL, 0, 0, NULL, 1, 0, 0, 0, 0, 0, 1, 1, '', '', '', 1, 1, 0, 0, 0, '', 0, 0, 0)",
# 	    "INSERT INTO groups (group_id, group_name, homepage, is_public, status, unix_group_name, unix_box,
#                     http_domain, short_description, cvs_box, license, register_purpose,
#                     license_other, register_time, use_bugs, rand_hash, use_mail, use_survey,
#                     use_patch, use_forum, use_pm, use_cvs, use_news, use_support, new_bug_address,
#                     new_patch_address, new_support_address, type, use_docman, send_all_bugs,
#                     send_all_patches, send_all_support, new_task_address, send_all_tasks,
#                     use_bug_depend_box, use_pm_depend_box)
# 	    VALUES ($newsadmin_groupid, 'Site News Admin', '$domain_name/news/', 0, 'A', 'newsadmin', 'shell1',
#                     NULL, NULL, 'cvs1', 'website', NULL, NULL, 0, 0, NULL, 1, 0, 0, 0, 0, 0, 1, 1, '', '',
#                     '', 1, 0, 0, 0, 0, '', 0, 0, 0)",
# 	    "INSERT INTO users (user_id, user_name, email, user_pw)  
# 		    VALUES (100,'None','$noreplymail','*********')", 
# 	    "INSERT INTO users VALUES (101,'$login','$email','$md5pwd','Sourceforge admin','A','/bin/bash','','N',2000,'$shellbox',$date,'',1,0,NULL,NULL,0,'','GMT', 1)", 
# 	    "INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (101, 1, 'A')",
# 	    "INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (101, $newsadmin_groupid, 'A')",
# 	    "INSERT INTO bug_category (bug_category_id, group_id, category_name) VALUES (100,1,'None')",
# 	    "INSERT INTO bug_group (bug_group_id, group_id, group_name) VALUES (100,1,'None')",
# 	    "INSERT INTO bug (bug_id,group_id,status_id,category_id,bug_group_id,submitted_by,assigned_to,resolution_id)
# 		    VALUES (100,1,100,100,100,100,100,100)",
# 	    "INSERT INTO patch_category (patch_category_id, group_id, category_name) VALUES (100,1,'None')",
# 	    "INSERT INTO patch (group_id,patch_status_id,patch_category_id,submitted_by,assigned_to)
# 		    VALUES (1,100,100,100,100)",
# 	    "INSERT INTO project_group_list (group_project_id,group_id) VALUES (1,1)",
# 	    "INSERT INTO project_task (group_project_id,created_by,status_id)
# 		    VALUES (1,100,100)",
# 	    "INSERT INTO support_category VALUES ('100','1','None')"
# 		    ) ;
#
# 	foreach my $s (@reqlist) {
# 	    $query = $s ;
# 	    $sth = $dbh->prepare ($query) ;
# 	    $sth->execute () ;
# 	    $sth->finish () ;
# 	}
#	@reqlist = () ;
#
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
#	debug "Inserting skills." ;
#
#	foreach my $skill (split /;/, $skill_list) {
#	    push @reqlist, "INSERT INTO people_skill (name) VALUES ('$skill')" ;
#	}
#
# 	foreach my $s (@reqlist) {
# 	    $query = $s ;
# 	    $sth = $dbh->prepare ($query) ;
# 	    $sth->execute () ;
# 	    $sth->finish () ;
# 	}
#	@reqlist = () ;

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
    
    # Init the state machine

    my ($l, $level, $inquote, $chunk, $rest) ;
    my $sql = "" ;
    my @sql_list = () ;
    
    # my $n = 0 ;

  FILELOOP: while ($l = <F>) {	# Loop over the file
      chomp $l ;
      $level = 0 ;
      $inquote = 0 ;
      $chunk = "" ;
      $rest = "" ;
      
    PARSELOOP: while (1) {	# Parse a request

	while ( ($l eq "")
		or ((! $inquote) and ($l =~ /^\s*$/))
		or ((! $inquote) and ($l =~ /^--/)) ) {
	    $l = <F> ;
	    if ($l) {
 		chomp $l ;
 	    } else {
 		last PARSELOOP ;
 	    }
	}
	($chunk, $rest) = ($l =~ /^([^()\\\';]*)(.*)/) ;
	$sql .= $chunk ;
	# debug "level = $level, inquote = $inquote, chunk = <$chunk>, rest = <$rest>, sql = <$sql>";

	# Here come the state transitions
      SWITCH: {
	  if ($rest =~ /^\(/) {	# Enter a paren block (unless we're inside a string)
	      $level += 1 unless $inquote ;
	      $sql .= '(' ;
	      $rest = substr $rest, 1 ;
	      last SWITCH;
	  }
	  if ($rest =~ /^\)/) {	# Exit a paren block (unless we're inside a string)
	      $level -= 1 unless $inquote ;
	      $sql .= ')' ;
	      $rest = substr $rest, 1 ;
	      last SWITCH;
	  }
	  if ($rest =~ /^\\\'/) { # Escaped single quote
	      if (!$inquote) {
		  debug "Encountered a \' sequence outside of a string." ;
		  debug "This really shouldn't have happened." ;
		  debug "I find it more prudent to just die now." ;
		  die "\' outside of a string -- check SQL file" ;
	      }
	      $sql .= '\\\'' ;
	      $rest = substr $rest, 2 ;
	      last SWITCH;
	  }
	  if ($rest =~ /^\\/) { # Other backslash is a normal character
	      $sql .= '\\' ;
	      $rest = substr $rest, 1 ;
	      last SWITCH;
	  }
	  if ($rest =~ /^;/) { # Semi-colon
	      if ($inquote) { # If inside a string, treat as a normal character
		  $sql .= ';' ;
		  $rest = substr $rest, 1 ;
	      } elsif ($level == 0) { # If out of a string and toplevel, end of SQL statement
		  last PARSELOOP;
	      } else{ # What, a semi-colon by itself not at toplevel?
		  debug "Encountered a semi-colon outside of a string and not at toplevel" ;
		  debug "This really shouldn't have happened." ;
		  debug "I find it more prudent to just die now." ;
		  die "semi-colon outside of a string and not at toplevel -- check SQL file" ;
	      }
	      last SWITCH;
	  }
	  if ($rest =~ /^\'/) { # Non-escaped single quote -- string delimiter
	      $inquote = $inquote ? 0 : 1 ; # Toggle $inquote
	      $sql .= '\'' ;
	      $rest = substr $rest, 1 ;
	      last SWITCH;
	  }
      } # SWITCH
	$l = $rest ;
    } # PARSELOOP
      
      # (Do something with $sql now that we have it :-)
      push @sql_list, $sql unless $sql eq "" ;
      # $n++ ; debug "SQL OK $n" ;
      $sql = "" ;
  } # FILELOOP
    
    close F ;
    
    return \@sql_list ;
}
