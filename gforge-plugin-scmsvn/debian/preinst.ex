#! /bin/sh
# preinst script for gforge-plugin-scmsvn
#
# see: dh_installdeb(1)

set -e

# summary of how this script can be called:
#        * <new-preinst> `install'
#        * <new-preinst> `install' <old-version>
#        * <new-preinst> `upgrade' <old-version>
#        * <old-preinst> `abort-upgrade' <new-version>
#
# for details, see http://www.debian.org/doc/debian-policy/ or
# the debian-policy package


case "$1" in
    install|upgrade)
#        if [ "$1" = "upgrade" ]
#        then
#            start-stop-daemon --stop --quiet --oknodo  \
#                --pidfile /var/run/gforge-plugin-scmsvn.pid  \
#                --exec /usr/sbin/gforge-plugin-scmsvn 2>/dev/null || true
#        fi
    ;;

    abort-upgrade)
    ;;

    *)
        echo "preinst called with unknown argument \`$1'" >&2
        exit 1
    ;;
esac

# dh_installdeb will replace this with shell code automatically
# generated by other debhelper scripts.

#DEBHELPER#

exit 0


