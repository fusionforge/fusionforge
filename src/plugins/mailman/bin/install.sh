#! /bin/sh
PATH=/usr/share/gforge/bin:/usr/share/fusionforge/bin:$PATH
export PATH
[ -d /var/lib/mailman/lists/mailman/ ] || /usr/sbin/newlist -q mailman postmaster@`hostname -f` `forge_get_config database_password`
