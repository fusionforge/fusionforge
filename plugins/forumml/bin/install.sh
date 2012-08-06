#! /bin/sh
/usr/share/gforge/plugins/forumml/bin/installFF.sh
/usr/share/gforge/plugins/forumml/bin/ml_arch_2_DBFF.pl
invoke-rc.d exim4 restart || true
invoke-rc.d mailman restart || true
