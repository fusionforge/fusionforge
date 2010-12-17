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
use Digest::MD5 ;

use vars qw/$dbh @reqlist $query/ ;
use vars qw/$sys_default_domain $sys_scm_host $sys_download_host
    $sys_shell_host $sys_users_host $sys_docs_host $sys_lists_host
    $sys_dns1_host $sys_dns2_host $FTPINCOMING_DIR $FTPFILES_DIR
    $sys_urlroot $sf_cache_dir $sys_name $sys_themeroot
    $sys_news_group $sys_dbhost $sys_dbname $sys_dbuser $sys_dbpasswd
    $sys_ldap_base_dn $sys_ldap_host $admin_password
    $server_admin $domain_name $newsadmin_groupid $statsadmin_groupid
    $libdir $sqldir/ ;

require ("/etc/gforge/local.pl") ; 
$libdir="/usr/share/gforge/lib";
$sqldir="/usr/share/gforge/db";
require ("$libdir/sqlparser.pm") ; # Our magic SQL parser
require ("$libdir/sqlhelper.pm") ; # Our SQL functions
require ("$libdir/include.pl");  # Some other functions

&db_connect ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my ($sth, @array, $version, $path, $target) ;

    # Do we have at least the basic schema?
    # Create Sourceforge database
    if (! &table_exists ($dbh, 'groups')) {	# No 'groups' table
	# Installing SF 2.6 from scratch
	&debug ("Creating initial Sourceforge database from files.") ;

	&create_metadata_table ("2.5.9999") ;

	$query = "SELECT count(*) from debian_meta_data where key = 'current-path'";
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;
	if ($array[0] == 0) {
	    $query = "INSERT INTO debian_meta_data (key, value) VALUES ('current-path', 'scratch-to-2.6')" ;
	    # debug $query ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	&debug ("...OK.") ;
	$dbh->commit () ;

    } else {			# A 'groups' table exists
	if (! &table_exists ($dbh, 'debian_meta_data')) {	# No 'debian_meta_data' table
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
		$query = "INSERT INTO debian_meta_data (key, value) VALUES ('current-path', '2.5-to-2.6')" ;
		$sth = $dbh->prepare ($query) ;
		$sth->execute () ;
		$sth->finish () ;
		$dbh->commit () ;
	    }
	}
    }

    $query = "SELECT count(*) from debian_meta_data where key = 'current-path'";
    $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    @array = $sth->fetchrow_array () ;
    $sth->finish () ;

    if ($array[0] == 0) {
	$path = "" ;
    } else {
	$query = "SELECT value from debian_meta_data where key = 'current-path'";
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	@array = $sth->fetchrow_array () ;
	$sth->finish () ;

	$path = $array[0] ;
    }

  PATH_SWITCH: {
      ($path eq 'scratch-to-2.6') && do {
	  &update_with_sql ("sf-2.6-complete", "2.5.9999.1+global+data+done") ;

	  $version = &get_db_version ;
	  $target = "2.5.9999.2+local+data+done" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Adding local data.") ;

	      do "/etc/gforge/local.pl" or die "Cannot read /etc/gforge/local.pl" ;

	      my ($login, $md5pwd, $unixpwd, $email, $noreplymail, $date) ;

	      $login = 'admin' ;
	      $md5pwd = 'INVALID' ;
	      $unixpwd = 'INVALID' ;
	      $email = $server_admin ;
	      $noreplymail="noreply\@$domain_name" ;
	      $date = time () ;

	      @reqlist = (
			  "UPDATE groups SET homepage = '$domain_name/admin/' where group_id = 1",
			  "UPDATE groups SET homepage = '$domain_name/news/' where group_id = 2",
			  "UPDATE groups SET homepage = '$domain_name/stats/' where group_id = 3",
			  "UPDATE groups SET homepage = '$domain_name/peerrating/' where group_id = 4",
			  "UPDATE users SET email = '$noreplymail' where user_id = 100",
			  "INSERT INTO users VALUES (101,'$login','$email','$md5pwd','Sourceforge admin','A','/bin/bash','$unixpwd','N',2000,'shell',$date,'',1,0,NULL,NULL,0,'','GMT', 1, 0)", 
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
	      &debug ("...OK.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5.9999.3+skills+done" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Inserting skills.") ;

	      foreach my $skill (split m/;/, "Ada;C;C++;HTML;LISP;Perl;PHP;Python;SQL") {
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
	      &debug ("...OK.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.6-0+checkpoint+1" ;
	  if (&is_lesser ($version, $target)) {
	      $query = "DELETE FROM debian_meta_data WHERE key = 'current-path'" ;
	      # debug $query ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;

	      &update_db_version ($target) ;
	      &debug ("...OK.") ;
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
	      &debug ("...OK.") ;
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
	      &debug ("...OK.") ;
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
	      &debug ("...OK.") ;
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
 	      &debug ("...OK.") ;
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
	      &debug ("...OK.") ;
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
	      &debug ("...OK.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.5.9999.2+data+upgraded" ;
	  if (&is_lesser ($version, $target)) {
	      &debug ("Upgrading your database scheme from 2.5") ;

	      @reqlist = (
		  "ALTER TABLE groups DROP CONSTRAINT groups_pkey",
		  "ALTER TABLE users DROP CONSTRAINT users_pkey",
		  ) ;
	      foreach my $s (@reqlist) {
		  $query = $s ;
		  # debug $query ;
		  $sth = $dbh->prepare ($query) ;
		  $sth->execute () ;
		  $sth->finish () ;
	      }

	      @reqlist = @{ &parse_sql_file ("$sqldir/sf2.5-to-sf2.6.sql") } ;
	      foreach my $s (@reqlist) {
		  $query = $s ;
		  # debug $query ;
		  $sth = $dbh->prepare ($query) ;
		  $sth->execute () ;
		  $sth->finish () ;
	      }
	      @reqlist = () ;

	      &update_db_version ($target) ;
	      &debug ("...OK.") ;
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
	      &debug ("...OK.") ;
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
	      &debug ("...OK.") ;
	      $dbh->commit () ;
	  }

	  $version = &get_db_version ;
	  $target = "2.6-0+checkpoint+1" ;
	  if (&is_lesser ($version, $target)) {
	      $query = "DELETE FROM debian_meta_data WHERE key = 'current-path'" ;
	      # debug $query ;
	      $sth = $dbh->prepare ($query) ;
	      $sth->execute () ;
	      $sth->finish () ;

	      &update_db_version ($target) ;
	      &debug ("...OK.") ;
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
	&debug ("...OK.") ;
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
	&debug ("...OK.") ;
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
	&debug ("...OK.") ;
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
	&debug ("...OK.") ;
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
	&debug ("...OK.") ;
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
	&debug ("...OK.") ;
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
	&debug ("...OK.") ;
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
	&debug ("...OK.") ;
	$dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "2.6-0+checkpoint+10" ;
    if (&is_lesser ($version, $target)) {
 	&debug ("Updating supported_languages table.") ;
	
	@reqlist = (
	    "ALTER TABLE supported_languages RENAME COLUMN language_code TO language_code_old",
	    "ALTER TABLE supported_languages ADD COLUMN language_code character(5)",
	    "UPDATE supported_languages SET language_code = language_code_old",
	    "ALTER TABLE supported_languages DROP COLUMN language_code_old",
	    "UPDATE supported_languages SET language_code='pt_BR', classname='PortugueseBrazilian', name='Pt. Brazilian', filename='PortugueseBrazilian.class' where classname='PortugueseBrazillian'",
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
 	&debug ("...OK.") ;
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
 	&debug ("...OK.") ;
 	$dbh->commit () ;
    }

    &update_with_sql("20021125", "2.6-0+checkpoint+12") ;
    &update_with_sql("20021212", "2.6-0+checkpoint+13") ;
    &update_with_sql("20021213-1", "2.6-0+checkpoint+14") ;

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
      &debug ("...OK.") ;
      $dbh->commit () ;
    }

    &update_with_sql("20021214", "2.6-0+checkpoint+16") ;
    &update_with_sql("20021215", "2.6-0+checkpoint+17") ;
    &update_with_sql("20021216", "2.6-0+checkpoint+18") ;
    &update_with_sql("20021223-2", "2.6-0+checkpoint+19") ;
    &update_with_sql("20030102-2", "2.6-0+checkpoint+20") ;
    &update_with_sql("20030105", "2.6-0+checkpoint+21") ;
    &update_with_sql("20030107", "2.6-0+checkpoint+22") ;
    &update_with_sql("20030109", "2.6-0+checkpoint+23") ;

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

      @reqlist = @{ &parse_sql_file ("$sqldir/20030112.sql") } ;
      foreach my $s (@reqlist) {
	  $query = $s ;
	  # debug $query ;
	  $sth = $dbh->prepare ($query) ;
	  $sth->execute () ;
	  $sth->finish () ;
      }
      @reqlist = () ;

      &update_db_version ($target) ;
      &debug ("...OK.") ;
      $dbh->commit () ;
    }

    &update_with_sql("20030113-2", "2.6-0+checkpoint+25") ;
    &update_with_sql("20030131", "2.6-0+checkpoint+26") ;
    &update_with_sql("20030209", "2.6-0+checkpoint+27") ;
    &update_with_sql("20030312", "2.6-0+checkpoint+28") ;

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
	&debug ("...OK.") ;
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
	&debug ("...OK.") ;
	$dbh->commit () ;
    }


    &update_with_sql("20030513", "2.6-0+checkpoint+31") ;

    $version = &get_db_version ;
    $target = "3.0-1" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Database schema is now version 3.0-1.") ;

	&update_db_version ($target) ;
	&debug ("...OK.") ;
	$dbh->commit () ;
    }

    &update_with_sql("20030822", "3.0-7") ;
    &update_with_sql("20031105", "3.1-0+1") ;
    &update_with_sql("20031124", "3.1-0+1.1") ;
    &update_with_sql("20031129", "3.1-0+2") ;
    &update_with_sql("20031126", "3.1-0+3") ;
    &update_with_sql("20031205", "3.2.1-0+2") ;
    &update_with_sql("20040130", "3.2.1-0+3") ;
    &update_with_sql("20040204", "3.2.1-0+4") ;
    &update_with_sql("20040315", "3.2.1-0+5") ;
    &update_with_sql("200403251", "3.3.0-0+0") ;
    &update_with_sql("200403252", "3.3.0-0+1") ;
    &update_with_sql("20040507", "3.3.0-0+3") ;
    &update_with_sql("20040722", "3.3.0-0+4") ;
    &update_with_sql("20040804", "3.3.0-0+6") ;
    &update_with_sql("20040826", "3.3.0-0+7") ;

    $version = &get_db_version ;
    $target = "3.3.0-2+1" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Migrating forum names") ;
	
	$query = "SELECT group_forum_id,forum_name FROM forum_group_list" ;
	# &debug ($query) ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	while (@array = $sth->fetchrow_array) {
	    my $forumid = $array[0] ;
	    my $oldname = $array[1] ;
	    
	    my $newname = lc $oldname ;
	    $newname =~ s/[^_.0-9a-z-]/-/g ;
	    
	    my $query2 = "UPDATE forum_group_list SET forum_name='$newname' WHERE group_forum_id=$forumid" ;
	    # &debug ($query2) ;
	    my $sth2 =$dbh->prepare ($query2) ;
	    $sth2->execute () ;
	    $sth2->finish () ;
	}
	$sth->finish () ;
	
        &update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "3.3.0-2+2" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Migrating permissions to RBAC") ;
	
	my $defaultroles = {
	    'Admin'	       => { 'projectadmin'=>'A', 'frs'=>'1', 'scm'=>'1', 'docman'=>'1', 'forumadmin'=>'2', 'forum'=>'2', 'trackeradmin'=>'2', 'tracker'=>'2', 'pmadmin'=>'2', 'pm'=>'2' },
	    'Senior Developer' => { 'projectadmin'=>'0', 'frs'=>'1', 'scm'=>'1', 'docman'=>'1', 'forumadmin'=>'2', 'forum'=>'2', 'trackeradmin'=>'2', 'tracker'=>'2', 'pmadmin'=>'2', 'pm'=>'2' },
	    'Junior Developer' => { 'projectadmin'=>'0', 'frs'=>'0', 'scm'=>'1', 'docman'=>'0', 'forumadmin'=>'0', 'forum'=>'1', 'trackeradmin'=>'0', 'tracker'=>'1', 'pmadmin'=>'0', 'pm'=>'1' },
	    'Doc Writer'       => { 'projectadmin'=>'0', 'frs'=>'0', 'scm'=>'0', 'docman'=>'1', 'forumadmin'=>'0', 'forum'=>'1', 'trackeradmin'=>'0', 'tracker'=>'0', 'pmadmin'=>'0', 'pm'=>'0' },
	    'Support Tech'     => { 'projectadmin'=>'0', 'frs'=>'0', 'scm'=>'0', 'docman'=>'1', 'forumadmin'=>'0', 'forum'=>'1', 'trackeradmin'=>'0', 'tracker'=>'2', 'pmadmin'=>'0', 'pm'=>'0' }
	} ;
	
	$query = "SELECT group_id FROM groups where status != 'P'" ;
	# &debug ($query) ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	while (@array = $sth->fetchrow_array) {
	    my $group_id = $array[0] ;

	    my ($query2, $sth2, @array2, $admin_rid, $jd_rid, %roledata) ;
	    foreach my $rname (keys %$defaultroles) {
		$query2 = "SELECT nextval('role_role_id_seq'::text)" ;
		# &debug ($query2) ;
		$sth2 =$dbh->prepare ($query2) ;
		$sth2->execute () ;
		@array2 = $sth2->fetchrow_array ;
		my $rid = $array2[0] ;
		$sth2->finish () ;
		if ($rname eq 'Admin') {
		    $admin_rid = $rid ;
		} elsif ($rname eq 'Junior Developer') {
		    $jd_rid = $rid ;
		}

		$query2 = "INSERT INTO role (role_id, group_id, role_name)
                           VALUES ($rid, $group_id, '$rname')" ;
		# &debug ($query2) ;
		$sth2 =$dbh->prepare ($query2) ;
		$sth2->execute () ;
		$sth2->finish () ;

		foreach my $section (keys %{$defaultroles->{$rname}}) {
		    if ($section eq 'forum') {
			$query2 = "SELECT group_forum_id 
                               FROM forum_group_list 
                               WHERE group_id = $group_id" ;
			# &debug ($query2) ;
			$sth2 =$dbh->prepare ($query2) ;
			$sth2->execute () ;
			while (@array2 = $sth2->fetchrow_array) {
			    $roledata{'forum'}{$array2[0]} = $defaultroles->{$rname}{'forum'} ;
			}
			$sth2->finish () ;
		    } elsif ($section eq 'pm') {
			$query2 = "SELECT group_project_id 
                               FROM project_group_list 
                               WHERE group_id = $group_id" ;
			# &debug ($query2) ;
			$sth2 =$dbh->prepare ($query2) ;
			$sth2->execute () ;
			while (@array2 = $sth2->fetchrow_array) {
			    $roledata{'pm'}{$array2[0]} = $defaultroles->{$rname}{'pm'} ;
			}
			$sth2->finish () ;
		    } elsif ($section eq 'tracker') {
			$query2 = "SELECT group_artifact_id 
                               FROM artifact_group_list 
                               WHERE group_id = $group_id" ;
			# &debug ($query2) ;
			$sth2 =$dbh->prepare ($query2) ;
			$sth2->execute () ;
			while (@array2 = $sth2->fetchrow_array) {
			    $roledata{'tracker'}{$array2[0]} = $defaultroles->{$rname}{'tracker'} ;
			}
			$sth2->finish () ;
		    } else {
			$roledata{$section}{0} = $defaultroles->{$rname}{$section} ;
		    }
		    
		    foreach my $rd_it (keys %{$roledata{$section}}) {
			$query2 = "INSERT INTO role_setting (role_id, section_name, ref_id, value)
                                   VALUES ($rid, '$section', $rd_it, '$roledata{$section}{$rd_it}')" ;
			# &debug ($query2) ;
			$sth2 =$dbh->prepare ($query2) ;
			$sth2->execute () ;
			$sth2->finish () ;
		    }
		    
		}
		
	    }
	    
	    #   affecter le rôle Admin aux admins, JD aux autres
	    $query2 = "SELECT user_id, admin_flags FROM user_group WHERE group_id = $group_id" ;
	    # &debug ($query2) ;
	    $sth2 =$dbh->prepare ($query2) ;
	    $sth2->execute () ;
	    while (@array2 = $sth2->fetchrow_array) {
		my $uid        = $array2[0] ;
		my $adminflags = $array2[1] ;
		my ($rid, $rname) ;

		$adminflags =~ s/\s//g ;
		if ($adminflags eq 'A') {
		    $rid = $admin_rid ;
		    $rname = 'Admin' ;
		} else {
		    $rid = $jd_rid ;
		    $rname = 'Junior Developer' ;
		}
		my @reqlist3 = (
				"UPDATE user_group
                                 SET role_id = $rid,
			         admin_flags    = '$defaultroles->{$rname}{'projectadmin'}',
			         forum_flags    = '$defaultroles->{$rname}{'forumadmin'}',
			         project_flags  = '$defaultroles->{$rname}{'pmadmin'}',
			         doc_flags      = '$defaultroles->{$rname}{'docman'}',
			         cvs_flags      = '$defaultroles->{$rname}{'scm'}',
			         release_flags  = '$defaultroles->{$rname}{'frs'}',
			         artifact_flags = '$defaultroles->{$rname}{'trackeradmin'}'
                                 WHERE user_id = $uid AND group_id = $group_id" ,
				"UPDATE forum_perm
				 SET perm_level=$defaultroles->{$rname}{'forum'}
				 WHERE group_forum_id IN (
                                    SELECT group_forum_id
                                    FROM forum_group_list
                                    WHERE group_id=$group_id)
                                 AND user_id=$uid" ,
				"UPDATE project_perm
				 SET perm_level=$defaultroles->{$rname}{'pm'}
				 WHERE group_project_id IN (
                                    SELECT group_project_id
                                    FROM project_group_list
                                    WHERE group_id=$group_id)
                                 AND user_id=$uid" ,
				"UPDATE artifact_perm
				 SET perm_level=$defaultroles->{$rname}{'tracker'}
				 WHERE group_artifact_id IN (
                                    SELECT group_artifact_id
                                    FROM artifact_group_list
                                    WHERE group_id=$group_id)
                                 AND user_id=$uid" ,
				) ;
		foreach my $query3 (@reqlist3) {
		    # &debug ($query3) ;
		    my $sth3 = $dbh->prepare ($query3) ;
		    $sth3->execute () ;
		    $sth3->finish () ;
		}
	    }
	    $sth2->finish () ;
	}
	$sth->finish () ;
	
        &update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    &update_with_sql("20040914", "3.3.0-2+4") ;
    &update_with_sql("20041001", "3.3.0-2+4+1") ;
    &update_with_sql("20041005", "3.3.0-2+5") ;
    &update_with_sql("20041006", "3.3.0-2+6") ;
    &update_with_sql("20041014", "3.3.0-3") ;
    &update_with_sql("20041020", "3.3.0-4") ;
    &update_with_sql("20040729", "4.0.0-0") ;

    $version = &get_db_version ;
    $target = "4.0.0-0+1" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Granting read access permissions to NSS") ;

        @reqlist = ( "GRANT SELECT ON nss_passwd TO ${sys_dbuser}_nss",
		     "GRANT SELECT ON nss_groups TO ${sys_dbuser}_nss",
		     "GRANT SELECT ON nss_usergroups TO ${sys_dbuser}_nss",
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
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.0.0-0+2" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20041031.sql") ;

        @reqlist = @{ &parse_sql_file ("$sqldir/20041031.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            # debug $query ;
            $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &debug ("Granting read access permissions to NSS") ;

        @reqlist = ( "GRANT SELECT ON mta_users TO ${sys_dbuser}_mta",
		     "GRANT SELECT ON mta_lists TO ${sys_dbuser}_mta",
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
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    &update_with_sql("20041104", "4.0.0-0+3") ;
    &update_with_sql("20041108", "4.0.0-0+4") ;
    &update_with_sql("20041124", "4.0.2-0+0") ;

    $version = &get_db_version ;
    $target = "4.0.2-0+1" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Creating automatic commit notification mailing-lists") ;
	

	$query = "SELECT group_id, unix_group_name FROM groups WHERE status='A' ORDER BY group_id" ;
	# &debug ($query) ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	while (@array = $sth->fetchrow_array) {
	    my $group_id   = $array[0] ;
	    my $group_name = $array[1] ;

	    my $query2 = "SELECT count(*) FROM mail_group_list 
   			  WHERE group_id = $group_id 
			  AND list_name = '".$group_name."-commits'" ;
	    # &debug ($query2) ;
	    my $sth2 =$dbh->prepare ($query2) ;
	    $sth2->execute () ;
	    my @array2 = $sth2->fetchrow_array ;
	    $sth2->finish () ;
	    if ($array2[0] == 0) {
		my $listname = $group_name."-commits" ;
		my $listpw = substr (Digest::MD5::md5_base64 ($listname . rand(1)), 0, 16) ;
		
		
		$query2 = "SELECT user_id FROM user_group 
			   WHERE admin_flags = 'A' 
			   AND group_id = $group_id" ;
		# &debug ($query2) ;
		$sth2 =$dbh->prepare ($query2) ;
		$sth2->execute () ;
		my $group_admin = -1 ;
		if (@array2 = $sth2->fetchrow_array) {
		    $group_admin = $array2[0] ;
		}
		$sth2->finish () ;

		$query2 = "INSERT INTO mail_group_list (group_id, list_name, is_public, password, list_admin, status, description)
                           VALUES ($group_id, '$listname', 1, '$listpw', $group_admin, 1, 'commits')" ;
		# &debug ($query2) ;
		$sth2 =$dbh->prepare ($query2) ;
		$sth2->execute () ;
		$sth2->finish () ;
	    }
	}
	$sth->finish () ;

        &update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    &update_with_sql("20050115", "4.0.2-0+3") ;
#
# We got this at upgrade
#
#DBD::Pg::st execute failed: ERREUR:  la relation avec l'OID 387345 n'existe pas at /usr/share/gforge/bin/db-upgrade.pl line 1970.
#Transaction aborted because DBD::Pg::st execute failed: ERREUR:  la relation avec l'OID 387345 n'existe pas at /usr/share/gforge/bin/db-upgrade.pl line 1970.
#Transaction aborted because DBD::Pg::st execute failed: ERREUR:  la relation avec l'OID 387345 n'existe pas at /usr/share/gforge/bin/db-upgrade.pl line 1970.
#Last SQL query was:
#update project_task SET last_modified_date=EXTRACT(EPOCH FROM now())::integer;
#(end of query)
#Your database schema is at version 4.0.2-0+5
#
# This is a hack to disconnect and reconnect the DB and solve the problem
#
    $dbh->rollback ;
    &db_disconnect ;
    &db_connect ;

    $dbh->{AutoCommit} = 0;
    $dbh->{RaiseError} = 1;

    &update_with_sql("20050130", "4.0.2-0+5") ;
    &update_with_sql("20050212", "4.0.2-0+6") ;

    $version = &get_db_version ;
    $target = "4.0.2-0+7" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20050214-nss.sql") ;

        @reqlist = @{ &parse_sql_file ("$sqldir/20050214-nss.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            $query =~ s/TO gforge_nss;/TO ${sys_dbuser}_nss;/ ;
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

    &update_with_sql("20050224-2", "4.1-0") ;

    $version = &get_db_version ;
    $target = "4.1-1" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading with 20050225-nsssetup.sql") ;

        @reqlist = @{ &parse_sql_file ("$sqldir/20050225-nsssetup.sql") } ;
        foreach my $s (@reqlist) {
            $query = $s ;
            $query =~ s/TO gforge_nss;/TO ${sys_dbuser}_nss;/ ;
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

    &update_with_sql("20050311", "4.1-2") ;
    &update_with_sql("20050315", "4.1-3") ;
    &update_with_sql("20050325-2", "4.1-4") ;

    $version = &get_db_version ;
    $target = "4.1-5" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Converting trackers to use their extra fields") ;
	
	$query = "SELECT group_id,group_artifact_id,use_resolution FROM artifact_group_list" ;
	# &debug ($query) ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	while (@array = $sth->fetchrow_array) {
	    my $group_id = $array[0] ;
	    my $gaid 	 = $array[1] ;
	    my $ur 	 = $array[2] ;

	    # Ajout du champ Category
	    my $query2 = "SELECT nextval('artifact_extra_field_list_extra_field_id_seq'::text)" ;
	    # &debug ($query2) ;
	    my $sth2 = $dbh->prepare ($query2) ;
	    $sth2->execute () ;
	    my @array2 = $sth2->fetchrow_array ;
	    $sth2->finish () ;
	    my $aefid = $array2[0] ;
	    
	    $query2 = "INSERT INTO artifact_extra_field_list (extra_field_id, group_artifact_id,field_name,field_type) 
                       VALUES ($aefid, $gaid, 'Category', 1)" ;
	    # &debug ($query2) ;
	    $sth2 =$dbh->prepare ($query2) ;
	    $sth2->execute () ;

	    $query2 = "SELECT id, category_name FROM artifact_category WHERE group_artifact_id=$gaid" ;
	    # &debug ($query2) ;
	    $sth2 = $dbh->prepare ($query2) ;
	    $sth2->execute () ;

	    while (@array2 = $sth2->fetchrow_array) {
		my $cat_id = $array2[0] ;
		my $catname = $array2[1] ;

		if ($catname eq '') { $catname = '[empty]' ; }
		
		my $query3 = "SELECT nextval('artifact_extra_field_elements_element_id_seq'::text)" ;
		# &debug ($query3) ;
		my $sth3 = $dbh->prepare ($query3) ;
		$sth3->execute () ;
		my @array3 = $sth3->fetchrow_array ;
		$sth3->finish () ;
		my $efeid = $array3[0] ;

		$query3 = "INSERT INTO artifact_extra_field_elements (element_id, extra_field_id, element_name, status_id) 
                              VALUES ($efeid, $aefid, ?, 0)" ;
		# &debug ($query3) ;
		$sth3 =$dbh->prepare ($query3) ;
		$sth3->execute ($catname) ;
		$sth3->finish () ;

		$query3 = "INSERT INTO artifact_extra_field_data (artifact_id,field_data,extra_field_id)
                           SELECT artifact_id,$efeid,$aefid FROM artifact 
                           WHERE category_id=$cat_id" ;
		# &debug ($query3) ;
		$sth3 =$dbh->prepare ($query3) ;
		$sth3->execute () ;
		$sth3->finish () ;

		$query3 = "UPDATE artifact_history SET old_value=?,field_name='Category'
			   WHERE old_value='$cat_id' AND field_name='category_id'" ;
		# &debug ($query3) ;
		$sth3 =$dbh->prepare ($query3) ;
		$sth3->execute ($catname) ;
		$sth3->finish () ;
	    }
	    $sth2->finish () ;
	    
	    # Ajout du champ Group
	    $query2 = "SELECT nextval('artifact_extra_field_list_extra_field_id_seq'::text)" ;
	    # &debug ($query2) ;
	    $sth2 = $dbh->prepare ($query2) ;
	    $sth2->execute () ;
	    @array2 = $sth2->fetchrow_array ;
	    $sth2->finish () ;
	    $aefid = $array2[0] ;
	    
	    $query2 = "INSERT INTO artifact_extra_field_list (extra_field_id, group_artifact_id,field_name,field_type) 
                       VALUES ($aefid, $gaid, 'Group', 1)" ;
	    # &debug ($query2) ;
	    $sth2 =$dbh->prepare ($query2) ;
	    $sth2->execute () ;

	    $query2 = "SELECT id, group_name FROM artifact_group WHERE group_artifact_id=$gaid" ;
	    # &debug ($query2) ;
	    $sth2 = $dbh->prepare ($query2) ;
	    $sth2->execute () ;

	    while (@array2 = $sth2->fetchrow_array) {
		my $grp_id = $array2[0] ;
		my $grpname = $array2[1] ;

		if ($grpname eq '') { $grpname = '[empty]' ; }
		
		my $query3 = "SELECT nextval('artifact_extra_field_elements_element_id_seq'::text)" ;
		# &debug ($query3) ;
		my $sth3 = $dbh->prepare ($query3) ;
		$sth3->execute () ;
		my @array3 = $sth3->fetchrow_array ;
		$sth3->finish () ;
		my $efeid = $array3[0] ;

		$query3 = "INSERT INTO artifact_extra_field_elements (element_id, extra_field_id, element_name, status_id) 
                              VALUES ($efeid, $aefid, ?, 0)" ;
		# &debug ($query3) ;
		$sth3 =$dbh->prepare ($query3) ;
		$sth3->execute ($grpname) ;
		$sth3->finish () ;

		$query3 = "INSERT INTO artifact_extra_field_data (artifact_id,field_data,extra_field_id)
                           SELECT artifact_id,$efeid,$aefid FROM artifact 
                           WHERE artifact_group_id=$grp_id" ;
		# &debug ($query3) ;
		$sth3 =$dbh->prepare ($query3) ;
		$sth3->execute () ;
		$sth3->finish () ;

		$query3 = "UPDATE artifact_history SET old_value=?,field_name='Group'
			   WHERE old_value='$grp_id' AND field_name='artifact_group_id'" ;
		# &debug ($query3) ;
		$sth3 =$dbh->prepare ($query3) ;
		$sth3->execute ($grpname) ;
		$sth3->finish () ;
	    }
	    $sth2->finish () ;

	    # Ajout du champ Resolution (s'il existe, cf. $ur)
	    if ($ur) {
		$query2 = "SELECT nextval('artifact_extra_field_list_extra_field_id_seq'::text)" ;
		# &debug ($query2) ;
		$sth2 = $dbh->prepare ($query2) ;
		$sth2->execute () ;
		@array2 = $sth2->fetchrow_array ;
		$sth2->finish () ;
		$aefid = $array2[0] ;
		
		$query2 = "INSERT INTO artifact_extra_field_list (extra_field_id, group_artifact_id,field_name,field_type) 
                       VALUES ($aefid, $gaid, 'Resolution', 1)" ;
		# &debug ($query2) ;
		$sth2 =$dbh->prepare ($query2) ;
		$sth2->execute () ;

		$query2 = "SELECT id, resolution_name FROM artifact_resolution" ;
		# &debug ($query2) ;
		$sth2 = $dbh->prepare ($query2) ;
		$sth2->execute () ;

		while (@array2 = $sth2->fetchrow_array) {
		    my $res_id = $array2[0] ;
		    my $resname = $array2[1] ;

		    if ($resname eq '') { $resname = '[empty]' ; }
		    
		    my $query3 = "SELECT nextval('artifact_extra_field_elements_element_id_seq'::text)" ;
		    # &debug ($query3) ;
		    my $sth3 = $dbh->prepare ($query3) ;
		    $sth3->execute () ;
		    my @array3 = $sth3->fetchrow_array ;
		    $sth3->finish () ;
		    my $efeid = $array3[0] ;

		    $query3 = "INSERT INTO artifact_extra_field_elements (element_id, extra_field_id, element_name, status_id) 
                               VALUES ($efeid, $aefid, ?, 0)" ;
		    # &debug ($query3) ;
		    $sth3 =$dbh->prepare ($query3) ;
		    $sth3->execute ($resname) ;
		    $sth3->finish () ;

		    $query3 = "INSERT INTO artifact_extra_field_data (artifact_id,field_data,extra_field_id)
                               SELECT artifact_id,$efeid,$aefid FROM artifact 
                               WHERE resolution_id=$res_id and group_artifact_id=$gaid" ;
		    # &debug ($query3) ;
		    $sth3 =$dbh->prepare ($query3) ;
		    $sth3->execute () ;
		    $sth3->finish () ;

		    $query3 = "UPDATE artifact_history SET old_value=?,field_name='Resolution'
			       WHERE old_value='$res_id' AND field_name='resolution_id'" ;
		    # &debug ($query3) ;
		    $sth3 =$dbh->prepare ($query3) ;
		    $sth3->execute ($resname) ;
		    $sth3->finish () ;
		}
		$sth2->finish () ;
	    }
	}
	
        &update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    &update_with_sql("20050325-5", "4.1-6") ;
    &update_with_sql("20050605", "4.1-7") ;

    $version = &get_db_version ;
    $target = "4.1-8" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Creating aliases for the extra fields") ;

	$query = "ALTER TABLE artifact_extra_field_list ADD COLUMN alias TEXT" ;
	# debug $query ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	$sth->finish () ;

	my %reserved_alias = (
	    "project" => 1,
	    "type" => 1,
	    "priority" => 1,
	    "assigned_to" => 1,
	    "summary" => 1,
	    "details" => 1,
	) ;

	$query = "SELECT field_name, alias, group_artifact_id, extra_field_id FROM artifact_extra_field_list" ;
	# &debug ($query) ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	while (@array = $sth->fetchrow_array) {
	    my $name = $array[0] ;
	    my $alias = $array[1] ;
	    my $gaid = $array[2] ;
	    my $efid = $array[3] ;

	    if (! $alias) {
		my $newalias = lc $name ;
		$newalias =~ s/\s/_/g ;
		$newalias =~ s/[^_a-z]//g ;
		
		if ($newalias ne "") {
		    if ($reserved_alias{$newalias}) {
			$newalias = "extra_" . $newalias ;
		    }
		    
		    my $candidate ;
		    my $conflict = 0 ;
		    my $count = 0 ;
		    do {
			$candidate = $newalias ;
			$candidate .= $count if ($count > 0) ;
			my $query2 = "SELECT count(*) FROM artifact_extra_field_list WHERE group_artifact_id=$gaid AND LOWER(alias)='$candidate' AND extra_field_id <> $efid" ;
			# &debug ($query2) ;
			my $sth2 =$dbh->prepare ($query2) ;
			$sth2->execute () ;
			my @array2 = $sth2->fetchrow_array ;
			if ($array2[0] == 0) {
			    $conflict = 0 ;
			} else {
			    $conflict = 1 ;
			    $count++ ;
			}
			$sth2->finish () ;
		    } until ($conflict == 0) ;
			
		    my $query2 = "UPDATE artifact_extra_field_list SET alias='$candidate' WHERE extra_field_id=$efid" ;
		    # &debug ($query2) ;
		    my $sth2 =$dbh->prepare ($query2) ;
		    $sth2->execute () ;
		    $sth2->finish () ;
		}
	    }

	}
	$sth->finish () ;

        &update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    &update_with_sql("20050628", "4.1-9") ;
    &update_with_sql("20050711", "4.5-1") ;
    &update_with_sql("20050906","4.5-2"); 
    &update_with_sql("20050804-1","4.5-3"); 

    $version = &get_db_version ;
    $target = "4.5-4" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Updating document sizes") ;

	$query = "SELECT docid, data FROM doc_data" ;
	# &debug ($query) ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	while (@array = $sth->fetchrow_array) {
	    my $docid = $array[0] ;
	    my $b64data = $array[1] ;
	    my $data = decode_base64 ($b64data) ;
	    my $size = length ($data) ;

	    my $query2 = "UPDATE doc_data SET filesize=$size WHERE docid=$docid" ;
	    # &debug ($query2) ;
	    my $sth2 =$dbh->prepare ($query2) ;
	    $sth2->execute () ;
	    $sth2->finish () ;
	}
	$sth->finish () ;

        &update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.5.14-3" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Setting up time tracking") ;

	if (&table_exists ($dbh, "rep_time_category")) {
	    &debug ("...already set up.") ;
	} else {
	    &drop_table_if_exists ($dbh, "rep_time_category") ;
	    &drop_sequence_if_exists ($dbh, "rep_time_category_time_code_seq") ;
	    &drop_table_if_exists ($dbh, "rep_time_tracking") ;
	    &drop_table_if_exists ($dbh, "rep_users_added_daily") ;
	    &drop_table_if_exists ($dbh, "rep_users_added_weekly") ;
	    &drop_table_if_exists ($dbh, "rep_users_added_monthly") ;
	    &drop_table_if_exists ($dbh, "rep_users_cum_daily") ;
	    &drop_table_if_exists ($dbh, "rep_users_cum_weekly") ;
	    &drop_table_if_exists ($dbh, "rep_users_cum_monthly") ;
	    &drop_table_if_exists ($dbh, "rep_groups_added_daily") ;
	    &drop_table_if_exists ($dbh, "rep_groups_added_weekly") ;
	    &drop_table_if_exists ($dbh, "rep_groups_added_monthly") ;
	    &drop_table_if_exists ($dbh, "rep_groups_cum_daily") ;
	    &drop_table_if_exists ($dbh, "rep_groups_cum_weekly") ;
	    &drop_table_if_exists ($dbh, "rep_groups_cum_monthly") ;
	    &drop_view_if_exists ($dbh, "rep_group_act_oa_vw") ;
	    &drop_view_if_exists ($dbh, "rep_user_act_oa_vw") ;
	    &drop_view_if_exists ($dbh, "rep_site_act_daily_vw") ;
	    &drop_view_if_exists ($dbh, "rep_site_act_weekly_vw") ;
	    &drop_view_if_exists ($dbh, "rep_site_act_monthly_vw") ;
	    &drop_table_if_exists ($dbh, "rep_user_act_daily") ;
	    &drop_table_if_exists ($dbh, "rep_user_act_weekly") ;
	    &drop_table_if_exists ($dbh, "rep_user_act_monthly") ;
	    &drop_table_if_exists ($dbh, "rep_group_act_daily") ;
	    &drop_index_if_exists ($dbh, "repgroupactdaily_daily") ;
	    &drop_table_if_exists ($dbh, "rep_group_act_weekly") ;
	    &drop_index_if_exists ($dbh, "repgroupactweekly_weekly") ;
	    &drop_table_if_exists ($dbh, "rep_group_act_monthly") ;
	    &drop_index_if_exists ($dbh, "repgroupactmonthly_monthly") ;

	    @reqlist = @{ &parse_sql_file ("$sqldir/timetracking-init.sql") } ;
	    foreach my $s (@reqlist) {
		$query = $s ;
		# debug $query ;
		$sth = $dbh->prepare ($query) ;
		$sth->execute () ;
		$sth->finish () ;
	    }
	    @reqlist = () ;
	}
	
	&update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    # I had to increase versions from 4.5.14 to 4.5.15
    # The activity view is created by 20060216-nocommit
    # If the view doesn't exists apply 
    if (! &view_exists ($dbh, 'activity_vw')) {
        &update_with_sql("20050812","4.5.15-10merge"); 
        &update_with_sql("20050822-2","4.5.15-11merge"); 
        &update_with_sql("20050823","4.5.15-12merge"); 
        &update_with_sql("20050824","4.5.15-13merge"); 
        &update_with_sql("20050831","4.5.15-14merge"); 

        &update_with_sql("20060113","4.5.15-15"); 
        &update_with_sql("20060214","4.5.15-16"); 
        &update_with_sql("20060216-2-debian-nocommit","4.5.15-17"); 
    }

    $version = &get_db_version ;
    $target = "4.5.15-21" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Fixing past mistakes in role naming") ;

	my $defaultroles_restricted = {
	    'Admin'	       => { 'projectadmin'=>'A', 'frs'=>'1', 'scm'=>'1', 'docman'=>'1', 'forumadmin'=>'2', 'trackeradmin'=>'2', 'pmadmin'=>'2' },
	    'Senior Developer' => { 'projectadmin'=>'0', 'frs'=>'1', 'scm'=>'1', 'docman'=>'1', 'forumadmin'=>'2', 'trackeradmin'=>'2', 'pmadmin'=>'2' },
	    'Junior Developer' => { 'projectadmin'=>'0', 'frs'=>'0', 'scm'=>'1', 'docman'=>'0', 'forumadmin'=>'0', 'trackeradmin'=>'0', 'pmadmin'=>'0' },
	    'Doc Writer'       => { 'projectadmin'=>'0', 'frs'=>'0', 'scm'=>'0', 'docman'=>'1', 'forumadmin'=>'0', 'trackeradmin'=>'0', 'pmadmin'=>'0' },
	    'Support Tech'     => { 'projectadmin'=>'0', 'frs'=>'0', 'scm'=>'0', 'docman'=>'1', 'forumadmin'=>'0', 'trackeradmin'=>'0', 'pmadmin'=>'0' }
	} ;

	foreach my $drname (keys %{$defaultroles_restricted}) {
	    $query = "UPDATE role SET role_name='$drname' WHERE role_id IN (SELECT role.role_id" ;
	    my $from = "" ;
	    my $where = "" ;
	    my $setting = "" ;
	    my $value = 0 ;
	    foreach my $setting (keys %{$defaultroles_restricted->{$drname}}) {
		$value = $defaultroles_restricted->{$drname}->{$setting} ;
		$from .= ", role_setting rs_$setting" ;
		$where .= "role.role_id = rs_$setting.role_id AND rs_$setting.section_name='$setting' AND " ;
		$where .= "rs_$setting.value = '$value' \nAND " ;
	    }
	    $query .= "\nFROM role$from" ;
	    $query .= "\nWHERE $where role.role_name='rname')";
	    push @reqlist, $query;
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
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

      &update_with_sql("20051103_transiciel_motscle_document","4.6-1");
	
      &update_with_sql("20070924-forum-perm","4.6.99-1");
      &update_with_sql("20070924-project-perm","4.6.99-2");
      &update_with_sql("20070924-artifact-perm","4.6.99-3");
	
    $version = &get_db_version ;
    $target = "4.6.99-4" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Dropping old translations table") ;

	&drop_table_if_exists ($dbh, "tmp_lang") ;

        &update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.6.99-5" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Updating available themes") ;

	my @obsolete_themes = qw/ classic debian savannah
                                  savannah_codex savannah_forest
                                  savannah_reverse savannah_sad
                                  savannah_savannah savannah_slashd
                                  savannah_startrek
                                  savannah_transparent savannah_water
                                  savannah_www.gnu.org
                                  savannah_darkslate forged kde
                                  darkaqua / ;

	my $otids = join (',', map { "'$_'" } @obsolete_themes) ;
	
	$query = "UPDATE users SET theme_id=1 WHERE theme_id IN
                     (SELECT theme_id FROM themes WHERE dirname IN ($otids))" ;
	push @reqlist, $query;
	
	$query = "DELETE FROM themes WHERE dirname IN ($otids)" ;
	push @reqlist, $query;

	my %new_themes = (
	    'gforge-classic'      => 'GForge classic',
	    'gforge-simple-theme' => 'GForge simple',
	    'lite'                => 'GForge lite'
	    ) ;

	foreach my $dir (sort keys %new_themes) {
	    $query = "INSERT INTO themes (dirname, fullname) VALUES ('$dir', '$new_themes{$dir}')" ;
	    push @reqlist, $query;
	}

	foreach my $s (@reqlist) {
	    $query = $s ;
	    # &debug ($query) ;
	    $sth = $dbh->prepare ($query) ;
	    $sth->execute () ;
	    $sth->finish () ;
	}
	@reqlist = () ;

        &update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }

    $version = &get_db_version ;
    $target = "4.6.99-6" ;
    if (&is_lesser ($version, $target)) {
      &debug ("DROP UNIQUE INDEX never UNIQUE") ;
      &drop_index_if_exists ($dbh, "statsaggsitebygrp_oid") ;
      &drop_index_if_exists ($dbh, "statsprojectmetric_oid") ;
      &drop_index_if_exists ($dbh, "statsagglogobygrp_oid") ;
      &drop_index_if_exists ($dbh, "statsprojectdevelop_oid") ;
      &drop_index_if_exists ($dbh, "statssubdpages_oid") ;
      &drop_index_if_exists ($dbh, "statscvsgrp_oid") ;
      &drop_index_if_exists ($dbh, "statsproject_oid") ;
      &drop_index_if_exists ($dbh, "statssite_oid") ;
      &drop_index_if_exists ($dbh, "statssitepgsbyday_oid") ;
      &update_db_version ($target) ;
      &debug ("...OK.") ;
      $dbh->commit () ;
    }
    
    &update_with_sql("20090327_create_table_project_tags","4.6.99-7");
    &update_with_sql("20090402-add-projecttags-constraints","4.7.99-1");
    &update_with_sql("20090402-forum-attachment-types","4.7.99-2");

    &update_with_sql("20090507-add_artifact_workflow","4.8.99-1");
    &update_with_sql("20090507-add_element_pos","4.8.99-2");
    &update_with_sql("20090507-add_project_query","4.8.99-3");
    &update_with_sql("20090507-browse_list","4.8.99-4");

    $version = &get_db_version ;
    $target = "4.8.99-5" ;
    if (&is_lesser ($version, $target)) {
	&debug ("Initialising tracker workflows") ;

	
	$query = "SELECT group_id, artifact_group_list.group_artifact_id, element_id, artifact_extra_field_elements.extra_field_id
		FROM artifact_extra_field_list, artifact_extra_field_elements, artifact_group_list
		WHERE artifact_extra_field_list.extra_field_id=artifact_extra_field_elements.extra_field_id
		AND artifact_group_list.group_artifact_id = artifact_extra_field_list.group_artifact_id
		AND field_type=7" ;
	# &debug ($query) ;
	$sth = $dbh->prepare ($query) ;
	$sth->execute () ;
	while (@array = $sth->fetchrow_array) {
	    my $gid = $array[0];
	    my $gaid = $array[1];
	    my $eid = $array[2];

	    my $query2 = "SELECT extra_field_id
				FROM artifact_extra_field_list 
				WHERE group_artifact_id=$gaid
                                AND field_type = 7
				ORDER BY field_type ASC" ;
	    my $sth2 = $dbh->prepare ($query2) ;
	    $sth2->execute () ;
	    
	    if (my @array2 = $sth2->fetchrow_array) {
		my $efid = $array2[0];
		$sth2->finish () ;

		$query2 = "SELECT element_id,element_name,status_id
				FROM artifact_extra_field_elements
				WHERE extra_field_id = $efid
				ORDER BY element_pos ASC, element_id ASC" ;
		# debug $query2;
		$sth2 = $dbh->prepare ($query2) ;
		$sth2->execute () ;
		while (@array2 = $sth2->fetchrow_array) {
		    my $eid2 = $array2[0];
		    if ($eid2 != $eid) {
			my $query3 = "INSERT INTO artifact_workflow_event
				(group_artifact_id, field_id, from_value_id, to_value_id)
				VALUES ($gaid, $efid, $eid, $eid2)";
			# debug $query3;
			my $sth3 = $dbh->prepare ($query3) ;
			$sth3->execute () ;
			$sth3->finish () ;
			$query3 = "INSERT INTO artifact_workflow_event
				(group_artifact_id, field_id, from_value_id, to_value_id)
				VALUES ($gaid, $efid, $eid2, $eid)";
			# debug $query3;
			$sth3 = $dbh->prepare ($query3) ;
			$sth3->execute () ;
			$sth3->finish () ;
		    }
		}
		$sth2->finish () ;
		my $query3 = "INSERT INTO artifact_workflow_event
				(group_artifact_id, field_id, from_value_id, to_value_id)
				VALUES ($gaid, $efid, 100, $eid)";
		# debug $query3;
		my $sth3 = $dbh->prepare ($query3) ;
		$sth3->execute () ;
		$sth3->finish () ;
	    }
	}
	$sth->finish () ;

	@reqlist = () ;
	&update_db_version ($target) ;
	&debug ("...OK.") ;
	$dbh->commit () ;
    }

    &update_with_sql("20100308-forum-attachment-types","4.8.99-6");

    $version = &get_db_version ;
    $target = "4.8.99-7" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Granting read access permissions to NSS and MTA") ;

        @reqlist = ( "GRANT SELECT ON nss_passwd TO ${sys_dbuser}_nss",
                    "GRANT SELECT ON nss_groups TO ${sys_dbuser}_nss",
                    "GRANT SELECT ON nss_usergroups TO ${sys_dbuser}_nss",
                    "GRANT SELECT ON mta_users TO ${sys_dbuser}_mta",
                    "GRANT SELECT ON mta_lists TO ${sys_dbuser}_mta",
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

    &update_with_sql("20100330-add-system-event","5.0.0-1");
    &update_with_sql("20100331-alter-system-event","5.0.0-2");
    &update_with_sql("20100505-alter-user-preference","5.0.1-1");
    &update_with_sql("20100506-add-widgets","5.0.1-2");
    &update_with_sql("20100517-add-project-widgets","5.0.1-3");
    &update_with_sql("20100518-pfo-rbac","5.0.1-4");
    &update_with_sql("20100524-pfo-rbac","5.0.1-5");
    &update_with_sql("20100606-clean-perm-views","5.0.1-6");
    &update_with_sql("20100610-pfo-rbac","5.0.1-7");
    &update_with_sql("20100730-docman","5.0.1-8");
    &update_with_sql("20100924-theme","5.0.1-9");
    &update_with_sql("20100926-pfo-rbac","5.0.1-10");
    &update_with_sql("20100927-pfo-rbac","5.0.1-11");
    &update_with_sql("20101012-docman-webdav","5.0.51-1");
    &update_with_sql("20101021-pfo-rbac","5.0.51-2");
    &update_with_sql("20101025-ipv6","5.0.51-3");

    $version = &get_db_version ;
    $target = "5.0.51-4" ;
    if (&is_lesser ($version, $target)) {
        &debug ("Granting read access permissions to NSS and MTA") ;

        @reqlist = ( "GRANT SELECT ON nss_passwd TO ${sys_dbuser}_nss",
                    "GRANT SELECT ON nss_groups TO ${sys_dbuser}_nss",
                    "GRANT SELECT ON nss_usergroups TO ${sys_dbuser}_nss",
                    "GRANT SELECT ON mta_users TO ${sys_dbuser}_mta",
                    "GRANT SELECT ON mta_lists TO ${sys_dbuser}_mta",
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

    &update_with_sql("20101027-docman-lock","5.0.51-5");
    &update_with_sql("20101105-pfo-rbac","5.0.51-6");
    &update_with_sql("20101029-docman-monitoring","5.0.51-7");
    &update_with_sql("20100402_add_query_options","5.0.51-8");
    &update_with_sql("20101024-docman-createonline","5.0.51-9");
    &update_with_sql("20101213-project-template","5.0.51-10");

    ########################### INSERT HERE #################################

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
&db_disconnect ;

sub get_pg_version () {
    my $command;
    if (-x '/usr/bin/pg_lsclusters' ) {
    	$command = q(/usr/bin/pg_lsclusters | grep 5432 | grep online | cut -d' ' -f1) ;
    } else {
    	$command = q(dpkg -s postgresql | awk '/^Version: / { print $2 }') ;
    }
    my $version = qx($command) ;
    chomp $version ;
    return $version ;
}

sub create_metadata_table ( $ ) {
    my $v = shift || "2.5-7+just+before+8" ;

    my ($query, $sth, @array) ;

    # Let's create this table if we have it not
    if (! &table_exists ($dbh, 'debian_meta_data')) {
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

    $query = "UPDATE debian_meta_data SET value = '$v' WHERE key = 'db-version'" ;
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

sub update_with_sql ( $$ ) {
    my $sqlfile = shift or die "Not enough arguments" ;
    my $target = shift or die "Not enough arguments" ;
    my $version = &get_db_version ;
    if (&is_lesser ($version, $target)) {
        &debug ("Upgrading database with $sqlfile.sql") ;

        @reqlist = @{ &parse_sql_file ("$sqldir/$sqlfile.sql") } ;
        foreach my $s (@reqlist) {
            my $query = $s ;
            # debug $query ;
            my $sth = $dbh->prepare ($query) ;
            $sth->execute () ;
            $sth->finish () ;
        }
        @reqlist = () ;

        &update_db_version ($target) ;
        &debug ("...OK.") ;
        $dbh->commit () ;
    }
}
