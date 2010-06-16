#! /bin/sh -e

if [ -e gforge/etc/httpd.conf.d ] ; then        # We're in the parent dir
    cd gforge/etc
elif [ -e etc/httpd.conf.d ] ; then             # probably in gforge/ (or a renamed gforge/)
    cd etc
elif [ -e ../etc/httpd.conf.d ] ; then          # possibly in gforge/etc
    cd ../etc
else
    echo "Couldn't find Apache config directory..."
    exit 1
fi

case $1 in
    build)
	mkdir -p httpd.conf.d-fhs
	for i in httpd.conf.d/*.inc httpd.conf.d/*.conf ; do
	    sed -e 's,{core/config_path},/etc/gforge,g' \
		-e 's,{core/source_path},/usr/share/gforge,g' \
		-e 's,{core/data_path},/var/lib/gforge,g' \
		-e 's,{core/log_path},/var/log/gforge,g' \
		-e 's,{core/chroot},/var/lib/gforge/chroot,g' \
		-e 's,{core/custom_path},/etc/gforge/custom,g' \
		-e 's,{core/url_prefix},/,g' \
		-e 's,{core/groupdir_prefix},/var/lib/gforge/chroot/home/groups,g' \
		-e 's,{mediawiki/src_path},/usr/share/mediawiki,g' \
		$i > httpd.conf.d-fhs/$(basename $i)
	done
	
	mkdir -p httpd.conf.d-opt
	for i in httpd.conf.d/*.inc httpd.conf.d/*.conf ; do
	    sed -e 's,{core/config_path},/opt/fusionforge/etc,g' \
		-e 's,{core/source_path},/opt/fusionforge,g' \
		-e 's,{core/data_path},/opt/fusionforge/data,g' \
		-e 's,{core/log_path},/opt/fusionforge/log,g' \
		-e 's,{core/chroot},/opt/fusionforge/data/chroot,g' \
		-e 's,{core/custom_path},/opt/fusionforge/etc/custom,g' \
		-e 's,{core/url_prefix},/,g' \
		-e 's,{core/groupdir_prefix},/opt/fusionforge/data/chroot/home/groups,g' \
		-e 's,{mediawiki/src_path},/usr/share/mediawiki,g' \
		$i > httpd.conf.d-opt/$(basename $i)
	done
	
	mkdir -p httpd.conf.d-usrlocal
	for i in httpd.conf.d/*.inc httpd.conf.d/*.conf ; do
	    sed -e 's,{core/config_path},/etc/gforge,g' \
		-e 's,{core/source_path},/usr/local/share/gforge,g' \
		-e 's,{core/data_path},/var/local/lib/gforge,g' \
		-e 's,{core/log_path},/var/log/gforge,g' \
		-e 's,{core/chroot},/var/local/lib/gforge/chroot,g' \
		-e 's,{core/custom_path},/etc/gforge/custom,g' \
		-e 's,{core/url_prefix},/,g' \
		-e 's,{core/groupdir_prefix},/var/local/lib/gforge/chroot/home/groups,g' \
		-e 's,{mediawiki/src_path},/usr/share/mediawiki,g' \
		$i > httpd.conf.d-usrlocal/$(basename $i)
	done
	;;
	
    install)
	dir=$(forge_get_config config_path)/httpd.conf.d
	cd dir
	files=$(ls *.inc *.conf | xargs grep -l {[a-z_]*/[a-z_]*})
	for f in files ; do
	    ftmp=$(mktemp $f.generated.XXXXXX)
	    cp -a $f $ftmp
	    for v in config_path source_path data_path log_path chroot custom_path url_prefix groupdir_prefix web_gost lists_host database_host database_name database_user database_password ldap_password jabber_password ; do
		
		sed -i -e s,{core/$v},$(forge_get_config $v),g $ftmp
	    done
	    sed -i -e s,{mediawiki/src_path},$(forge_get_config src_path mediawiki),g $ftmp
	    mv $ftmp $f.generated
	done
	;;
    
    *)
	echo "Unknown operation"
	exit 1
	;;
esac
