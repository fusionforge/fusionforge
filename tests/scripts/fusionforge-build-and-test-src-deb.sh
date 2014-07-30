#!/bin/sh
. tests/scripts/common-functions
. tests/scripts/common-vm

export FORGE_HOME=/opt/gforge
export HOST=$1
case $HOST in
    debian7.local)
	export DIST=wheezy
	VM=debian7
	;;
    debian8.local)
	export DIST=jessie
	VM=debian8
	;;
    *)
	export DIST=jessie
	VM=debian8
	;;
esac	

export FILTER="DEBDebian70TestsSRC.php"

get_config $@
prepare_workspace
destroy_vm -t $VM $@
start_vm_if_not_keeped -t $VM $@

setup_debian_3rdparty_repo

ssh root@$HOST "apt-get update"

echo "Sync code on root@$HOST:$FORGE_HOME"
ssh root@$HOST "[ -d $FORGE_HOME ] || mkdir -p $FORGE_HOME"
rsync -a --delete src/ root@$HOST:$FORGE_HOME/
rsync -a --delete tests/ root@$HOST:$FORGE_HOME/tests/

echo "Run Install on $HOST"
ssh root@$HOST "$FORGE_HOME/install-ng --auto --reinit"

# Dump database
echo "Dump freshly installed database"
ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"

config_path=$(ssh root@$HOST forge_get_config config_path)

echo "Set use_ssl=no"
ssh root@$HOST "(echo [core];echo use_ssl=no;echo use_fti=no) > $config_path/config.ini.d/zzz-zbuildbot.ini"
ssh root@$HOST "(echo [moinmoin];echo use_frame=no) >> $config_path/config.ini.d/zzz-buildbot.ini"
ssh root@$HOST "(echo [mediawiki];echo unbreak_frames=yes) >> $config_path/config.ini.d/zzz-buildbot.ini"

# Stop cron
echo "Stop cron daemon"
ssh root@$HOST "service crond stop" || true

# Install selenium
ssh root@$HOST "apt-get -y install selenium"

# Transfer hudson config
ssh root@$HOST "cat > $FORGE_HOME/tests/config/phpunit" <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

# Run tests
retcode=0
echo "Run phpunit test on $HOST in $FORGE_HOME"
ssh root@$HOST "$FORGE_HOME/tests/func/vncxstartsuite.sh $FILTER"
retcode=$?
rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/

stop_vm_if_not_keeped -t $VM $@
exit $retcode
