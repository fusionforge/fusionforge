# $Id$
#
# A state machine to turn an SQL file into list of requests
# (represented in an array of strings)
#
### AUTHOR/COPYRIGHT
# This file is copyright 2002 Roland Mas <99.roland.mas@aist.enst.fr>.
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
# * Be more permissive with quotes: 'copy table from stdin;' won't work
#   whereas 'copy "table" from stdin;' will.  This is unfair, and should be
#   fixed.
# * Make sure the output of pg_dump is interpreted the way it should.
# * Ditto for he output of mysqldump.

sub sql_parser_debug ( $ ) {
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
		  'ERROR' => 9,
		  'DONE' => 999) ;
    my ($state, $l, $par_level, $chunk, $rest, $sql, @sql_list, $copy_table, $copy_rest, @copy_data, @copy_data_tmp, $copy_field) ;

    # Init the state machine

    $state = $states{INIT} ;
    
    # my $n = 0 ;
    
  STATE_LOOP: while ($state != $states{DONE}) { # State machine main loop
      # sql_parser_debug "STATE_LOOP: state = $state" ;
    STATE_SWITCH: {		# State machine step processing
	$state == $states{INIT} && do {
	    # sql_parser_debug "State = INIT" ;
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
	    # sql_parser_debug "State = SCAN" ;
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
	    # sql_parser_debug "State = SQL_SCAN" ;
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
	    # sql_parser_debug "State = IN_SQL" ;
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
	    # sql_parser_debug "State = END_SQL" ;
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
	    # sql_parser_debug "State = QUOTE_SCAN" ;
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
		  # sql_parser_debug "chunk = $chunk" ;
		  # sql_parser_debug "rest = $rest" ;
		  $sql .= $chunk ;
		  
		  $state = $states{IN_QUOTE} ;
		  last QUOTE_SCAN_STATE_SWITCH ;
	      } ;

	  }			# QUOTE_SCAN_STATE_SWITCH
	    last STATE_SWITCH ;
	} ;			# End of QUOTE_SCAN state
	
	$state == $states{IN_QUOTE} && do {
	    # sql_parser_debug "State = IN_QUOTE" ;
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
	    # sql_parser_debug "State = START_COPY" ;
	  START_COPY_STATE_SWITCH: {
	      ($l =~ m/\s*copy\s+\"[\w_]+\"\s+from\s+stdin\s*;/i) && do {
		  ($copy_table, $copy_rest) = ($l =~ /\s*copy\s+\"([\w_]+)\"\s+from\s+stdin\s*;(.*)/i) ;
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

	      ($l =~ m/\s*copy\s+[\w_]+\s+from\s+stdin\s*;/i) && do {
		  ($copy_table, $copy_rest) = ($l =~ /\s*copy\s+([\w_]+)\s+from\s+stdin\s*;(.*)/i) ;
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
	    # sql_parser_debug "State = IN_COPY" ;
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
	    # sql_parser_debug "State = DONE" ;
	    last STATE_SWITCH ;
	} ;			# End of DONE state

	$state == $states{ERROR} && do {
	    # sql_parser_debug "State = ERROR" ;
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

1 ;
