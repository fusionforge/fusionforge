#!/usr/bin/perl -w
# bugzilla2gforge.pl - transfers bug reports from Bugzilla to gforge.
# steev hise, steev AT datamassage.com, december 2001
# todd wallentine, tcw AT ksu edu, february 2002
# oliver blume, skytecag.com, march 2004
#
# version 1.2 - copyleft 2001 - GNU Public License
#
# to customize this, of course you'll need to change a lot of 
# the mappings. Most important, the user map.
# Mostly these things happen in the "init_maps"
# subroutine, though there are a few other places you
# might have to customize. Also note in the init_dbh subroutine
# you'll need to put in your database users and passwords and hosts.

# The script has one characteristic: It creates the artifacts in GForge
# with the same id that the bugs have in the bugzilla system. That's 
# because we put the bug id in our source code comments and keeping
# the ids makes it easier to lookup a bug reports in Gforge.
# This requires an empty artifacts table. 
#
# If you don't care about keeping your bug ids and/or want to import bugzilla 
# bugs into a running gforge system you have to rewrite the script in some parts.
#####################################################################

use strict;
use warnings;

use DBI;
use Data::Dumper;   # debugging only.
use MIME::Base64;

use vars qw( $BZ_DBH $SF_DBH $MREF %ID_MAP $SFGROUP $SFBUGTRACKERID);
$| = 1;
my $time = time;

# change this to the gforge group id of the project you're
# importing into.
$SFGROUP = 5;   # all the bugs are for this one gforge project.
$SFBUGTRACKERID = 101; # value of bug-tracker-id (artifact.group_artifact_id).

($BZ_DBH, $SF_DBH) = &init_dbh;   # open all the database handles.

$MREF = &init_maps;  

&check_users();

#############################################################
&create_artifact_categories();

&create_artifact_groups();

&create_artifacts();

&update_artifacts_count_agg();

&update_artifact_seq();

$BZ_DBH->disconnect;
$SF_DBH->disconnect;

# there. done.
print "Done. Transferred " , scalar keys %ID_MAP, " bugs in ", time-$time, " seconds.\n\n";





############ subroutines  ##########################


# be sure to change the hosts, users, passwords to values appropo
# to your setup.
sub init_dbh {
	# first connect to the Bugzilla mysql database.
	my $bzdb = 'test';
	my $bzhost = 'localhost';
	my $bzdsn = "DBI:mysql:database=$bzdb;host=$bzhost";
	my $bzuser = 'root';
	my $bzpw   = '';

	my $bz_dbh = DBI->connect($bzdsn, $bzuser, $bzpw);
	$bz_dbh->{ RaiseError } = 1;

	# now connect to the gforge postgres database
	my $sfdb = 'alexandria';
	my $sfhost = 'localhost';  # probably running locally so unneeded.
	my $sfdsn = "DBI:PgPP:dbname=$sfdb;host=$sfhost;";
	my $sfuser = 'postgres';
	my $sfpw   = '';  # no passwd needed

	my $sf_dbh = DBI->connect($sfdsn, $sfuser, $sfpw);
    $sf_dbh->{AutoCommit} = 0;  # enable transactions, if possible
	$sf_dbh->{ RaiseError } = 1;
	
	return $bz_dbh, $sf_dbh;
}


