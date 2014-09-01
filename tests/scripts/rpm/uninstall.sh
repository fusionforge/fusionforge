#!/bin/bash
# Aggressive desinstall for testing a clean re-install
yum remove -y 'fusionforge*'
service postgresql stop
rm -rf  /usr/share/fusionforge/ /etc/fusionforge/ /var/lib/fusionforge/
rm -rf  /root/dump /var/lib/pgsql*/
