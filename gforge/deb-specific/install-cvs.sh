#!/bin/bash

if [ $# != 1 ]
then
	echo "Usage: $0 {configure|purge}"
	exit 1
else
	if [ "$1" = "configure" ]
	then
		# Setup our own CVS
		echo "Modifying inetd for cvs server"
		echo "CVS usual config is changed for sourceforge one"
		# First, dedupe the commented lines
		update-inetd --remove  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	/usr/lib/sourceforge/bin/cvs-pserver"
		update-inetd --comment-chars "#SF_WAS_HERE#" --enable cvspserver
		# Then, insinuate ourselves
		update-inetd --comment-chars "#SF_WAS_HERE#" --disable cvspserver
		update-inetd --add  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	/usr/lib/sourceforge/bin/cvs-pserver"
	else
		if [ "$1" = "purge" ]
		then
			echo "Purging inetd for cvs server"
			# echo "You should dpkg-reconfigure cvs to use std install"
			update-inetd --remove  "cvspserver	stream	tcp	nowait.400	root	/usr/sbin/tcpd	/usr/lib/sourceforge/bin/cvs-pserver"
			update-inetd --comment-chars "#SF_WAS_HERE#" --enable cvspserver
		else
			echo "Usage: $0 {configure|purge}"
			exit 1
		fi
	fi
fi