# this just sets up some hashes and stuff for mapping between
# the bugzilla schema and the gforge schema.
sub init_maps {
	# this going to return a hash of references.
	# each reference is an anonymous subroutine.
	# each reference maps the values of certain fields from
	# one database to another.
	
	# you pass each subroutine the original value and it
	# returns the mapped value, plus, in some cases,
	# the name of the field in the destination table where it goes.
	
	my $mapref = {};
	
	# first, a few all-purpose mappings
	
	# map bugzilla user ids into gforge user ids.
	# before running this script you have to create the users in gforge manually.
	$mapref->{user} = sub {
		my ($bz_userid) = @_;
		my $usermap =
					{
					    3   =>  109,		# attrossbach
						4   =>  108,		# mreinermann
						5   =>  111,        # mroeder
						6   =>  112,        # fgrassinger
						7   =>  110,        # hnuernberger
						11  =>  113,        # tschuett
						14  =>  107,        # oblume
						0   =>  100,		# none nobody - default
					};
		if ($usermap->{$bz_userid}) {
		    return $usermap->{$bz_userid};
		} else {
		    die "User not found: # $bz_userid";
		}
	};	

	$mapref->{bug_id} = sub {
		my($bz, $sf) = @_;
		
		# map bug_id and get longdesc for the bug
		# we keep the bug_id so that we can easily look up old bug reports.
		# requires emtpy artifacts table of course.
        # change this if you plan to import into existing artifacts table where you cannot choose your own bug id. 	
		$sf->{artifact_id} = $bz->{bug_id};
		
		# now add a little note.
		$sf->{details} = "NOTE: This bug is originally from Bugzilla " .
		    "<a href=\"https://bugzilla.skytec-ag.net/cgi-bin/bugzilla/show_bug.cgi?id=$bz->{bug_id}\">Bug #$bz->{bug_id}</a>.\n\n";

		# find the first longdesc and use as details field in SF.
		my $bz_sth = $BZ_DBH->prepare('SELECT thetext FROM longdescs WHERE bug_id=? order by bug_when');
		$bz_sth->execute($bz->{bug_id});
		my ($text) = $bz_sth->fetchrow_array;
		$sf->{details} .= $text;
		
	};
	
	$mapref->{assigned_to} = sub {
		my($bz, $sf) = @_;
		$sf->{assigned_to} = $MREF->{user}($bz->{assigned_to});
	};
	
	$mapref->{bug_severity} = sub {
		my($bz, $sf) = @_;
		$sf->{details} .= "\nOriginal severity: ". $bz->{bug_severity};
	};
	
	$mapref->{bug_status} = sub {
		my($bz, $sf) = @_;
		my $status_map = {
							'UNCONFIRMED'	=> 1,
							'NEW'		=> 1,
							'ASSIGNED'	=> 1,
							'REOPENED'	=> 1,
							'RESOLVED'	=> 2,
							'VERIFIED'	=> 2,
							'CLOSED'	=> 2,
						};
		$sf->{status_id} = $status_map->{$bz->{bug_status}};
	};
	
	$mapref->{creation_ts} = sub {
		my($bz, $sf) = @_;
		$sf->{open_date} = $bz->{creation_ts};
	};
	
	# here we check the status, and if it's a closed bug,
	# we assign close_date the value of delta_ts.
	# this assumes that if a bug is closed, closing it
	# was the last thing ever done to it.
	$mapref->{delta_ts} = sub {
		my($bz, $sf) = @_;
		if($sf->{status_id} == 3) {
			$sf->{close_date} = $bz->{delta_ts};
		}
	};
	
	$mapref->{short_desc} = sub {
		my($bz, $sf) = @_;
		$sf->{summary} = $bz->{short_desc};
	};

	$mapref->{priority} = sub {
		my($bz, $sf) = @_;
		$bz->{priority} =~ s/P//;       # remove the stupid letter P.
		$sf->{priority} = $bz->{priority} * 2 - 1;
	};
	
	$mapref->{reporter} = sub {
		my($bz, $sf) = @_;
		$sf->{submitted_by} = $MREF->{user}($bz->{reporter});
	};
	
	# we're mapping bugzilla "versions" to gforge "artifact_groups" ids.
	$mapref->{version} = sub {
		my($bz, $sf) = @_;
		my $sf_sth = $SF_DBH->prepare('SELECT id FROM artifact_group WHERE group_artifact_id = ? and group_name =?');
		$sf_sth->execute($SFBUGTRACKERID, $bz->{version});
		my ($id) = $sf_sth->fetchrow_array;
		$sf->{artifact_group_id} = $id;
	};
	
	# we're mapping bugzilla "components" to gforge "category" ids.
	$mapref->{component} = sub {
		my($bz, $sf) = @_;

		my $sf_sth = $SF_DBH->prepare('SELECT id FROM artifact_category WHERE group_artifact_id = ? and category_name =?');
		$sf_sth->execute($SFBUGTRACKERID, $bz->{component});
		my ($id) = $sf_sth->fetchrow_array;
		$sf->{category_id} = $id;
	};
	
	#  the names of the gforge resolutions are identical,
	# we just need to map the names to the ids.
	$mapref->{resolution} = sub {
		my($bz, $sf) = @_;	
		my $resolution_map = {
					''			=>      100,
					'ACCEPTED'   =>    1,
					'FIXED'			=>	3,
					'INVALID'		=>      4,
					'WONTFIX'		=>      10,
				    'LATER'			=>      5,
					'REMIND'		=>	9,
					'DUPLICATE'		=>	2,
				    'WORKSFORME'	        =>	11,
				};
		$sf->{resolution_id} = $resolution_map->{$bz->{resolution}};
	};
	
	return $mapref;	
}


