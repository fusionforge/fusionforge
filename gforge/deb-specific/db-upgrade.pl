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

use vars qw/$dbh @reqlist $query/ ;
use vars qw/$sys_default_domain $sys_scm_host $sys_download_host
    $sys_shell_host $sys_users_host $sys_docs_host $sys_lists_host
    $sys_dns1_host $sys_dns2_host $FTPINCOMING_DIR $FTPFILES_DIR
    $sys_urlroot $sf_cache_dir $sys_name $sys_themeroot
    $sys_news_group $sys_dbhost $sys_dbname $sys_dbuser $sys_dbpasswd
    $sys_ldap_base_dn $sys_ldap_host $admin_login $admin_password
    $server_admin $domain_name $newsadmin_groupid $statsadmin_groupid
    $skill_list/ ;

require ("/etc/gforge/local.pl") ; 
require ("/usr/lib/gforge/lib/sqlparser.pm") ; # Our magic SQL parser
require ("/usr/lib/gforge/lib/sqlhelper.pm") ; # Our SQL functions

&debug ("You'll see some debugging info during this installation.") ;
&debug ("Do not worry unless told otherwise.") ;

if ( "$sys_dbname" ne "gforge" || "$sys_dbuser" ne "gforge" ) {
$dbh ||= DBI->connect("DBI:Pg:dbname=$sys_dbname","$sys_dbuser","$sys_dbpasswd");
} else {
$dbh ||= DBI->connect("DBI:Pg:dbname=$sys_dbname");
}
die "Cannot connect to database: $!" if ( ! $dbh );

