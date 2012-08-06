# A state machine to turn an SQL file into list of requests
# (represented by an array of strings)
#
### AUTHOR/COPYRIGHT
# This file is copyright 2002, 2008 Roland Mas <99.roland.mas@aist.enst.fr>,
#
# This is Free Software; you can redistribute it and/or modify it under the
# terms of the GNU General Public License version 2, as published by the
# Free Software Foundation.
#
### USAGE
# @reqlist = @{ &parse_sql_file ("blah.sql") } ;
# foreach $req (@reqlist) {
#     $sth = $dbh->prepare ($query) ;
#     $sth->execute () ;
#     $sth->finish () ;
# }
#
### BUGS
# * No real bugs known, but see TODO.
# * Should bugs appear, please notify me (patches are of course welcome)
#
### TODO
# * Make sure the output of pg_dump is interpreted the way it should.

use strict ;
use subs qw/ &parse_sql_file &sql_parser_debug / ;

sub sql_parser_debug ( $ ) ;
sub parse_sql_file ( $ ) ;

sub parse_sql_file ( $ ) {
    my $f = shift ;
    open F, $f or die "Could not open file $f: $!\n" ;

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
		  'ERROR' => 9,
		  'IN_COMMENT' => 10,
		  'IN_SQL_COMMENT' => 11,
		  'IN_DOLDOL' => 12,
		  'DONE' => 999) ;
    my ($state, $l, $par_level, $com_level, $chunk, $rest, $sql, @sql_list, $copy_table, $copy_field_list, $copy_rest, @copy_data, @copy_data_tmp, $copy_field, @doldolstack) ;
    $l = $sql = $chunk = $rest = '';

    # Init the state machine

    $state = $states{INIT} ;
    
    # my $n = 0 ;
    
  STATE_LOOP: while ($state != $states{DONE}) { # State machine main loop
      sql_parser_debug "STATE_LOOP: state = $state" ;
      sql_parser_debug "l=$l, sql=$sql, chunk=$chunk, rest=$rest";
    STATE_SWITCH: {		# State machine step processing
	$state == $states{INIT} && do {
	    sql_parser_debug "State = INIT" ;
	    $par_level = 0 ;
	    $com_level = 0 ;
	    @doldolstack = () ;
	    $l = $sql = $chunk = $rest = "" ;	 
	    @sql_list = () ;
	    $copy_table = $copy_field_list = $copy_rest = "" ;
	    @copy_data = @copy_data_tmp = () ;
	    $copy_field = "" ;
	    
	    $state = $states{SCAN} ;
	    last STATE_SWITCH ;
	} ;			# End of INIT state
	
	$state == $states{SCAN} && do {
	    sql_parser_debug "State = SCAN" ;
	  SCAN_STATE_SWITCH: {
	      ( ($l eq "") or ($l =~ /^\s*$/) or ($l =~ /^\s*--/) ) && do {
		  $l = <F> ;
		  unless ($l) {
		      $state = $states{DONE} ;
		      last SCAN_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  last SCAN_STATE_SWITCH ;
	      } ;

	      ( ($l =~ m/\s*copy\s+\"[\w_]+\"\s*(\([\w, "]+\))?\s*from\s+stdin\s*;/i) 
		or ($l =~ m/\s*copy\s+[\w_]+\s*(\([\w, "]+\))?\s*from\s+stdin\s*;/i) ) && do {
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

	$state == $states{IN_COMMENT} && do {
	    sql_parser_debug "State = IN_COMMENT" ;
	  IN_COMMENT_STATE_SWITCH: {
	      ( ($l eq "") or ($l =~ /^\s*$/) ) && do {
		  $l = <F> ;
		  unless ($l) {
		      sql_parser_debug "End of file detected during a comment." ;
		      $state = $states{ERROR} ;
		      last IN_COMMENT_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{IN_COMMENT} ;
		  last IN_COMMENT_STATE_SWITCH ;
	      } ;

	      ( ($l =~ m,\*/,) || ($l =~ m,/\*,) ) && do {
		  $l =~ s,.*?((/\*)|(\*/)),$1, ;
		  ($chunk, $rest) = ($l =~ /^(..)(.*)/) ;
		  
		  $l = $rest ;

		  if ($chunk eq '/*') {
		      $com_level += 1 ;
		  } else {
		      $com_level -= 1 ;
		  }

		  if ($com_level == 0) {
		      $state = $states{SQL_SCAN} ;
		      last IN_COMMENT_STATE_SWITCH ;
		  } else {
		      $state = $states{IN_COMMENT} ;
		      last IN_COMMENT_STATE_SWITCH ;
		  }
	      } ;

	      ( 1 ) && do {
		  $l = <F> ;
		  sql_parser_debug "Examining $l\n" ;
		  unless ($l) {
		      $state = $states{ERROR} ;
		      last IN_COMMENT_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{IN_COMMENT} ;
		  last IN_COMMENT_STATE_SWITCH ;
	      } ;

	  }			# IN_COMMENT_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_COMMENT state
	
	$state == $states{IN_SQL_COMMENT} && do {
	    sql_parser_debug "State = IN_SQL_COMMENT" ;
	  IN_SQL_COMMENT_STATE_SWITCH: {
	      ( ($rest eq "") or ($rest =~ /^\s*$/) ) && do {
		  $rest = <F> ;
		  unless ($rest) {
		      sql_parser_debug "End of file detected during a comment." ;
		      $state = $states{ERROR} ;
		      last IN_SQL_COMMENT_STATE_SWITCH ;
		  }
		  chomp $rest ;
		  
		  $state = $states{IN_SQL_COMMENT} ;
		  last IN_SQL_COMMENT_STATE_SWITCH ;
	      } ;

	      ( ($rest =~ m,\*/,) || ($rest =~ m,/\*,) ) && do {
		  $rest =~ s,.*?((/\*)|(\*/)),$1, ;
		  ($chunk, my $rest2) = ($rest =~ /^(..)(.*)/) ;
		  
		  $rest = $rest2 ;

		  if ($chunk eq '/*') {
		      $com_level += 1 ;
		  } else {
		      $com_level -= 1 ;
		  }

		  if ($com_level == 0) {
		      $state = $states{IN_SQL} ;
		      last IN_SQL_COMMENT_STATE_SWITCH ;
		  } else {
		      $state = $states{IN_SQL_COMMENT} ;
		      last IN_SQL_COMMENT_STATE_SWITCH ;
		  }
	      } ;

	      ( 1 ) && do {
		  $rest = <F> ;
		  unless ($rest) {
		      sql_parser_debug "End of file detected during a comment." ;
		      $state = $states{ERROR} ;
		      last IN_SQL_COMMENT_STATE_SWITCH ;
		  }
		  chomp $rest ;
		  
		  $state = $states{IN_SQL_COMMENT} ;
		  last IN_SQL_COMMENT_STATE_SWITCH ;
	      } ;

	  }			# IN_SQL_COMMENT_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_SQL_COMMENT state
	
	$state == $states{SQL_SCAN} && do {
	    sql_parser_debug "State = SQL_SCAN" ;
	  SQL_SCAN_STATE_SWITCH: {
	      ( ($l eq "") or ($l =~ /^\s*$/) or ($l =~ /^--/) ) && do {
		  $l = <F> ;
		  unless ($l) {
		      sql_parser_debug "End of file detected during an SQL statement." ;
		      $state = $states{ERROR} ;
		      last SQL_SCAN_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{SQL_SCAN} ;
		  last SQL_SCAN_STATE_SWITCH ;
	      } ;

	      ($l =~ m,^\s*/\*,) && do {
		  $l =~ s,^\s*/\*,, ;
		  $com_level = 1 ;
		  $state = $states{IN_COMMENT} ;
		  last SQL_SCAN_STATE_SWITCH ;
	      } ;
	      
	      ($l =~ m,^(.*?)\$([\w]*)\$,) && do {
		  $sql .= "$1\$$2\$" ;
		  push @doldolstack, $2 ;
		  sql_parser_debug "---$sql---$doldolstack[0]---" ;
		  $l =~ s,^(.*?)\$[\w]*\$,, ;
		  $state = $states{IN_DOLDOL} ;
		  last SQL_SCAN_STATE_SWITCH ;
	      } ;
	      
	      ( 1 ) && do {
		  ($chunk, $rest) = ($l =~ m,^([^()\';-]*)(.*),) ;
		  $sql .= $chunk ;
		  
		  $state = $states{IN_SQL} ;
		  last SQL_SCAN_STATE_SWITCH ;
	      } ;
	      
	  }			# SQL_SCAN_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of SQL_SCAN state
	
	$state == $states{IN_COMMENT} && do {
	    sql_parser_debug "State = IN_COMMENT" ;
	  IN_COMMENT_STATE_SWITCH: {
	      ( ($l eq "") or ($l =~ /^\s*$/) ) && do {
		  $l = <F> ;
		  unless ($l) {
		      sql_parser_debug "End of file detected during a comment." ;
		      $state = $states{ERROR} ;
		      last IN_COMMENT_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{IN_COMMENT} ;
		  last IN_COMMENT_STATE_SWITCH ;
	      } ;

	      ( ($l !~ m,\*/,) || ($l !~ m,/\*,) ) && do {
		  $l =~ s,.*?((/\*)|(\*/)),$1, ;
		  ($chunk, $rest) = ($l =~ /^(..)(.*)/) ;
		  
		  $l = $rest ;

		  if ($chunk eq '/*') {
		      $com_level += 1 ;
		  } else {
		      $com_level -= 1 ;
		  }

		  if ($com_level == 0) {
		      $state = $states{SQL_SCAN} ;
		      last IN_COMMENT_STATE_SWITCH ;
		  } else {
		      $state = $states{IN_COMMENT} ;
		      last IN_COMMENT_STATE_SWITCH ;
		  }
	      } ;

	      ( 1 ) && do {
		  $l = <F> ;
		  unless ($l) {
		      sql_parser_debug "End of file detected during a comment." ;
		      $state = $states{ERROR} ;
		      last IN_COMMENT_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{IN_COMMENT} ;
		  last IN_COMMENT_STATE_SWITCH ;
	      } ;

	  }			# IN_COMMENT_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_COMMENT state
	
	$state == $states{IN_SQL} && do {
	    sql_parser_debug "State = IN_SQL" ;
	    
	  IN_SQL_STATE_SWITCH: {
	      ($rest =~ m,^\s*/\*,) && do {
		  $rest =~ s,^\s*/\*,, ;
		  $com_level = 1 ;
		  $state = $states{IN_SQL_COMMENT} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;
	      

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
		  sql_parser_debug "Detected ')' without any matching '('." ;
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
		  sql_parser_debug "Detected ';' within a parenthesis." ;
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
		  sql_parser_debug "Unknown event in IN_SQL state" ;
		  $state = $states{ERROR} ;
		  last IN_SQL_STATE_SWITCH ;
	      } ;
	  }			# IN_SQL_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_SQL state

	$state == $states{END_SQL} && do {
	    sql_parser_debug "State = END_SQL" ;
	  END_SQL_STATE_SWITCH: {
	      ($sql =~ /^\s*$/) && do {
		  $sql = "" ;
		  $l = $rest ;

		  $state = $states{SCAN} ;
		  last END_SQL_STATE_SWITCH ;
	      } ;

	      ( 1 ) && do {
		  push @sql_list, $sql ;
		  sql_parser_debug ("Found SQL $sql\n") ;
		  $sql = "" ;
		  $l = $rest ;

		  $state = $states{SCAN} ;
		  last END_SQL_STATE_SWITCH ;
	      } ;

	  }			# END_SQL_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of END_SQL state

	$state == $states{QUOTE_SCAN} && do {
	    sql_parser_debug "State = QUOTE_SCAN" ;
	  QUOTE_SCAN_STATE_SWITCH: {
	      ($rest eq "") && do {
		  $sql .= "\n" ;
		  $l = <F> ;
		  unless ($l) {
		      sql_parser_debug "Detected end of file inside a quoted string." ;
		      $state = $states{ERROR} ;
		      last QUOTE_SCAN_STATE_SWITCH ;
		  }
		  chomp $l ;
		  $rest = $l ;
		  
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
	    sql_parser_debug "State = IN_QUOTE" ;
	  IN_QUOTE_STATE_SWITCH: {
	      ($rest =~ /^\'/) && do {
		  $sql .= q/'/ ;
		  $rest = substr $rest, 1 ;
		  $l = $rest ;
		  
		  $state = $states{SQL_SCAN} ;
		  last IN_QUOTE_STATE_SWITCH ;
	      } ;

	      ($rest =~ /^\\\'/) && do {
		  $sql .= q/''/ ;
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

	$state == $states{IN_DOLDOL} && do {
	    sql_parser_debug "State = IN_DOLDOL" ;
	  IN_DOLDOL_STATE_SWITCH: {
	      my $cur = $doldolstack[0] ;

	      ($l =~ m,^(.*?)\$([\w]*)\$,) && do {
		  $sql .= "$1\$$2\$" ;
		  my $found = $2 ;
		  if ($found eq $cur) {
		      pop @doldolstack ;
		      if ($#doldolstack >= 0) {
			  $state = $states{IN_DOLDOL} ;
		      } else {
			  $rest = $l ;
			  $rest =~ s,^(.*?)\$[\w]*\$,, ;
			  sql_parser_debug "Exiting DOLDOL for $cur (current = $sql) ($rest)" ;
			  $state = $states{SQL_SCAN} ;
		      }
		  } else {
		      push @doldolstack, $found ;
		  }
		  $l =~ s,^(.*?)\$[\w]*\$,, ;
		  last IN_DOLDOL_STATE_SWITCH ;
	      } ;

	      ( 1 ) && do {
		  $sql .= $l."\n" ;

		  $l = <F> ;
		  unless ($l) {
		      sql_parser_debug "Detected end of file within a dollar-quoted string." ;
		      $state = $states{ERROR} ;
		      last IN_DOLDOL_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{IN_DOLDOL} ;
		  last IN_DOLDOL_STATE_SWITCH ;
	      } ;

	  }			# IN_DOLDOL_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of IN_DOLDOL state

	$state == $states{START_COPY} && do {
	    sql_parser_debug "State = START_COPY" ;
	  START_COPY_STATE_SWITCH: {
	      ($l =~ m/\s*copy\s+\"[\w_]+\"\s*(\([\w, "]+\))?\s*from\s+stdin\s*;/i) && do {
		  ($copy_table, $copy_field_list, undef, $copy_rest) = ($l =~ /\s*copy\s+\"([\w_]+)\"\s*((\([\w, "]+\))?)\s*from\s+stdin\s*;(.*)/i) ;
		  $copy_field_list =~ s/^\s+//;
		  $copy_field_list =~ s/\s+$//;
		  $copy_field_list = ' '.$copy_field_list unless $copy_field_list eq '';
		  $l = <F> ;
		  unless ($l) {
		      sql_parser_debug "Detected end of file within a COPY statement." ;
		      $state = $states{ERROR} ;
		      last START_COPY_STATE_SWITCH ;
		  }
		  chomp $l ;
		  
		  $state = $states{IN_COPY} ;
		  last START_COPY_STATE_SWITCH ;
	      } ;

	      ($l =~ m/\s*copy\s+[\w_]+\s*(\([\w, "]+\))?\s*from\s+stdin\s*;/i) && do {
		  ($copy_table, $copy_field_list, undef, $copy_rest) = ($l =~ /\s*copy\s+([\w_]+)\s*((\([\w, "]+\))?)\s*from\s+stdin\s*;(.*)/i) ;
		  $copy_field_list =~ s/^\s+//;
		  $copy_field_list =~ s/\s+$//;
		  $copy_field_list = ' '.$copy_field_list unless $copy_field_list eq '';		      
		  $l = <F> ;
		  unless ($l) {
		      sql_parser_debug "Detected end of file within a COPY statement." ;
		      $state = $states{ERROR} ;
		      last START_COPY_STATE_SWITCH ;
		  }
		  chomp $l ;

		  $state = $states{IN_COPY} ;
		  last START_COPY_STATE_SWITCH ;
	      } ;
	      
	      ( 1 ) && do {
		  sql_parser_debug "Unknown event in START_COPY state." ;
		  $state = $states{ERROR} ;
		  last START_COPY_STATE_SWITCH ;
	      } ;

	  }			# START_COPY_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of START_COPY state

	$state == $states{IN_COPY} && do {
	    sql_parser_debug "State = IN_COPY" ;
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
			  $copy_field =~ s/\'/\'\'/g ;
			  $copy_field = "'" . $copy_field . "'" ;
		      }
		      push @copy_data, $copy_field ;
		  }
		  $sql = "INSERT INTO \"$copy_table\"$copy_field_list VALUES (" ;
		  $sql .= join (", ", @copy_data) ;
		  $sql .= ")" ;
		  push @sql_list, $sql ;
		  $l = <F> ;
		  unless ($l) {
		      sql_parser_debug "Detected end of file within a COPY statement." ;
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
	    sql_parser_debug "State = DONE" ;
	    last STATE_SWITCH ;
	} ;			# End of DONE state

	$state == $states{ERROR} && do {
	    sql_parser_debug "State = ERROR" ;
	    sql_parser_debug "Reached the ERROR state.  Dying." ;
	    die "State machine is buggy." ;
	    
	    last STATE_SWITCH ;
	} ;			# End of ERROR state

	( 1 ) && do {
	    sql_parser_debug "State machine went in an unknown state...  Redirecting to ERROR." ;
	    $state = $states{ERROR} ;
	    last STATE_SWITCH ;
	} ;

    }				# STATE_SWITCH
  }				# STATE_LOOP

    close F ;
    return \@sql_list ;
}

sub sql_parser_debug ( $ ) {
    return ;
    my $v = shift ;
    chomp $v ;
    print STDERR "$v\n" ;
}

1 ;