#############################################################
# create artifacts
# maps bugzilla.bugs into gforge.artifacts
sub create_artifacts {

    # remove existing artifacts from gforge database!!!!!
    # this is helpful while debugging the script to avoid violation of PK constraints or duplicate entries.
    # should only be done when working on a fresh and empty database.
    # if you plan importing into an existing gforge system you might want to re-write part of the code
    print "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";
    print "Should i empty the artifacts database before importing from bugzilla???? (y/n)\n";
    print "All existing tracker/forum data will be lost if you do that!\n";
    my $key = getc();
    if ($key eq 'y') {
        print "You choose to delete all existing artifacts.\n";
        print "Please edit the perl script and remove the exit statement from the line below.\n";
        print "This is done to prevent accidental removal of your existing bugs.\n";
        exit; 
        
        $SF_DBH->do("delete from artifact_message");
        $SF_DBH->do("delete from artifact_file");
        $SF_DBH->do("delete from artifact_history");
        $SF_DBH->do("delete from artifact");
    } 
    
    my @bzbugs_fields = ( 'bug_id',
    							 'assigned_to',
    							 'bug_severity', 
    							 'bug_status',
    							 'creation_ts',
    							 'delta_ts',
    							 'short_desc',
    							 'priority',
    							 'reporter',
    							 'version',
    							 'component',
    							 'resolution'
    						);
    						
    my $bzbugs_fieldstring = join (", ", @bzbugs_fields);
    
    # all the timestamps are stored as unixtime in integer fields
    # in the gforge database. dumb, but we're stuck with it.
    $bzbugs_fieldstring =~ s/(\w+_ts)/UNIX_TIMESTAMP($1) AS $1/g;
    
    # first, handle the bugs table
    # get all the records from the table, and loop through them
    # for each one, loop through the fields, doing the appropriate
    # conversion for each. build an array of field names and and 
    # an array of values.
    my $sql = "SELECT $bzbugs_fieldstring from bugs";
    my $bz_sth = $BZ_DBH->prepare($sql);
    $bz_sth->execute;
    
    while(my $bug = $bz_sth->fetchrow_hashref) {
    	print "\n\n****************************************************\nBugzilla bug # $bug->{bug_id}:  ";

    	# print "Hit key to continue!\n->";
    	# getc;

    	my $sf_bug = {};
    	foreach my $field (@bzbugs_fields) {
    		# warn "field is $field.\n";
    		$MREF->{$field}($bug, $sf_bug);
    	}
    	
    	# print "original data:\n--------------\n", Dumper($bug);
    	# print "new data:     \n--------------\n", Dumper($sf_bug);
    	
    	# insert values into sf db.
    	# first create list of fieldnames and values
    	my(@fields,@values);
    	foreach my $key (sort keys %$sf_bug) {
    		push @fields, $key;
    		# most of the gforge fields require NOT NULL.
    		# if(length($sf_bug->{$key})<1) { $sf_bug->{$key} = '0' };
    		push @values, &quotesub($sf_bug->{$key});		
    		# print "key: $key -> " . &quotesub($sf_bug->{$key}) . "\n";
    	}	
    	
    	push @fields, "group_artifact_id";
    	push @values, $SFBUGTRACKERID;
    	
    	my $n = scalar(@values);
    	my $placeholders = '?,'x$n; chop $placeholders;
    	
    	my $sql = 'INSERT INTO artifact (' . join( ',', @fields) 
    					. ") VALUES ($placeholders )";	
    	print "-> Inserting new bug #$bug->{bug_id} into table artifact...\n";
    	# warn "bug insert sql: $sql\n";
    
    	my $sf_sth = $SF_DBH->prepare($sql);
    	$sf_sth->execute(@values);
    	# $sf_sth->finish;

# since we keep the bug_id we don't need the ID_MAP   
# change this if you plan to import into existing artifacts table where you cannot choose your own bug id. 	
#    	# after the insert, get the bug_id of the bug just inserted,
#    	# using the postgres "currval" function.
#    	# then add to the ID_MAP hash.
#    	$sql = 'select currval(\'bug_pk_seq\')';
#    	$sf_sth = $SF_DBH->prepare($sql);
#    	$sf_sth->execute;
    	my $sf_bug_id =  $bug->{bug_id};
    	# print " ->  transferred to gforge bug $sf_bug_id.\n";
    	$ID_MAP{$bug->{bug_id}} = $sf_bug_id;
    	# $sf_sth->finish;
    	
        # artifact_history is not filled, we dont care for that type of data
       
        # get the comments from bugzilla.longdescs into gforge.artifact_messages 
    	my $bz_sth = $BZ_DBH->prepare('SELECT thetext, UNIX_TIMESTAMP(bug_when) as date, who FROM longdescs WHERE bug_id=? order by bug_when');
    	$bz_sth->execute($bug->{bug_id});
    	my $longdesc = $bz_sth->fetchrow_hashref;	 # throw this away, we already have it.

    	$sql = "INSERT INTO artifact_message (artifact_id, submitted_by, from_email, adddate, body) VALUES ($sf_bug_id, ?, ?, ?, ?)";
    	# warn "inserting longdescs SQL: $sql";
    	
    	while($longdesc = $bz_sth->fetchrow_hashref) {
    		my $body = &quotesub($longdesc->{thetext});
    		my $adddate = $longdesc->{date};
    		my $submitted_by = $MREF->{user}($longdesc->{who});
    		my $from_email = "";
        	print "->   Adding message for bug #$sf_bug_id ...\n";
        	$sf_sth = $SF_DBH->prepare($sql);
    		$sf_sth->execute($submitted_by, $from_email, $adddate, $body);
    	}
        
        # now copy file attachments (at least try it)
        
    	$bz_sth = $BZ_DBH->prepare('SELECT thedata, UNIX_TIMESTAMP(creation_ts) as date, description, mimetype, filename, submitter_id, bug_id FROM attachments WHERE bug_id=? order by creation_ts');
    	$bz_sth->execute($bug->{bug_id});

    	$sql = "INSERT INTO artifact_file (artifact_id, description, bin_data, filename, filesize, filetype, adddate, submitted_by) VALUES ($sf_bug_id, ?, ?, ?, ?, ?, ?, ?)";
    	# warn "inserting attachment SQL: $sql";
    	
    	while(my $file = $bz_sth->fetchrow_hashref) {
		    my $bin_data = $file->{thedata};
    		my $filetype = $file->{mimetype};
       		my $filesize = length($bin_data);
      	    $bin_data = encode_base64($bin_data);
    		my $adddate = $file->{date};
    		my $submitted_by = $MREF->{user}($file->{submitter_id});
    		my $description = $file->{description};
    		my $filename = $file->{filename};
    		$filename =~ s/\\/\\\\/g;
        	print "->   Adding attachment for bug #$sf_bug_id ...\n";
        	$sf_sth = $SF_DBH->prepare($sql);
    		$sf_sth->execute($description, $bin_data, $filename, $filesize, $filetype,
    		            $adddate, $submitted_by);
    	}

    	# done with bug activity. done with this bug, actually.	
    	
    }
}

