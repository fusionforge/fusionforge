#!/usr/bin/pgtclsh

package require cmdline

set dbName "gforge"
set dbHost "gforge02"
set dbUser "gforge"
set dbPassword "db"
set fileEncoding "utf-8"


set progname [::cmdline::getArgv0]

set options {
	{U.arg "" "User name"}
	{p.arg "" "password" }
	{h.arg "" "host name" }
	{e.arg "utf-8" "Output file encoding" }
}

#	{?  "Help" }


set usage ": [::cmdline::getArgv0] \[options\] database trackerID"

set isError [catch {array set cmdArgs [::cmdline::getoptions argv $options $usage]} msg]

if { $isError } {
	puts $msg
	exit 1
}

if { [llength $argv] <= 0 } {
	puts stderr "$progname: Missing database name."
	exit 3
}

if { [llength $argv] <= 1 } {
	puts stderr "$progname: Missing tracker id."
	exit 4
}

if { $cmdArgs(U) == "" } {
	puts stderr "$progname: Missing username."
	exit 2
}

set dbHost $cmdArgs(h)

if { $cmdArgs(h) == "" } {
	set dbHost "localhost"
}

if { $cmdArgs(e) != "" } {
	set fileEncoding $cmdArgs(e)
}


set dbUser $cmdArgs(U)
set dbPassword $cmdArgs(p)



set trackerId [lindex $argv 1]

set dbName [lindex $argv 0]

set dbConn ""

set connStr "dbname=$dbName host=$dbHost port=5432 user=$dbUser password=$dbPassword"

set isError [catch {
	set dbConn [pg_connect -conninfo $connStr]
} msg]

if { $isError } {
	puts "$progname: connection to database failed."
	puts stderr "--- Details\n$msg\n---"
	exit 5
}

array set typeNameCache {}

proc getTypeName { dbConn typeoid } {
	set toReturn ""
	set query "select typname from pg_type where oid = $typeoid";
	set result [pg_exec $dbConn $query]
	set row [pg_result $result -getTuple 0]
	set toReturn [lindex $row 0]
	pg_result $result -clear
	return $toReturn
}

