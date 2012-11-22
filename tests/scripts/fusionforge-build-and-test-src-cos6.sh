#!/bin/sh -e
. tests/scripts/common-functions
. tests/scripts/common-vm

get_config

export FORGE_HOME=/opt/gforge
export HOST=$1
export FILTER="TarCentosTests.php"

prepare_workspace
destroy_vm -t centos6 $HOST
start_vm_if_not_keeped -t centos6 $HOST

setup_redhat_3rdparty_repo

#[ ! -e $HOME/doxygen-1.6.3/bin/doxygen ] || make build-doc DOCSDIR=$WORKSPACE/apidocs DOXYGEN=$HOME/doxygen-1.6.3/bin/doxygen
#make BUILDRESULT=$WORKSPACE/build/packages buildtar
#make -f Makefile.rh BUILDRESULT=$WORKSPACE/build/packages src

setup_dag_repo $@
setup_epel_repo $@

echo "Create $FORGE_HOME if necessary"
ssh root@$HOST "[ -d $FORGE_HOME ] || mkdir -p $FORGE_HOME"

echo "Sync code on root@$HOST:$FORGE_HOME"
rsync -a --delete src/ root@$HOST:$FORGE_HOME/src/

echo "Run Install on $HOST"
ssh root@$HOST "$FORGE_HOME/src/install-ng --auto --reinit"

echo "Dump freshly installed database"
ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"

echo "Set use_ssl=no"
ssh root@$HOST "(echo [core];echo use_ssl=no;echo use_fti=no) > /etc/gforge/config.ini.d/zzz-zbuildbot.ini"
# Better to remove this until ssl conf is fixed properly
# I noticed that on centos6 gforge.conf should be included after ssl.conf
# We should consider having separate files for ssl and maybe separate ff package
ssh root@$HOST "yum -y remove mod_ssl ; service httpd restart"
ssh root@$HOST "(echo [moinmoin];echo use_frame=no) >> /etc/gforge/config.ini.d/zzz-buildbot.ini"
ssh root@$HOST "(echo [mediawiki];echo unbreak_frames=yes) >> /etc/gforge/config.ini.d/zzz-buildbot.ini"

#  Install a fake sendmail to catch all outgoing emails.
# ssh root@$HOST "perl -spi -e s#/usr/sbin/sendmail#/opt/tests/scripts/catch_mail.php# /etc/gforge/local.inc"

echo "Stop cron daemon"
ssh root@$HOST "service crond stop" || true

# Install phpunit
ssh root@$HOST "yum -y --enablerepo=epel install php-phpunit-PHPUnit-Selenium"

# Install selenium
ssh root@$HOST "yum -y install selenium"

# Install selenium tests
rsync -a --delete tests/ root@$HOST:$FORGE_HOME/tests/

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
scp root@$HOST:/tmp/gforge-*.log $WORKSPACE/reports/

stop_vm_if_not_keeped -t centos6 $@
exit $retcode
