#!/bin/bash
# Aggressive desinstall for testing a clean re-install
aptitude purge ~nforge ~npostgres ~nnss-pgsql ~napache2 ~nphp ~npostfix ~nexim4 && rm -rf /usr/share/gforge/ /etc/fusionforge/
