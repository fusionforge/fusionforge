#! /bin/bash -e

# Generates contents of the apache configuration files based on the
# sources in src/etc/httpd.conf.d/ for 3 different flavours :
#  - src/etc/httpd.conf.d-fhs/ : for FHS like paths (/usr, ...)
#  - src/etc/httpd.conf.d-opt/ : for /opt like paths
#  - src/etc/httpd.conf.d-usrlocal/ : for /usr/local like paths
#
# See the thread at : http://lists.fusionforge.org/pipermail/fusionforge-general/2010-June/001067.html for some more details
#

# invoke with utils/manage-apache-config.sh build to regenerate the config files
# or with utils/manage-apache-config.sh install to ...(TODO: document this)...

case $1 in
    build)
	# Change to the script directory
        cd $(dirname $0)
	# Guess where is Apache config directory
	if [ -e src/etc/httpd.conf.d ] ; then # We're in the parent dir
	    cd src/etc
	elif [ -e etc/httpd.conf.d ] ; then # probably in src/ (or a renamed gforge/)
	    cd etc
	elif [ -e ../etc/httpd.conf.d ] ; then # possibly in src/etc
	    cd ../etc
	else
	    echo "Couldn't find Apache config directory..."
	    exit 1
	fi

	# FHS like paths (for Debian packages, etc.)
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
		-e 's,{scmsvn/repos_path},/var/lib/gforge/chroot/scmrepos/svn,g' \
		$i > httpd.conf.d-fhs/$(basename $i)
	done
	message="FHS like paths"
	cat > httpd.conf.d-fhs/README.generated <<EOF
Attention developers : contents of this directory are *generated
files* for $message.

See ../README.httpd-conf-d-flavours for more details

-- OlivierBerger
EOF

	# FHS like paths (for Redhat packages, etc.)
	mkdir -p httpd.conf.d-fhsrh
	for i in httpd.conf.d/*.inc httpd.conf.d/*.conf ; do
	    sed -e 's,{core/config_path},/etc/gforge,g' \
		-e 's,{core/source_path},/usr/share/gforge/src,g' \
		-e 's,{core/data_path},/var/lib/gforge,g' \
		-e 's,{core/log_path},/var/log/gforge,g' \
		-e 's,{core/chroot},/var/lib/gforge/chroot,g' \
		-e 's,{core/custom_path},/etc/gforge/custom,g' \
		-e 's,{core/url_prefix},/,g' \
		-e 's,{core/groupdir_prefix},/var/lib/gforge/chroot/home/groups,g' \
		-e 's,{mediawiki/src_path},/usr/share/mediawiki,g' \
		-e 's,{scmsvn/repos_path},/var/lib/gforge/chroot/scmrepos/svn,g' \
		$i > httpd.conf.d-fhsrh/$(basename $i)
	done
	message="FHS like paths"
	cat > httpd.conf.d-fhsrh/README.generated <<EOF
Attention developers : contents of this directory are *generated
files* for $message.

See ../README.httpd-conf-d-flavours for more details

-- OlivierBerger
EOF

	# /opt like paths
	mkdir -p httpd.conf.d-opt
	for i in httpd.conf.d/*.inc httpd.conf.d/*.conf ; do
	    sed -e 's,{core/config_path},/etc/gforge,g' \
		-e 's,{core/source_path},/opt/gforge/src,g' \
		-e 's,{core/data_path},/var/lib/gforge,g' \
		-e 's,{core/log_path},/var/log/gforge,g' \
		-e 's,{core/chroot},/var/lib/gforge/chroot,g' \
		-e 's,{core/custom_path},/etc/gforge/custom,g' \
		-e 's,{core/url_prefix},/,g' \
		-e 's,{core/groupdir_prefix},/var/lib/gforge/chroot/home/groups,g' \
		-e 's,{mediawiki/src_path},/usr/share/mediawiki,g' \
		-e 's,{scmsvn/repos_path},/var/lib/gforge/svnroot,g' \
		$i > httpd.conf.d-opt/$(basename $i)
	done
	message="/opt like paths"
	cat > httpd.conf.d-opt/README.generated <<EOF
Attention developers : contents of this directory are *generated
files* for $message.

See ../README.httpd-conf-d-flavours for more details

-- OlivierBerger
EOF
	
	# /usr/local like paths
	mkdir -p httpd.conf.d-usrlocal
	for i in httpd.conf.d/*.inc httpd.conf.d/*.conf ; do
	    sed -e 's,{core/config_path},/etc/gforge,g' \
		-e 's,{core/source_path},/usr/local/share/gforge/src,g' \
		-e 's,{core/data_path},/var/local/lib/gforge,g' \
		-e 's,{core/log_path},/var/log/gforge,g' \
		-e 's,{core/chroot},/var/local/lib/gforge/chroot,g' \
		-e 's,{core/custom_path},/etc/gforge/custom,g' \
		-e 's,{core/url_prefix},/,g' \
		-e 's,{core/groupdir_prefix},/var/local/lib/gforge/chroot/home/groups,g' \
		-e 's,{mediawiki/src_path},/usr/share/mediawiki,g' \
		-e 's,{scmsvn/repos_path},/var/lib/gforge/chroot/scmrepos/svn,g' \
		$i > httpd.conf.d-usrlocal/$(basename $i)
	done
	message="/usr/local like paths"
	cat > httpd.conf.d-usrlocal/README.generated <<EOF
Attention developers : contents of this directory are *generated
files* for $message.

See ../README.httpd-conf-d-flavours for more details

-- OlivierBerger
EOF
	;;
	
    install)
	dir=$(forge_get_config config_path)/httpd.conf.d
	[ -e $dir ] || mkdir -p $dir
	cd $dir
	files=$(ls *.inc *.conf | xargs grep -l {[a-z_]*/[a-z_]*})
	vars=$(forge_get_config list-all-variables)
	if [ $BASH_VERSINFO -ge 4 ] ; then
	    # Use associative array if available
	    declare -A var_cache
	fi
	for f in $files ; do
	    ftmp=$(mktemp $f.generated.XXXXXX)
	    cp -a $f $ftmp
	    for v in $vars ; do
		if [ $BASH_VERSINFO -ge 4 ] ; then
		    # Fast version, with cache, for Bash >= 4
		    if grep -q {$v} $ftmp ; then
			var_cache[$v]=${var_cache[$v]:-$(forge_get_config ${v##*/} ${v%%/*})}
			sed -i -e s,{$v},${var_cache[$v]},g $ftmp
		    fi
		else
		    # Bash 3... no cache, slower
		    if grep -q {$v} $ftmp ; then
			curvar=$(forge_get_config ${v##*/} ${v%%/*})
			sed -i -e s,{$v},$curvar,g $ftmp
		    fi
		fi
	    done
	    mv $ftmp $f.generated
	done
	;;
    
    *)
	echo "Unknown operation"
	echo "invoke with $0 [build|install]"
	exit 1
	;;
esac
