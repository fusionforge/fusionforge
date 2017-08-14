#!/bin/bash
while [ -z "`netstat -tln | grep 5432`" ]; do
  echo 'Waiting for PostgreSQL to start ...'
  sleep 1
done
echo 'PostgreSQL started.'
/usr/local/share/fusionforge/post-install.d/db/db.sh configure
cd /opt/sources/fusionforge/src
for pluginname in blocks compactpreview gravatar headermenu mediawiki message moinmoin phptextcaptcha repositoryapi scmgit scmhook scmsvn scmhg taskboard webanalytics; do
    make post-install-plugin-${pluginname}
done
