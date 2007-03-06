#!/usr/local/bin/ruby

# This is a utility to delete the "dead" columns
# from various tables in GForge.  When run,
# it queries all the tables in the database, checks
# all their columns, and deletes the columns that 
# start with the word "dead".  Then it runs vacuumdb
# to reclaim the space.
#
# Usage: ./dead_column_cleaner.rb
# 

USER_STR = "-U gforge gforge"

tables = []
`psql -t -q #{USER_STR} -c "\dt"`.split("\n").each {|line|
	tables << line.split("|")[1].delete(" ")
}

tables.each {|t| 
	puts "Checking table " + t
	cmd = "psql -t -q #{USER_STR} -c "
	cmd += "\"\\d #{t}\""
	`#{cmd}`.split("\n").each {|line|
		col = line.split("|")[0].delete(" ")
		if col.index("dead") == 0
			puts "Removing column " + col + " from table " + t
			cmd = "psql #{USER_STR} -c "
			cmd += "\"alter table #{t} drop column #{col}\""
			puts cmd
			`#{cmd}`
		end
	}	
}

`vacuumdb #{USER_STR}`
