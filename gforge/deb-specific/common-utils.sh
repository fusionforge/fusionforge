#! /bin/sh
# 
# $Id$
#
# Shell functions used throughout the sourceforge-* packages
#
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

###
# Functions to propose changes in configuration files
###
# Replace an exsting file with the proposed one
replace_file () {
    file=$1
    cp $file ${file}.sourceforge-old
    mv ${file}.sourceforge-new $file
}

# Propose a replacement to the user
propose_update () {
    file=$1
    if diff -q ${file} ${file}.sourceforge-new 2>&1 > /dev/null ; then
	# Old file and new file are identical
	rm -f ${file}.sourceforge-new
    else
	db_fset sourceforge/shared/replace_file_install seen false
	db_subst sourceforge/shared/replace_file_install file $file
	db_input high sourceforge/shared/replace_file_install || true
	db_go || true
	db_get sourceforge/shared/replace_file_install || true
	case "$RET" in
	    "true")
		echo >&2 "Replacing file $file with changed version"
		replace_file $file
		;;
	    "false")
		db_fset sourceforge/shared/file_changed seen false
		db_subst sourceforge/shared/file_changed file $file
		db_input high sourceforge/shared/file_changed || true
		db_go || true
		;;
	esac
    fi
}

###
# Functions to handle the main Sourceforge confguration file
###
mainconffile=/etc/sourceforge/sourceforge.conf
# Create the main configuraion file (unless it already exists)
create_mainconffile () {
    if [ ! -e $mainconffile ] ; then
	touch $mainconffile
	chmod 600 $mainconffile
    fi
}

# Update it for the variables received as parameters
update_mainconffile () {
    for i in $@ ; do
	if ! grep -q "^$i=" $mainconffile ; then
	    db_get sourceforge/$i
	    echo "$i=$RET" >> $mainconffile
	fi
    done
    
}

# Delete the main configuration file
delete_mainconffile () {
    rm -f $mainconffile
}
