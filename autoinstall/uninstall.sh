#!/bin/bash
# Aggressive desinstall for testing a clean re-install
if [ -e /etc/debian_version ]; then
    aptitude purge ~nforge ~npostgres ~nnss-pgsql ~napache2 ~nphp ~npostfix ~nexim4

elif [[ ! -z `cat /etc/os-release | grep "SUSE"` ]]; then
    zypper remove -y 'fusionforge*' postgresql-server
    rm -f /etc/cron.d/fusionforge-*
else
    yum remove -y 'fusionforge*' postgresql
    rm -f /etc/cron.d/fusionforge-*
fi
service postgresql stop
rm -rf /usr/share/fusionforge /usr/local/share/fusionforge /etc/fusionforge /var/lib/fusionforge
rm -rf /root/dump /var/lib/postgresql*/ /var/lib/pgsql*/
