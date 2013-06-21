#! /bin/sh

p=/usr/share/gforge/plugins/sysauthldap

if [ -x /usr/sbin/slapd ] && [ -x /usr/bin/ldapadd ] ; then
    if ! slapcat -b cn=schema,cn=config 2> /dev/null | egrep -q ^cn:.\{[[:digit:]]+\}gforge$ ; then
	$p/bin/schema2ldif.pl < $p/gforge.schema | ldapadd -H ldapi:/// -Y EXTERNAL -Q
    fi
fi

c=/etc/fusionforge/config.ini.d/sysauthldap-secrets.ini
if ! [ -e "$c" ] ; then
    touch $c
    chmod 600 $c
    echo [sysauthldap] >> $c
    echo ldap_password = CHANGEME >> $c
fi

f=/etc/gforge/httpd.conf.d/plugin-sysauthldap-secrets.inc
if [ ! -e $f ] ; then
    cp /usr/share/gforge/etc/httpd.conf.d-fhs/plugin-sysauthldap-secrets.inc $f
    chmod 600 $f
    PATH=/usr/share/gforge/bin:$PATH manage-apache-config.sh install
    mv $f.generated $f
fi