#############################################################
# update artifacts_counts_agg
sub update_artifacts_count_agg {
 
    # get number of bugs
    my $sql = "select count(*) as cnt from artifact where group_artifact_id = $SFBUGTRACKERID";
	my $bugstotal = $SF_DBH->selectrow_array($sql);
    
    # get number of open bugs
    $sql = "select count(*) as cnt from artifact where group_artifact_id = $SFBUGTRACKERID and status_id = 1";
	my $bugsopen = $SF_DBH->selectrow_array($sql);
    
    # update artifacts_count_agg
    $sql = "update artifact_countS_agg set count = $bugstotal, open_count = $bugsopen where group_artifact_id = $SFBUGTRACKERID";
	my $res = $SF_DBH->selectrow_array($sql);
    
}

#############################################################
# update artifact_seq
sub update_artifact_seq {
    
    # since we used the bugzilla bug ids for the artifacts id and
    # not the artifact_artifact_id_seq to generate ids, we have to adjust the 
    # currval of the sequence (otherwise you wouldnt be able to 
    # post new bug reports.
    
    # get max(artifact_id)
    my $sql = "select max(artifact_id) from artifact";
	my $maxid = $SF_DBH->selectrow_array($sql);

	# update sequence' currval
    print "Updating sequence artifact_artifact_id_seq to $maxid\n";
	$sql = "select setval('artifact_artifact_id_seq', $maxid)";
	my $res = $SF_DBH->selectrow_array($sql);
}


