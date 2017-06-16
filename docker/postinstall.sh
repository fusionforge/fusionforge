#!/bin/bash
/usr/local/share/fusionforge/post-install.d/db/db.sh configure
cd /opt/sources/fusionforge/src
for pluginname in blocks compactpreview gravatar headermenu mediawiki message moinmoin repositoryapi scmgit scmhook scmsvn taskboard webanalytics; do
    make post-install-plugin-${pluginname}
done
