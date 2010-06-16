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

mkdir -p httpd.conf.d-fhs
for i in httpd.conf.d/*.inc httpd.conf.d/*.conf ; do
    sed -e 's,{core/config_path},/etc/gforge,g' \
	-e 's,{core/source_path},/usr/share/gforge,g' \
	-e 's,{core/data_path},/var/lib/gforge,g' \
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
	-e 's,{core/chroot},/var/local/lib/gforge/chroot,g' \
	-e 's,{core/custom_path},/etc/gforge/custom,g' \
	-e 's,{core/url_prefix},/,g' \
	-e 's,{core/groupdir_prefix},/var/local/lib/gforge/chroot/home/groups,g' \
	-e 's,{mediawiki/src_path},/usr/share/mediawiki,g' \
	$i > httpd.conf.d-usrlocal/$(basename $i)
done
