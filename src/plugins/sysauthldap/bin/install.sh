#! /bin/sh

p=$(forge_get_config plugins_path)/sysauthldap

if [ -x /usr/sbin/slapd ] && [ -x /usr/bin/ldapadd ] ; then
    if ! slapcat -b cn=schema,cn=config 2> /dev/null | egrep -q ^cn:.\{[[:digit:]]+\}gforge$ ; then
	$p/bin/schema2ldif.pl < $p/gforge.schema | ldapadd -H ldapi:/// -Y EXTERNAL -Q
    fi
fi

c=$(forge_get_config config_path)/config.ini.d/sysauthldap-secrets.ini
if ! [ -e "$c" ] ; then
    touch $c
    chmod 600 $c
    echo [sysauthldap] >> $c
    echo ldap_password = CHANGEME >> $c
fi

f=$(forge_get_config config_path)/httpd.conf.d/plugin-sysauthldap-secrets.inc
if [ ! -e $f ] ; then
    cp $(forge_get_config source_path)/etc/httpd.conf.d-fhs/plugin-sysauthldap-secrets.inc $f
    chmod 600 $f
    PATH=$(forge_get_config binary_path):$PATH manage-apache-config.sh install
    mv $f.generated $f
fi

