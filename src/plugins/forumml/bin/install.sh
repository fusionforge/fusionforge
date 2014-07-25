#! /bin/sh
$(forge_get_config plugins_path)/forumml/bin/installFF.sh
$(forge_get_config plugins_path)/forumml/bin/ml_arch_2_DBFF.pl
invoke-rc.d exim4 restart || true
invoke-rc.d mailman restart || true