proc getSQLValueFormat { dbConn typeoid value } {
	global typeNameCache
	set toReturn ""

	set allTypes [array get typeNameCache]

	if { [lsearch $allTypes $typeoid] < 0 } {
		set typeName [getTypeName $dbConn $typeoid]
		array set typeNameCache [list $typeoid $typeName]
	} else {
		set typeName $typeNameCache($typeoid)
	}

	switch -glob $typeName {
		*char* -
		*text* {
#			set tmp [string map {\' \' é e è e à a ç c ê e ô o â a} $value]
			set tmp [string map {\' \'} $value]
			set tmp [string map {' "\\'"} $tmp]
#			set tmp [string map {\\\\ \\ § paragraphe } $tmp]
			set tmp [string map {\\\\ \\} $tmp]
			set toReturn "'$tmp'"
		}
		
		default {
			set toReturn $value
		}
	}
	return $toReturn
}


proc getColumnTypeInfo { types colName } {
	set toReturn {}
	foreach type $types {
		if { [lindex $type 0] == $colName } {
			set toReturn $type
			break
		}
	}
	return $toReturn
}

proc generateInsert { sqlFile dbConn table query} {
	set result [pg_exec $dbConn $query]
	set colNames [pg_result $result -attributes]
	set colTypes [pg_result $result -lAttributes]
	pg_result $result -assign tuples
	set nbTuples [pg_result $result -numTuples]

	for {set idx 0} { $idx < $nbTuples} { incr idx} {
		set fields {}
		set values {}
		foreach colName $colNames {
			set colTypeInfo [getColumnTypeInfo $colTypes $colName]
			lappend fields $colName
			lappend values [getSQLValueFormat $dbConn [lindex $colTypeInfo 1] $tuples($idx,$colName)]
		}
		
		puts $sqlFile "insert into $table \([join $fields ,]\) values \([join $values ,]\);\n"
		
	}
	pg_result $result -clear
}

proc getColumnAsList {dbConn query colName} {
	set toReturn {}
	set result [pg_exec $dbConn $query]

	set nbRows [pg_result $result -numTuples]
	for {set idx 0} { $idx < $nbRows } { incr idx } {
		pg_result $result -tupleArray $idx row
		lappend toReturn $row($colName)
		unset row
	}
	pg_result $result -clear
	return $toReturn
}

set sqlFile [open "${trackerId}-export.sql" "w"]
set verifyFile [open "${trackerId}-validate.sql" "w"]
set deleteFile [open "${trackerId}-delete.sql" "w"]

fconfigure $sqlFile -encoding $fileEncoding
fconfigure $verifyFile -encoding $fileEncoding
fconfigure $deleteFile -encoding $fileEncoding



puts "Generating ${trackerId}-export.sql ..."


# Artifact list of this tracker
set query "select artifact_id from artifact where group_artifact_id = $trackerId order by artifact_id;"
set artifactIdList [getColumnAsList $dbConn $query artifact_id]

# Extra field list of this tracker
set query "select extra_field_id from artifact_extra_field_list where group_artifact_id = $trackerId order by extra_field_id;"
set extraFieldIdList [getColumnAsList $dbConn $query extra_field_id]


#
# Generating artifacts
#

set query "select * from artifact_group_list where group_artifact_id = $trackerId;"
generateInsert $sqlFile $dbConn artifact_group_list $query

set query "select * from artifact where group_artifact_id = $trackerId;"
generateInsert $sqlFile $dbConn artifact $query

set query "select * from artifact_history where artifact_id in ([join $artifactIdList ,]);"
generateInsert $sqlFile $dbConn artifact_history $query

#
# Generating extra fields
#

set query "select * from artifact_extra_field_list where extra_field_id in ([join $extraFieldIdList ,]);"
generateInsert $sqlFile $dbConn artifact_extra_field_list $query

set query "select * from artifact_extra_field_elements where extra_field_id in ([join $extraFieldIdList ,]);"
generateInsert $sqlFile $dbConn artifact_extra_field_elements $query

set query "select * from artifact_extra_field_data where artifact_id in ([join $artifactIdList ,]);"
generateInsert $sqlFile $dbConn artifact_extra_field_data $query

set query "select * from artifact_monitor where artifact_id in ([join $artifactIdList ,]);"
generateInsert $sqlFile $dbConn artifact_monitor $query

set query "select * from artifact_perm where group_artifact_id = $trackerId;"
generateInsert $sqlFile $dbConn artifact_perm $query

set query "select * from artifact_file where artifact_id in ([join $artifactIdList ,]);"
generateInsert $sqlFile $dbConn artifact_file $query


puts "Generating ${trackerId}-validate.sql ..."
puts $verifyFile "-- Validation"

set query "select id from artifact_history where artifact_id in ([join $artifactIdList ,]);"
set artifactHistoryIdList [getColumnAsList $dbConn $query id]

puts $verifyFile "select * from artifact_history where id in ([join $artifactHistoryIdList ,]);"

set query "select count(*) as extra_field_id_count from artifact_extra_field_list where group_artifact_id = $trackerId;"
puts $verifyFile $query

set query "select count(*) as extrafield_elt_count from artifact_extra_field_elements where extra_field_id in ([join $extraFieldIdList ,]);"
puts $verifyFile $query


set query "select count(*) as extrafield_data_count from artifact_extra_field_data where artifact_id in ([join $artifactIdList ,]);"
puts $verifyFile $query

set query "select count(*) as artifact_monitor_count from artifact_monitor where artifact_id in ([join $artifactIdList ,]);"
puts $verifyFile $query

set query "select count(*) as artifact_perm_count from artifact_perm where group_artifact_id = $trackerId;"
puts $verifyFile $query

set query "select count(*) as artifact_file_count from artifact_file where artifact_id in ([join $artifactIdList ,]);"
puts $verifyFile $query


puts "Generating ${trackerId}-delete.sql ..."

set query "delete from artifact_group_list where group_artifact_id = $trackerId;"
puts $deleteFile $query

puts $deleteFile "delete from artifact_history where id in ([join $artifactHistoryIdList ,]);"

puts $deleteFile "delete from artifact where group_artifact_id = $trackerId;"

set query "delete from artifact_extra_field_list where group_artifact_id = $trackerId;"
puts $deleteFile $query

set query "delete from artifact_extra_field_elements where extra_field_id in ([join $extraFieldIdList ,]);"
puts $deleteFile $query


set query "delete from artifact_extra_field_data where artifact_id in ([join $artifactIdList ,]);"
puts $deleteFile $query

set query "delete from artifact_monitor where artifact_id in ([join $artifactIdList ,]);"
puts $deleteFile $query

set query "delete from artifact_perm where group_artifact_id = $trackerId;"
puts $deleteFile $query

set query "delete from artifact_file where artifact_id in ([join $artifactIdList ,]);"
puts $deleteFile $query


puts "done."