# debug "Connected to the database OK." ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($sth, @array, $version, $action, $path, $target) ;

    # Do we have at least the basic schema?

    $query = "SELECT count(*) from pg_class where relname = 'groups' and relkind = 'r'";
    # debug $query ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    # Create Sourceforge database

    if ($array [0] == 0) {	# No 'groups' table
	# Installing SF 2.6 from scratch
	$action = "installation" ;
	&debug ("Creating initial Sourceforge database from files.") ;

	&create_metadata_table ("2.5.9999") ;

	$query = "SELECT count(*) from debian_meta_data where key = 'current-path'";
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;
	if ($array[0] == 0) {
	    &debug ("Updating debian_meta_data table.") ;
	    $query = "INSERT INTO debian_meta_data (key, value) VALUES ('current-path', 'scratch-to-2.6')" ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	&debug ("Committing.") ;
	$dbh->commit () ;

    } else {			# A 'groups' table exists
	$action = "upgrade" ;

	$query = "SELECT count(*) from pg_class where relname = 'debian_meta_data' and relkind = 'r'";
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;

	if ($array[0] == 0) {	# No 'debian_meta_data' table
	    # If we're here, we're upgrading from 2.5-7 or earlier
	    # We therefore need to create the table
	    &create_metadata_table ("2.5-7+just+before+8") ;
	}

	$version = &get_db_version ;
	if (&is_lesser ($version, "2.5.9999")) {
	    &debug ("Found an old (2.5) database, will upgrade to 2.6") ;

	    $query = "SELECT count(*) from debian_meta_data where key = 'current-path'";
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    @array = $sth->fetchrow_array () ;
	    $sth->finish () ;

	    if ($array[0] == 0) {
		# &debug ("Updating debian_meta_data table.") ;
		$query = "INSERT INTO debian_meta_data (key, value) VALUES ('current-path', '2.5-to-2.6')" ;
		# debug $query ;
		$sth = $dbh->prepare ($query) ;
		$sth->execute () ;
		$sth->finish () ;
		&debug ("Committing.") ;
		$dbh->commit () ;
	    }
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
	  $target = "2.5.9999.1+global+data+done" ;
	  if (&is_lesser ($version, $target)) {
	      my @filelist = qw{ /usr/lib/gforge/db/sf-2.6-complete.sql } ;
	      # TODO: user_rating.sql

	      foreach my $file (@filelist) {
		  &debug ("Processing $file") ;
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

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5.9999.2+local+data+done" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Adding local data.") ;

	      do "/etc/gforge/local.pl" or die "Cannot read /etc/gforge/local.pl" ;

	      my ($login, $pwd, $md5pwd, $email, $noreplymail, $date) ;

	      $login = $admin_login ;
	      $pwd = $admin_password ;
	      $md5pwd=qx/echo -n $pwd | md5sum/ ;
	      chomp $md5pwd ;
	      $md5pwd =~ s/(.{32}) .*/$1/ ;
	      $email = $server_admin ;
	      $noreplymail="noreply\@$domain_name" ;
	      $date = time () ;

	      @reqlist = (
			  "UPDATE groups SET homepage = '$domain_name/admin/' where group_id = 1",
			  "UPDATE groups SET homepage = '$domain_name/news/' where group_id = 2",
			  "UPDATE groups SET homepage = '$domain_name/stats/' where group_id = 3",
			  "UPDATE groups SET homepage = '$domain_name/peerrating/' where group_id = 4",
			  "UPDATE users SET email = '$noreplymail' where user_id = 100",
			  "INSERT INTO users VALUES (101,'$login','$email','$md5pwd','Sourceforge admin','A','/bin/bash','','N',2000,'shell',$date,'',1,0,NULL,NULL,0,'','GMT', 1, 0)", 
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

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5.9999.3+skills+done" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Inserting skills.") ;

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

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.6-0+checkpoint+1" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Updating debian_meta_data table.") ;
	      $query = "DELETE FROM debian_meta_data WHERE key = 'current-path'" ;
	      # debug $query ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  last PATH_SWITCH ;
      } ;

      ($path eq '2.5-to-2.6') && do {

	  $version = &get_db_version ;
	  $target = "2.5-8" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Adding row to people_job_category.") ;
	      $query = "INSERT INTO people_job_category VALUES (100, 'Undefined', 0)" ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5-25" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Adding row to supported_languages.") ;
	      $query = "INSERT INTO supported_languages VALUES (15, 'Korean', 'Korean.class', 'Korean', 'kr')" ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5-27" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Fixing unix_box entries.") ;

	      $query = "update groups set unix_box = 'shell'" ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;

	      $query = "update users set unix_box = 'shell'" ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;

	      &debug ("Also fixing a few sequences.") ;

	      &bump_sequence_to ($dbh, "bug_pk_seq", 100) ;
	      &bump_sequence_to ($dbh, "project_task_pk_seq", 100) ;

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

 	  $version = &get_db_version ;
 	  $target = "2.5-30" ;
 	  if (&is_lesser ($version, $target)) {
 	      &debug ("Adding rows to supported_languages.") ;
	      @reqlist = (
			  "INSERT INTO supported_languages VALUES (16,'Bulgarian','Bulgarian.class','Bulgarian','bg')",
			  "INSERT INTO supported_languages VALUES (17,'Greek','Greek.class','Greek','el')",
			  "INSERT INTO supported_languages VALUES (18,'Indonesian','Indonesian.class','Indonesian','id')",
			  "INSERT INTO supported_languages VALUES (19,'Portuguese (Brazillian)','PortugueseBrazillian.class','PortugueseBrazillian', 'br')",
			  "INSERT INTO supported_languages VALUES (20,'Polish','Polish.class','Polish','pl')",
			  "INSERT INTO supported_languages VALUES (21,'Portuguese','Portuguese.class','Portuguese', 'pt')",
			  "INSERT INTO supported_languages VALUES (22,'Russian','Russian.class','Russian','ru')"
			  ) ;

	      foreach my $s (@reqlist) {
		  $query = $s ;
		  # debug $query ;
		  $sth = $dbh->prepare ($query) ;
		  $sth->execute () ;
		  $sth->finish () ;
	      }
	      @reqlist = () ;

 	      &update_db_version ($target) ;
 	      &debug ("Committing.") ;
 	      $dbh->commit () ;
 	  }

	  $version = &get_db_version ;
	  $target = "2.5-32" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Fixing unix_uid entries.") ;

	      $query = "UPDATE users SET unix_uid = nextval ('unix_uid_seq') WHERE unix_status != 'N' AND status != 'P' AND unix_uid = 0" ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5.9999.1+temp+data+dropped" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Preparing to upgrade your database - dropping temporary tables") ;

	      my @tables = qw/ user_metric_tmp1_1 user_metric_tmp1_2
		  user_metric_tmp1_3 user_metric_tmp1_4
		  user_metric_tmp1_5 user_metric_tmp1_6
		  user_metric_tmp1_7 user_metric_tmp1_8 user_metric1
		  user_metric2 user_metric3 user_metric4 user_metric5
		  user_metric6 user_metric7 user_metric8
		  project_counts_tmp project_metric_tmp
		  project_metric_tmp1 project_counts_weekly_tmp
		  project_metric_weekly_tmp project_metric_weekly_tmp1
		  / ;

	      my @sequences = qw/ user_metric1_ranking_seq
		  user_metric2_ranking_seq user_metric3_ranking_seq
		  user_metric4_ranking_seq user_metric5_ranking_seq
		  user_metric6_ranking_seq user_metric7_ranking_seq
		  user_metric8_ranking_seq project_metric_weekly_seq
		  trove_treesum_trove_treesum_seq
		  project_metric_tmp1_pk_seq / ;

	      my @indexes = qw/ idx_project_metric_group
		  idx_project_metric_weekly_group
		  user_metric_history_date_userid / ;

	      foreach my $table (@tables) {
		  &drop_table_if_exists ($dbh, $table) ;
	      }

	      foreach my $sequence (@sequences) {
		  &drop_sequence_if_exists ($dbh, $sequence) ;
	      }

	      foreach my $index (@indexes) {
		  &drop_index_if_exists ($dbh, $index) ;
	      }

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5.9999.2+data+upgraded" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Upgrading your database scheme from 2.5") ;

	      my $pg_version = &get_pg_version ;

	      if (&is_lesser ($pg_version, "7.3")) {
		  @reqlist = (
			      "DROP INDEX groups_pkey",
			      "DROP INDEX users_pkey",
			      ) ;
	      } else {
		  @reqlist = (
			      "ALTER TABLE groups DROP CONSTRAINT groups_pkey",
			      "ALTER TABLE users DROP CONSTRAINT users_pkey",
			      ) ;
	      }
	      foreach my $s (@reqlist) {
		  $query = $s ;
		  # debug $query ;
		  $sth = $dbh->prepare ($query) ;
		  $sth->execute () ;
		  $sth->finish () ;
	      }

	      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/sf2.5-to-sf2.6.sql") } ;
	      foreach my $s (@reqlist) {
		  $query = $s ;
		  # debug $query ;
		  $sth = $dbh->prepare ($query) ;
		  $sth->execute () ;
		  $sth->finish () ;
	      }
	      @reqlist = () ;

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5.9999.3+artifact+transcoded" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Transcoding the artifact data fields") ;

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

	      @reqlist = () ;
	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5.9999.4+groups+inserted" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Inserting missing groups") ;

	      @reqlist = (
			  "INSERT INTO groups (group_name, homepage,
                           is_public, status, unix_group_name,
                           unix_box, http_domain, short_description,
                           cvs_box, license, register_purpose,
                           license_other, register_time, rand_hash,
                           use_mail, use_survey, use_forum, use_pm,
                           use_cvs, use_news, type, use_docman,
                           new_task_address, send_all_tasks,
                           use_pm_depend_box)
       	                   VALUES ('Stats', '$domain_name/top/', 0,
       	    	           'A', 'stats', 'shell', NULL, NULL, 'cvs',
       	    	           'website', NULL, NULL, 0, NULL, 1, 0, 0, 0, 0,
       	    	           1, 1, 1, '', 0, 0)",
			  "INSERT INTO groups (group_name, homepage,
                           is_public, status, unix_group_name,
                           unix_box, http_domain, short_description,
                           cvs_box, license, register_purpose,
                           license_other, register_time, rand_hash,
                           use_mail, use_survey, use_forum, use_pm,
                           use_cvs, use_news, type, use_docman,
                           new_task_address, send_all_tasks,
                           use_pm_depend_box)
                           VALUES ('Peer Ratings', '$domain_name/people/', 0,
                           'A', 'peerrating', 'shell', NULL, NULL, 'cvs1',
                           'website', NULL, NULL, 0, NULL, 1, 0, 0, 0, 0,
                           1, 1, 0, '', 0, 0)"
			  ) ;

	      foreach my $s (@reqlist) {
		  $query = $s ;
		  # debug $query ;
		  $sth = $dbh->prepare ($query) ;
		  $sth->execute () ;
		  $sth->finish () ;
	      }
	      @reqlist = () ;
	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.6-0+checkpoint+1" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Database has successfully been converted.") ;
	      $query = "DELETE FROM debian_meta_data WHERE key = 'current-path'" ;
	      # debug $query ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;

	      &update_db_version ($target) ;
	      &debug ("Committing.") ;
	      $dbh->commit () ;
	  }

	  last PATH_SWITCH ;
      } ;
  } # PATH_SWITCH

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+2" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Updating permissions on system groups.") ;
	$query = "UPDATE groups SET group_name='Site Admin', is_public=1 WHERE group_id=1" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
	$query = "UPDATE groups SET group_name='Site News Admin', is_public=1 WHERE group_id=$sys_news_group" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+3" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Creating table group_cvs_history.") ;
	$query = "CREATE TABLE group_cvs_history (
            id integer DEFAULT nextval('group_cvs_history_pk_seq'::text) NOT NULL,
            group_id integer DEFAULT '0' NOT NULL,
            user_name character varying(80) DEFAULT '' NOT NULL,
            cvs_commits integer DEFAULT '0' NOT NULL,
            cvs_commits_wk integer DEFAULT '0' NOT NULL,
            cvs_adds integer DEFAULT '0' NOT NULL,
            cvs_adds_wk integer DEFAULT '0' NOT NULL,
            PRIMARY KEY (id))";
    	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+4" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Registering Savannah themes.") ;

	$query = "SELECT max(theme_id) FROM themes" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;
	my $maxid = $array [0] ;

	&bump_sequence_to ($dbh, "themes_pk_seq", $maxid) ;

	@reqlist = (
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_codex', 'Savannah CodeX')",
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_forest', 'Savannah Forest')",
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_reverse', 'Savannah Reverse')",
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_sad', 'Savannah Sad')",
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_savannah', 'Savannah Original')",
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_slashd', 'Savannah SlashDot')",
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_startrek', 'Savannah StarTrek')",
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_transparent', 'Savannah Transparent')",
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_water', 'Savannah Water')",
		    "INSERT INTO themes (dirname, fullname) VALUES ('savannah_www.gnu.org', 'Savannah www.gnu.org')"
		    ) ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+5" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Registering yet another Savannah theme.") ;

	$query = "INSERT INTO themes (dirname, fullname) VALUES ('savannah_darkslate', 'Savannah Dark Slate')";
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+6" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Updating language codes.") ;

	@reqlist = (
		    "UPDATE supported_languages SET language_code='en' where classname='English'",
		    "UPDATE supported_languages SET language_code='ja' where classname='Japanese'",
		    "UPDATE supported_languages SET language_code='iw' where classname='Hebrew'",
		    "UPDATE supported_languages SET language_code='es' where classname='Spanish'",
		    "UPDATE supported_languages SET language_code='th' where classname='Thai'",
		    "UPDATE supported_languages SET language_code='de' where classname='German'",
		    "UPDATE supported_languages SET language_code='it' where classname='Italian'",
		    "UPDATE supported_languages SET language_code='no' where classname='Norwegian'",
		    "UPDATE supported_languages SET language_code='sv' where classname='Swedish'",
		    "UPDATE supported_languages SET language_code='zh' where classname='Chinese'",
		    "UPDATE supported_languages SET language_code='nl' where classname='Dutch'",
		    "UPDATE supported_languages SET language_code='eo' where classname='Esperanto'",
		    "UPDATE supported_languages SET language_code='ca' where classname='Catalan'",
		    "UPDATE supported_languages SET language_code='ko' where classname='Korean'",
		    "UPDATE supported_languages SET language_code='bg' where classname='Bulgarian'",
		    "UPDATE supported_languages SET language_code='el' where classname='Greek'",
		    "UPDATE supported_languages SET language_code='id' where classname='Indonesian'",
		    "UPDATE supported_languages SET language_code='pt' where classname='Portuguese (Brazillian)'",
		    "UPDATE supported_languages SET language_code='pl' where classname='Polish'",
		    "UPDATE supported_languages SET language_code='pt' where classname='Portuguese'",
		    "UPDATE supported_languages SET language_code='ru' where classname='Russian'",
		    "UPDATE supported_languages SET language_code='fr' where classname='French'"
		    ) ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;
	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+7" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Fixing artifact-related views.") ;

	&drop_view_if_exists ($dbh, "artifact_file_user_vw") ;
	&drop_view_if_exists ($dbh, "artifact_history_user_vw") ;
	&drop_view_if_exists ($dbh, "artifact_message_user_vw") ;
	&drop_view_if_exists ($dbh, "artifactperm_artgrouplist_vw") ;
	&drop_view_if_exists ($dbh, "artifactperm_user_vw") ;
	&drop_view_if_exists ($dbh, "artifact_vw") ;

	@reqlist = (
		    "CREATE VIEW artifact_file_user_vw as SELECT af.id, af.artifact_id, af.description, af.bin_data, af.filename, af.filesize, af.filetype, af.adddate, af.submitted_by, users.user_name, users.realname FROM artifact_file af, users WHERE (af.submitted_by = users.user_id)",
		    "CREATE VIEW artifact_history_user_vw as SELECT ah.id, ah.artifact_id, ah.field_name, ah.old_value, ah.entrydate, users.user_name FROM artifact_history ah, users WHERE (ah.mod_by = users.user_id)",
		    "CREATE VIEW artifact_message_user_vw as SELECT am.id, am.artifact_id, am.from_email, am.body, am.adddate, users.user_id, users.email, users.user_name, users.realname FROM artifact_message am, users WHERE (am.submitted_by = users.user_id)",
		    "CREATE VIEW artifactperm_artgrouplist_vw as SELECT agl.group_artifact_id, agl.name, agl.description, agl.group_id, ap.user_id, ap.perm_level FROM artifact_perm ap, artifact_group_list agl WHERE (ap.group_artifact_id = agl.group_artifact_id)",
		    "CREATE VIEW artifactperm_user_vw as SELECT ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level, users.user_name, users.realname FROM artifact_perm ap, users WHERE (users.user_id = ap.user_id)",
		    "CREATE VIEW artifact_vw as SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.status_id, artifact.category_id, artifact.artifact_group_id, artifact.resolution_id, artifact.priority, artifact.submitted_by, artifact.assigned_to, artifact.open_date, artifact.close_date, artifact.summary, artifact.details, u.user_name AS assigned_unixname, u.realname AS assigned_realname, u.email AS assigned_email, u2.user_name AS submitted_unixname, u2.realname AS submitted_realname, u2.email AS submitted_email, artifact_status.status_name, artifact_category.category_name, artifact_group.group_name, artifact_resolution.resolution_name FROM users u, users u2, artifact, artifact_status, artifact_category, artifact_group, artifact_resolution WHERE ((((((artifact.assigned_to = u.user_id) AND (artifact.submitted_by = u2.user_id)) AND (artifact.status_id = artifact_status.id)) AND (artifact.category_id = artifact_category.id)) AND (artifact.artifact_group_id = artifact_group.id)) AND (artifact.resolution_id = artifact_resolution.id))"
		    ) ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;
	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+8" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Adding integrity constraints between the Trove map tables.") ;

	@reqlist = (
		    "ALTER TABLE trove_group_link ADD CONSTRAINT tgl_group_id_fk FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL",
		    "ALTER TABLE trove_group_link ADD CONSTRAINT tgl_cat_id_fk FOREIGN KEY (trove_cat_id) REFERENCES trove_cat(trove_cat_id) MATCH FULL",
		    "ALTER TABLE trove_agg ADD CONSTRAINT trove_agg_cat_id_fk FOREIGN KEY (trove_cat_id) REFERENCES trove_cat(trove_cat_id) MATCH FULL",
		    "ALTER TABLE trove_agg ADD CONSTRAINT trove_agg_group_id_fk FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL",
		    "DELETE FROM trove_treesums WHERE trove_cat_id NOT IN (SELECT trove_cat_id FROM trove_cat)",
		    "ALTER TABLE trove_treesums ADD CONSTRAINT trove_treesums_cat_id_fk FOREIGN KEY (trove_cat_id) REFERENCES trove_cat(trove_cat_id) MATCH FULL",
		    ) ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;
	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+9" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Adding extra fields to the groups table.") ;

	@reqlist = (
		    "ALTER TABLE groups ADD COLUMN use_ftp integer",
		    "ALTER TABLE groups ALTER COLUMN use_ftp SET DEFAULT 1",
		    "UPDATE groups SET use_ftp = 1",
		    "ALTER TABLE groups ADD COLUMN use_tracker integer",
		    "ALTER TABLE groups ALTER COLUMN use_tracker SET DEFAULT 1",
		    "UPDATE groups SET use_tracker = 1",
		    "ALTER TABLE groups ADD COLUMN use_frs integer",
		    "ALTER TABLE groups ALTER COLUMN use_frs SET DEFAULT 1",
		    "UPDATE groups SET use_frs = 1",
		    "ALTER TABLE groups ADD COLUMN use_stats integer",
		    "ALTER TABLE groups ALTER COLUMN use_stats SET DEFAULT 1",
		    "UPDATE groups SET use_stats = 1",
		    "ALTER TABLE groups ADD COLUMN enable_pserver integer",
		    "ALTER TABLE groups ALTER COLUMN enable_pserver SET DEFAULT 1",
		    "UPDATE groups SET enable_pserver = 1",
		    "ALTER TABLE groups ADD COLUMN enable_anoncvs integer",
		    "ALTER TABLE groups ALTER COLUMN enable_anoncvs SET DEFAULT 1",
		    "UPDATE groups SET enable_anoncvs = 1",
		    ) ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;
	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+10" ;
    if (&is_lesser ($version, $target)) {
 	&debug ("Updating supported_languages table.") ;
	
	my $pg_version = &get_pg_version ;

	if (&is_lesser ($pg_version, "7.3")) {
	    @reqlist = (
			"ALTER TABLE supported_languages RENAME TO supported_languages_old",
			"CREATE TABLE supported_languages (language_id integer DEFAULT nextval('supported_languages_pk_seq'::text) NOT NULL, name text, filename text, classname text, language_code character(5))",
			"INSERT INTO supported_languages SELECT * FROM supported_languages_old",
			"DROP TABLE supported_languages_old",
			"ALTER TABLE supported_languages ADD CONSTRAINT supported_languages_pkey PRIMARY KEY (language_id)",
			"ALTER TABLE users ADD CONSTRAINT users_languageid_fk FOREIGN KEY (language) REFERENCES supported_languages(language_id) MATCH FULL",
			"ALTER TABLE doc_data ADD CONSTRAINT docdata_languageid_fk FOREIGN KEY (language_id) REFERENCES supported_languages(language_id) MATCH FULL",
			"UPDATE supported_languages SET language_code='pt_BR', classname='PortugueseBrazilian', name='Pt. Brazilian', filename='PortugueseBrazilian.class' where classname='PortugueseBrazillian'",
			) ;
	} else {
	    @reqlist = (
			"ALTER TABLE supported_languages RENAME COLUMN language_code TO language_code_old",
			"ALTER TABLE supported_languages ADD COLUMN language_code character(5)",
			"UPDATE supported_languages SET language_code = language_code_old",
			"ALTER TABLE supported_languages DROP COLUMN language_code_old",
			"UPDATE supported_languages SET language_code='pt_BR', classname='PortugueseBrazilian', name='Pt. Brazilian', filename='PortugueseBrazilian.class' where classname='PortugueseBrazillian'",
			) ;
	}
 	foreach my $s (@reqlist) {
 	    $query = $s ;
 	    # debug $query ;
 	    $sth = $dbh->prepare ($query) ;
 	    $sth->execute () ;
 	    $sth->finish () ;
 	}
 	@reqlist = () ;
 	&update_db_version ($target) ;
 	&debug ("Committing.") ;
 	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+11" ;
    if (&is_lesser ($version, $target)) {
 	&debug ("Adding tables for the plugin subsystem.") ;

 	@reqlist = (
		    "CREATE SEQUENCE plugins_pk_seq",
		    "CREATE TABLE plugins (plugin_id integer DEFAULT nextval('plugins_pk_seq'::text) NOT NULL, plugin_name varchar(32) UNIQUE NOT NULL, plugin_desc text, CONSTRAINT plugins_pkey PRIMARY KEY (plugin_id))",
		    "CREATE SEQUENCE group_plugin_pk_seq",
		    "CREATE TABLE group_plugin (group_plugin_id integer DEFAULT nextval('group_plugin_pk_seq'::text) NOT NULL, group_id integer, plugin_id integer, CONSTRAINT group_plugin_pkey PRIMARY KEY (group_plugin_id), CONSTRAINT group_plugin_group_id_fk FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL, CONSTRAINT group_plugin_plugin_id_fk FOREIGN KEY (plugin_id) REFERENCES plugins(plugin_id) MATCH FULL)",
		    "CREATE SEQUENCE user_plugin_pk_seq",
		    "CREATE TABLE user_plugin (user_plugin_id integer DEFAULT nextval('user_plugin_pk_seq'::text) NOT NULL, user_id integer, plugin_id integer, CONSTRAINT user_plugin_pkey PRIMARY KEY (user_plugin_id), CONSTRAINT user_plugin_user_id_fk FOREIGN KEY (user_id) REFERENCES users(user_id) MATCH FULL, CONSTRAINT user_plugin_plugin_id_fk FOREIGN KEY (plugin_id) REFERENCES plugins(plugin_id) MATCH FULL)",
 		    ) ;
 	foreach my $s (@reqlist) {
 	    $query = $s ;
 	    # debug $query ;
 	    $sth = $dbh->prepare ($query) ;
 	    $sth->execute () ;
 	    $sth->finish () ;
 	}
 	@reqlist = () ;
 	&update_db_version ($target) ;
 	&debug ("Committing.") ;
 	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+12" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20021125.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20021125.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+13" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20021212.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20021212.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+14" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20021213.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20021213.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+15" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Transcoding documentation data fields") ;
      $query = "SELECT docid,data FROM doc_data ORDER BY docid ASC" ;
      # debug $query ;
      $sth = $dbh->prepare ($query) ;
      $sth->execute () ;
      while (@array = $sth->fetchrow_array) {
	  my $query2 = "UPDATE doc_data SET data='" ;
	  $query2 .= encode_base64 (decode_entities ($array [1])) ;
	  $query2 .= "', filename='file".$array [0].".html'";
	  $query2 .= ", filetype='text/html'"; 
	  $query2 .= " WHERE docid=" ;
	  $query2 .= $array [0] ;
	  $query2 .= "" ;
	  # debug $query2 ;
	  my $sth2 =$dbh->prepare ($query2) ;
	  $sth2->execute () ;
	  $sth2->finish () ;
      }
      $sth->finish () ;

      @reqlist = () ;
      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+16" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20021214.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20021214.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+17" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20021215.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20021215.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+18" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20021216.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20021216.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+19" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20021223.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20021223.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+20" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20030102.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030102.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+21" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20030105.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030105.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+22" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20030107.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030107.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+23" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20030109.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030109.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+24" ;
    if (&is_lesser ($version, $target)) {

      &debug ("Adjusting language sequences") ;

	$query = "SELECT max(language_id) FROM supported_languages" ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;
	my $maxid = $array [0] ;
	&bump_sequence_to ($dbh, "supported_languages_pk_seq", $maxid) ;

      &debug ("Upgrading with 20030112.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030112.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+25" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20030113.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030113.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+26" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20030131.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030131.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+27" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20030209.sql") ;

      @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030209.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+28" ;
    if (&is_lesser ($version, $target)) {
      &debug ("Upgrading with 20030312.sql") ;

      my $pg_version = &get_pg_version ;
      
      if (&is_lesser ($pg_version, "7.3")) {
	  @reqlist = (
		      "DROP TRIGGER projtask_insert_depend_trig ON project_task",
		      "DROP FUNCTION projtask_insert_depend ()",
		      "CREATE OR REPLACE FUNCTION projtask_insert_depend () RETURNS OPAQUE AS '
DECLARE
	dependon RECORD;
	delta INTEGER;
BEGIN
	IF NEW.start_date > NEW.end_date THEN
		RAISE EXCEPTION ''START DATE CANNOT BE AFTER END DATE'';
	END IF;
	FOR dependon IN SELECT * FROM project_dependon_vw
				WHERE project_task_id=NEW.project_task_id LOOP
		IF dependon.end_date > NEW.start_date THEN
			delta := dependon.end_date-NEW.start_date;
			RAISE NOTICE ''Bumping Back: % Delta: % '',NEW.project_task_id,delta;
			NEW.start_date := NEW.start_date+delta;
			NEW.end_date := NEW.end_date+delta;
		END IF;
	END LOOP;
	RETURN NEW;
END;
' LANGUAGE 'plpgsql'",
"CREATE TRIGGER projtask_insert_depend_trig BEFORE INSERT OR UPDATE ON project_task
	FOR EACH ROW EXECUTE PROCEDURE projtask_insert_depend()",
		      ) ;
      } else {
	  @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030312.sql") } ;
      }
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("Committing $target.") ;
      $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+29" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Registering KDE theme.") ;

	$query = "INSERT INTO themes (dirname, fullname) VALUES ('kde', 'KDE')";
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }


    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+30" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Registering Dark Aqua theme.") ;

	$query = "INSERT INTO themes (dirname, fullname) VALUES ('darkaqua', 'Dark Aqua')";
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }


    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+31" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Upgrading with 20030513.sql") ;

	@reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030513.sql") } ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }


    $version = &get_db_version ;
    $target = "3.0-1" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Database schema is now version 3.0-1.") ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }


    $version = &get_db_version ;
    $target = "3.0-7" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Upgrading with 20030822.sql") ;

	@reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20030822.sql") } ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.1-0+1" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Upgrading with 20031105.sql") ;

	@reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20031105.sql") } ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}

	&debug ("Upgrading with 20031124.sql") ;

	@reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20031124.sql") } ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.1-0+2" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Upgrading with 20031129.sql") ;

	@reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20031129.sql") } ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.1-0+3" ;
    if (&is_lesser ($version, $target)) {
	# Yes, I know.  20031126 < 20031129, yet we apply that change later.
	# Blame tperdue for late committing.
	# They are independent anyway.
	&debug ("Upgrading with 20031126.sql") ; 

	@reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20031126.sql") } ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.2.1-0+2" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Upgrading with 20031205.sql") ; 

	@reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20031205.sql") } ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }
    
    $version = &get_db_version ;
    $target = "3.2.1-0+3" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Upgrading with 20040130.sql") ; 

	@reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040130.sql") } ;
	foreach my $s (@reqlist) {
	    $query = $s ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;

	&update_db_version ($target) ;
	&debug ("Committing.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.2.1-0+4" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20040204.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040204.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.2.1-0+5" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20040315.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040315.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-0+0" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20040325.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040325.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-0+1" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 200403252.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/200403252.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-0+3" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20040507.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040507.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-0+4" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20040722.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040722.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-0+6" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20040804.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040804.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-0+7" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20040826.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040826.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-2+1" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with migrateforum.php") ;
	system("/usr/lib/gforge/db/20040826_migrateforum.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include") == 0 
	or die "system call of 20040826_migrateforum.php failed: $?" ;
        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-2+2" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with migraterbac.php") ;
	system("/usr/lib/gforge/db/20040826_migraterbac.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include") == 0
	or die "system call of 20040826_migraterbac.php failed: $?" ;
        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-2+4" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20040914.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040914.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-2+4+1" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041001.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20041001.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-2+5" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041005.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20041005.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-2+6" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041006.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20041006.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-3" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041014.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20041014.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-4" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041020.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20041020.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.0.0-0" ;
    # This is an exception, I reapply a modified version of 20040729.sql since it was doing nothing
    # the other call was deleted from this file
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20040729.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20040729.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.0.0-0+1" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Granting read access permissions to NSS") ;

        @reqlist = ( "GRANT SELECT ON nss_passwd TO gforge_nss",
		     "GRANT SELECT ON nss_groups TO gforge_nss",
		     "GRANT SELECT ON nss_usergroups TO gforge_nss",
		    ) ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.0.0-0+2" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041031.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20041031.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &debug ("Granting read access permissions to NSS") ;

        @reqlist = ( "GRANT SELECT ON mta_users TO gforge_mta",
		     "GRANT SELECT ON mta_lists TO gforge_mta",
		    ) ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.0.0-0+3" ;
    # This is an exception, I reapply a modified version of 20040729.sql since it was doing nothing
    # the other call was deleted from this file
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041104.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20041104.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.0.0-0+4" ;
    # This is an exception, I reapply a modified version of 20040729.sql since it was doing nothing
    # the other call was deleted from this file
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041108.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20041108.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.0.2-0+0" ;
    # This is an exception, I reapply a modified version of 20040729.sql since it was doing nothing
    # the other call was deleted from this file
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041124.sql") ;

        @reqlist = @{ &parse_sql_file ("/usr/lib/gforge/db/20041124.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("Committing.") ;
        $dbh->commit () ;
    }

    &debug ("It seems your database $action went well and smoothly.  That's cool.") ;
    &debug ("Please enjoy using Gforge.") ;

    # There should be a commit at the end of every block above.
    # If there is not, then it might be symptomatic of a problem.
    # For safety, we roll back.
    $dbh->rollback ();
};

if ($@) {
    warn "Transaction aborted because $@" ;
    &debug ("Transaction aborted because $@") ;
    &debug ("Last SQL query was:\n$query\n(end of query)") ;
    $dbh->rollback ;
    my $version = &get_db_version ; 
    if ($version) {
	&debug ("Your database schema is at version $version") ;
    } else {
	&debug ("Couldn't get your database schema version.") ;
    }
    &debug ("Please report this bug on the Debian bug-tracking system.") ;
    &debug ("Please include the previous messages as well to help debugging.") ;
    &debug ("You should not worry too much about this,") ;
    &debug ("your DB is still in a consistent state and should be usable.") ;
    exit 1 ;
}

$dbh->rollback ;
$dbh->disconnect ;

sub get_pg_version () {
    my $command = q(dpkg -s postgresql | awk '/^Version: / { print $2 }') ;
    my $version = qx($command) ;
    chomp $version ;
    return $version ;
}

sub create_metadata_table ( $ ) {
    my $v = shift || "2.5-7+just+before+8" ;
    # Do we have the metadata table?

    $query = "SELECT count(*) FROM pg_class WHERE relname = 'debian_meta_data' and relkind = 'r'";
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    # Let's create this table if we have it not

    if ($array [0] == 0) {
	&debug ("Creating debian_meta_data table.") ;
	$query = "CREATE TABLE debian_meta_data (key varchar primary key, value text not null)" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }

    $query = "SELECT count(*) FROM debian_meta_data WHERE key = 'db-version'";
    # debug $query ;
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    # Empty table?  We'll have to fill it up a bit

    if ($array [0] == 0) {
	&debug ("Inserting first data into debian_meta_data table.") ;
	$query = "INSERT INTO debian_meta_data (key, value) VALUES ('db-version', '$v')" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;
    }
}

sub update_db_version ( $ ) {
    my $v = shift or die "Not enough arguments" ;

    &debug ("Updating debian_meta_data table.") ;
    $query = "UPDATE debian_meta_data SET value = '$v' WHERE key = 'db-version'" ;
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    $sth->finish () ;
}

sub get_db_version () {
    $query = "SELECT value FROM debian_meta_data WHERE key = 'db-version'" ;
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    my @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    my $version = $array [0] ;

    return $version ;
}
