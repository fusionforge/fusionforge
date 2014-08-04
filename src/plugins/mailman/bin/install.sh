#! /bin/sh
PATH=$(forge_get_config binary_path):$PATH
export PATH
[ -d /var/lib/mailman/lists/mailman/ ] || /usr/sbin/newlist -q mailman postmaster@`hostname -f` `forge_get_config database_password`
