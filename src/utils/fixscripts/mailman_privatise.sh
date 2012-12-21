#!/bin/mksh
#-
# Mailman mass-modification tool to make mailing lists private
#
# Copyright © 2012
#	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
# All rights reserved.
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

me=$0

usage() {
	print -u2 "Usage: ${me##*/} [-r user@host] -a | listname ..."
	print -u2 "The argument to -r is passed unquoted to ssh."
	exit ${1:-1}
}

aflag=0
rflag=""
while getopts "ahr:" ch; do
	case $ch {
	(a)	aflag=1 ;;
	(+a)	aflag=0 ;;
	(h|+h)	usage 0 ;;
	(r)	rflag=$OPTARG ;;
	(*)	usage ;;
	}
done
shift $((OPTIND - 1))
set -A args -- "$@"

if [[ -n $rflag ]]; then
	set -A cmd -- /bin/mksh -s --
	(( aflag )) && set -A cmd+ -- -a
	# does not work yet: set -A cmd+ -- "${x[@]@Q}"
	for x in "${args[@]}"; do
		set -A cmd+ -- "${x@Q}"
	done
	exec ssh $rflag "${cmd[@]}" <"$me"
fi

nargs=${#args[*]}
(( aflag || nargs )) || usage
(( aflag && nargs )) && usage

if (( aflag )); then
	set -A args -- $(/usr/lib/mailman/bin/list_lists -b)
	nargs=${#args[*]}
	if (( !nargs )); then
		print No lists to process.
		exit 0
	fi
fi

rv=0
for x in "${args[@]}"; do
	print -nr "Processing list ${x}..."
	if ! cf=$(/usr/lib/mailman/bin/config_list -o - "$x"); then
		print " failed to read: $?"
		(( rv |= 1 ))
		continue
	fi
	if [[ -z $cf ]]; then
		print " failed to read: empty configuration"
		(( rv |= 2 ))
		continue
	fi
	if ! et=$(/usr/lib/mailman/bin/config_list -c -i /dev/stdin \
	    "$x" <<<"$cf" 2>&1); then
		print " failed to read: invalid configuration"
		sed 's/^/	/' <<<"$et"
		(( rv |= 4 ))
		continue
	fi
	IFS=$'\n'
	set -A cfa -- $cf
	IFS=$' \t\n'
	cfo=
	for cfl in "${cfa[@]}"; do
		case $cfl {
		(advertised*([	 ])=*)
			cfl='advertised = 0'
			;;
		(subscribe_policy*([	 ])=*)
			cfl='subscribe_policy = 3'
			;;
		(private_roster*([	 ])=*([	 ])2)
			;;
		(private_roster*([	 ])=*)
			cfl='private_roster = 1'
			;;
		(archive_private*([	 ])=*)
			cfl='archive_private = 1'
			;;
		}
		cfo+=$cfl$'\n'
	done
	if ! et=$(/usr/lib/mailman/bin/config_list -i /dev/stdin \
	    "$x" <<<"$cfo" 2>&1); then
		print " failed to configure: $?"
		sed 's/^/	/' <<<"$et"
		(( rv |= 8 ))
		continue
	fi
	print " done."
done
exit $rv