#############################################################
# create categories
# map bugzilla.bugs.component to gforge.artifact_category
sub create_artifact_categories {
    
    my $sql = "select distinct component from bugs";
    my $bz_sth = $BZ_DBH->prepare($sql);
    $bz_sth->execute;
    
    while (my $component = $bz_sth->fetchrow_hashref) {
        print "Bugzilla component: $component->{component}\n";
        # check if category already exists
        $sql = "select count(*) from artifact_category where group_artifact_id = ? and category_name = ?";
        my $sf_sth = $SF_DBH->prepare($sql);
        $sf_sth->execute($SFBUGTRACKERID, $component->{component});
        my $count = $sf_sth->fetchrow_array();
        if ($count < 1) {
            
            $sql = "insert into artifact_category (group_artifact_id, category_name, auto_assign_to) " .
                "values ($SFBUGTRACKERID, '$component->{component}', " . $MREF->{user}(0) . ")";
            # print $sql . "\n";
            my $sf_sth = $SF_DBH->prepare($sql);
            $sf_sth->execute;
        } else {
            print "Category $component->{component} already exists for bugtracker $SFBUGTRACKERID\n";
        }
    }
}    

#############################################################
# create artifact groups
# map bugzilla.bugs.version to gforge.artifact_group
# we use the artifact group value as version information of the buggy software module
sub create_artifact_groups {
    
    my $sql = "select distinct version from bugs";
    my $bz_sth = $BZ_DBH->prepare($sql);
    $bz_sth->execute;
    
    while (my $version = $bz_sth->fetchrow_hashref) {
        print "Bugzilla version: $version->{version}\n";
        # check if group already exists
        $sql = "select count(*) from artifact_group where group_artifact_id = ? and group_name = ?";
        my $sf_sth = $SF_DBH->prepare($sql);
        $sf_sth->execute($SFBUGTRACKERID, $version->{version});
        my $count = $sf_sth->fetchrow_array();
        if ($count < 1) {
            $sql = "insert into artifact_group (group_artifact_id, group_name) " .
                "values ($SFBUGTRACKERID, '$version->{version}')";
            # print $sql . "\n";
            my $sf_sth = $SF_DBH->prepare($sql);
            $sf_sth->execute;
        } else {
            print "Version $version->{version} already exists for bugtracker $SFBUGTRACKERID\n";
        }
    }
}   

#############################################################
# check users
# check if all bugzilla users have a mapping 
# if not, then exit with a warning.
sub check_users {
    
    my $checkit = sub {
        my $col = shift;
        my $bz_sth = $BZ_DBH->prepare("select $col as user_id, count(*) as cnt from bugs group by $col");
        $bz_sth->execute;
        while (my $user_id = $bz_sth->fetchrow_hashref) {
            print "Checking $col user # $user_id->{user_id} = $user_id->{cnt}\n";
            # check if user has a mapping, if not the scripts exits
            $MREF->{user}($user_id->{user_id});
        }
    };
    
    # select all bugzilla users that have a bug reported or assigned.
    &$checkit("assigned_to");
    &$checkit("reporter");
}


# the gforge database should not have any double-quotes.
sub quotesub {
	my ($text) = @_;
	# $text =~ s/"/&quot;/g;
	return $text;
}
