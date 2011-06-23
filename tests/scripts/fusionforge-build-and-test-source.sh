#!/bin/sh -ex

export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=${SELENIUM_RC_HOST:-`hostname -i`}
export SELENIUM_RC_DIR=$WORKSPACE/reports

# get config 
. tests/config/default
if [ -f tests/config/`hostname` ] ; then . tests/config/`hostname`; fi
if [ ! -z "$1" ]
then
	export HOST="$1"
else
	export HOST=centos5src.$DNSDOMAIN
	export VEID=$VEIDSRC
fi


export LXCTEMPLATE=$LXCCOSTEMPLATE

export IPBASE=$IPCOSBASE
export IPDNS=$IPCOSDNS
export IPMASK=$IPCOSMASK
export IPGW=$IPCOSGW

ARCH=`dpkg-architecture -qDEB_BUILD_ARCH`
export VZTEMPLATE=centos-$COSVERS-$ARCH-minimal
export VZPRIVATEDIR
export DEBMIRROR

export DIST
export VMENGINE
export SSHPUBKEY
export HOSTKEYDIR

if [ "x${HUDSON_URL}" = "x" ]
then
	export BASEDIR=${BASEDIR:-/~`id -un`/ws}
	export USEVZCTL=true
	export SELENIUM_RC_HOST=localhost
	export SELENIUM_RC_URL=http://`hostname -f`$BASEDIR/reports
	export FFORGE_RPM_REPO=http://`hostname -f`$BASEDIR/build/packages
else
	export SELENIUM_RC_URL=${HUDSON_URL}job/$JOB_NAME/ws/reports
	export FFORGE_RPM_REPO=${HUDSON_URL}job/$JOB_NAME/ws/build/packages
	export VZTEMPLATE=centos-5-x86
fi
export DB_NAME=fforge
export CONFIGURED=true

export BUILDRESULT=$WORKSPACE/build/packages

[ ! -d $WORKSPACE/build/packages ] || rm -fr $WORKSPACE/build/packages
[ ! -d $WORKSPACE/build/config ] || rm -fr $WORKSPACE/build/config
[ ! -d $WORKSPACE/reports ] || rm -fr $WORKSPACE/reports
[ ! -d $WORKSPACE/apidocs ] || rm -fr $WORKSPACE/apidocs
mkdir -p $WORKSPACE/build/packages $WORKSPACE/build/config $WORKSPACE/reports/coverage $WORKSPACE/apidocs

[ ! -e $HOME/doxygen-1.6.3/bin/doxygen ] || make build-doc DOCSDIR=$WORKSPACE/apidocs DOXYGEN=$HOME/doxygen-1.6.3/bin/doxygen

make BUILDRESULT=$WORKSPACE/build/packages buildtar

make -f Makefile.rh BUILDRESULT=$WORKSPACE/build/packages src

(cd 3rd-party/selenium ; make getselenium)

if $KEEPVM
then
	echo "Destroying vm $HOST"
	(cd tests/scripts ; sh ./stop_vm.sh $HOST || true)
fi

(cd tests/scripts ; ./start_vm.sh $HOST)

# FUSIONFORGE REPO
cp src/rpm-specific/fusionforge.repo $WORKSPACE/build/packages/fusionforge.repo
sed -i "s#http://fusionforge.org/#${HUDSON_URL}#" $WORKSPACE/build/packages/fusionforge.repo
if [ ! -z "$FFORGE_RPM_REPO" ]
then
	sed -i "s#baseurl = .*#baseurl = ${FFORGE_RPM_REPO}/#" $WORKSPACE/build/packages/fusionforge.repo
fi
scp $WORKSPACE/build/packages/fusionforge.repo root@$HOST:/etc/yum.repos.d/

# DAG
cp src/rpm-specific/dag-rpmforge.repo $WORKSPACE/build/packages/dag-rpmforge.repo
if [ ! -z "$DAG_RPMFORGE_REPO" ] ; then
        sed -i "s#http://apt.sw.be/redhat#${DAG_RPMFORGE_REPO}#" $WORKSPACE/build/packages/dag-rpmforge.repo
fi
scp $WORKSPACE/build/packages/dag-rpmforge.repo root@$HOST:/etc/yum.repos.d/

cat > $WORKSPACE/build/config/phpunit <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

scp -r tests root@$HOST:/root
scp -r $WORKSPACE/build/config  root@$HOST:/root
scp 3rd-party/selenium/binary/selenium-server-current/selenium-server.jar root@$HOST:/root
if [ "x$BUILDRESULT" != "x" ]
then
	scp $BUILDRESULT/fusionforge-*.tar.bz2 root@$HOST:
else
	scp ../build/packages/fusionforge-*.tar.bz2 root@$HOST:
fi
ssh root@$HOST 'tar jxf fusionforge-*.tar.bz2'
[ ! -e "/tmp/timedhosts.txt" ] || scp -p /tmp/timedhosts.txt root@$HOST:/var/cache/yum/timedhosts.txt
ssh root@$HOST "cd fusionforge-*; FFORGE_RPM_REPO=$FFORGE_RPM_REPO DAG_RPMFORGE_REPO=$DAG_RPMFORGE_REPO FFORGE_DB=fforge FFORGE_USER=gforge FFORGE_ADMIN_USER=ffadmin FFORGE_ADMIN_PASSWORD=ffadmin ./install.sh $HOST"
scp -p root@$HOST:/var/cache/yum/timedhosts.txt /tmp/timedhosts.txt || true
ssh root@$HOST "(echo [core];echo use_ssl=no) > /etc/gforge/config.ini.d/zzz-zbuildbot.ini"
ssh root@$HOST "cd /root/tests/func; CONFIGURED=true CONFIG_PHP=config.php.buildbot DB_NAME=$DB_NAME php db_reload.php"
ssh root@$HOST "su - postgres -c \"pg_dump -Fc $DB_NAME\" > /root/dump"
#ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"
#  Install a fake sendmail to catch all outgoing emails.
# ssh root@$HOST "perl -spi -e s#/usr/sbin/sendmail#/opt/tests/scripts/catch_mail.php# /etc/gforge/local.inc"
ssh root@$HOST "service crond stop" || true

retcode=0
if $REMOTESELENIUM
then
	echo "Run phpunit test on $HOST"
	ssh -X root@$HOST "tests/scripts/phpunit.sh TarCentos52Tests.php" || retcode=$?
else
	cd tests
	phpunit --log-junit $WORKSPACE/reports/phpunit-selenium.xml TarCentos52Tests.php || retcode=$?
	cd ..
fi
if [ "x$SELENIUM_RC_DIR" != "x" ]
then
	rsync -av root@$HOST:/var/log/ $SELENIUM_RC_DIR/
fi
cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
if $KEEPVM 
then
	echo "Keeping vm $HOST alive"
else
	(cd tests/scripts ; sh ./stop_vm.sh $HOST)
fi
exit $retcode
