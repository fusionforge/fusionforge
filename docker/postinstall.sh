#!/bin/bash
/usr/local/share/fusionforge/post-install.d/db/db.sh configure
cd /opt/sources/fusionforge/src
for pluginname in blocks message scmgit scmsvn taskboard; do
    make post-install-plugin-${pluginname}
done
