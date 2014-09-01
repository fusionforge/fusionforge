#!/bin/bash
# Aggressive desinstall for testing a clean re-install
aptitude purge ~nforge ~npostgres ~nnss-pgsql ~napache2 ~nphp ~npostfix ~nexim4
service postgresql stop
rm -rf /usr/share/fusionforge /usr/local/share/fusionforge /etc/fusionforge /var/lib/fusionforge
rm -rf /root/dump /var/lib/postgresql*/
