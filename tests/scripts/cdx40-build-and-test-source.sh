#! /bin/sh -x
export CURDIR=`pwd`
export WORKSPACE=${WORKSPACE:-$CURDIR}

export CONFIG_PHP=func/config.php.buildbot
export SELENIUM_RC_HOST=${SELENIUM_RC_HOST:-`hostname -i`}
export SELENIUM_RC_DIR=$WORKSPACE/reports

export DEBMIRROR

# get config
. tests/config/default
if [ -f tests/config/`hostname` ] ; then . tests/config/`hostname`; fi
if [ ! -z "$1" ]
then
        export HOST=$1
else
	export HOST=cdx40.$DNSDOMAIN
fi

export LXCTEMPLATE=$LXCCOSTEMPLATE

export DIST
export VMENGINE
export SSHPUBKEY
export HOSTKEYDIR

CDXVERS=Codendi_4.0
CDXIP=`hostname -i`

# Unit tests
#sh $WORKSPACE/$CDXVERS/codendi_tools/ci_build.sh $HOST $CDXIP $CDXVERS

if $KEEPVM
then
        echo "Destroying vm $HOST"
        (cd tests/scripts ; sh ./stop_vm.sh $HOST || true)
fi
(cd tests/scripts ; ./start_vm.sh $HOST)

[ ! -d $WORKSPACE/build ] || rm -fr $WORKSPACE/build
[ ! -d $WORKSPACE/reports ] || rm -fr $WORKSPACE/reports
mkdir -p $WORKSPACE/build/packages $WORKSPACE/build/config $WORKSPACE/reports/coverage

cat > $WORKSPACE/build/config/phpunit <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

scp -r tests root@$HOST:/root
scp -r $WORKSPACE/build/config  root@$HOST:/root
(cd 3rd-party/selenium ; make getselenium)
scp 3rd-party/selenium/binary/selenium-server-current/selenium-server.jar root@$HOST:/root

# EPEL
cp src/rpm-specific/epel-short.repo $WORKSPACE/build/packages/epel.repo
if [ ! -z "$EPEL_REPO" ] ; then
        sed -i "s#http://download.fedoraproject.org/pub/epel#${EPEL_REPO}#" $WORKSPACE/build/packages/epel.repo
fi
scp $WORKSPACE/build/packages/epel.repo root@$HOST:/etc/yum.repos.d/ 

# DAG
cp src/rpm-specific/dag-rpmforge.repo $WORKSPACE/build/packages/dag-rpmforge.repo
if [ ! -z "$DAG_RPMFORGE_REPO" ] ; then
        sed -i "s#http://apt.sw.be/redhat#${DAG_RPMFORGE_REPO}#" $WORKSPACE/build/packages/dag-rpmforge.repo
	# disable dag by default
        sed -i "s#enabled = 1#enabled = 0#" $WORKSPACE/build/packages/dag-rpmforge.repo
fi
scp $WORKSPACE/build/packages/dag-rpmforge.repo root@$HOST:/etc/yum.repos.d/ 

#================
CDXPACKAGES="mod_ssl vsftpd perl-DBI perl-DBD-MySQL gd sendmail telnet bind bind-chroot caching-nameserver ntp perl-suidperl python-devel rcs sendmail-cf perl-URI perl-HTML-Tagset perl-Digest-SHA1 perl-Digest-HMAC perl-Socket6 perl-HTML-Parser perl-libwww-perl php-ldap php-mysql mysql-server mysql MySQL-python php-mbstring php-gd php-soap perl-DateManip sysstat gd-devel freetype-devel libpng-devel libjpeg-devel dump dejavu-lgc-fonts compat-libstdc++-33 policycoreutils selinux-policy selinux-policy-targeted zip unzip enscript xinetd mod_auth_mysql nscd"
BUILDDEPS="libtool krb5-devel pam-devel byacc flex httpd-devel docbook-style-xsl doxygen gettext neon-devel openssl-devel sqlite-devel swig"
MISSINGCVS="gcc"
MISSINGHIL="gcc-c++"
MISSINGMUNIN="which"
#================
# See http://wiki.centos.org/PackageManagement/Yum/Priorities
#================
ssh root@$HOST mkdir -p /usr/share/codendi/src
ssh root@$HOST yum install -y rsync make rpm-build yum-priorities
# Needed to build package, not working yet
#rsync -rlptD $CDXVERS/rpm/ root@$HOST:/usr/src/redhat/
#ssh root@$HOST yum install -y $BUILDDEPS $MISSINGCVS $MISSINGHIL $MISSINGMUNIN
#ssh root@$HOST "cd /usr/src/redhat ; make"
# Coping prebuilded
rsync -rlptD $WORKSPACE/CDROM/ root@$HOST:/root/
# Next is done in codendi.tgz
#rsync -a $CDXVERS/src/ root@$HOST:/usr/share/codendi/src/

scp $WORKSPACE/$CDXVERS/codendi_tools/codendi_install.sh root@$HOST:
scp $WORKSPACE/$CDXVERS/codendi_tools/localconf root@$HOST:
ssh root@$HOST chmod +x codendi_install.sh
ssh root@$HOST yum install -y $CDXPACKAGES

ssh root@$HOST /root/codendi_install.sh
ssh root@$HOST /usr/share/codendi/src/utils/generate_ssl_certificate.sh <<-FIN
y
FR
ISERE
GRENOBLE
MYCOMPANY

$HOST
admin@$HOST





FIN
#=== Mediawiki ===
ssh root@$HOST yum --enablerepo=epel -y  install mediawiki115
#=================
retcode=0

echo "Run phpunit test on $HOST"
ssh -X root@$HOST "tests/scripts/phpunit.sh CDXCentos52Tests.php" || retcode=$?

if [ "x$SELENIUM_RC_DIR" != "x" ]
then
        rsync -av root@$HOST:/var/log/ $SELENIUM_RC_DIR/
fi
cp $WORKSPACE/reports/phpunit-selenium.xml $WORKSPACE/reports/phpunit-selenium.xml.org
xalan -in $WORKSPACE/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $WORKSPACE/reports/phpunit-selenium.xml
#================

if $KEEPVM
then
        echo "Keeping vm $HOST alive"
else
        (cd tests/scripts ; sh ./stop_vm.sh $HOST)
fi
exit $retcode
