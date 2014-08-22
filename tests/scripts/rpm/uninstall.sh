#!/bin/bash
# Aggressive desinstall for testing a clean re-install
yum remove -y 'fusionforge*'
rm -rf /var/lib/pgsql/ /usr/share/fusionforge/ /etc/fusionforge/ /var/lib/fusionforge/
