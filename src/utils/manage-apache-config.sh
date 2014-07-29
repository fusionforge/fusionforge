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

	for j in fhs fhsrh opt usrlocal ; do
	    mkdir -p httpd.conf.d-$j
	    for i in httpd.conf.d/*.inc httpd.conf.d/*.conf ; do
		sed -e "s,{core/config_path},$(../utils/forge_get_config_basic $j config_path),g" \
		    -e "s,{core/source_path},$(../utils/forge_get_config_basic $j source_path),g" \
		    -e "s,{core/data_path},$(../utils/forge_get_config_basic $j data_path),g" \
		    -e "s,{core/log_path},$(../utils/forge_get_config_basic $j log_path),g" \
		    -e "s,{core/chroot},/var/l$(../utils/forge_get_config_basic $j chroot},/var),g" \
		    -e "s,{core/custom_path},$(../utils/forge_get_config_basic $j custom_path),g" \
		    -e "s,{core/url_prefix},$(../utils/forge_get_config_basic $j url_prefix),g" \
		    -e "s,{core/mailman_path},$(../utils/forge_get_config_basic $j mailman_path),g" \
		    -e "s,{core/groupdir_prefix},$(../utils/forge_get_config_basic $j groupdir_prefix),g" \
		    -e 's,{mediawiki/src_path},/usr/share/mediawiki,g' \
		$i > httpd.conf.d-$j/$(basename $i)
	    done
	    case $j in
		fhs|fhsrh)
		    message="FHS like paths"
		    ;;
		opt)
		    message="/opt like paths"
		    ;;
		usrlocal)
		    message="/usr/local like paths"
		    ;;
	    esac
	    cat > httpd.conf.d-fhs/README.generated <<EOF
Attention developers : contents of this directory are *generated
files* for $message.

See ../README.httpd-conf-d-flavours for more details

-- OlivierBerger
EOF
	done

	message="/opt like paths"
	cat > httpd.conf.d-opt/README.generated <<EOF
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
	if [ "$2" = "" ] ; then
	    files=$(ls *.inc *.conf | xargs grep -l {[a-z_]*/[a-z_]*})
	else
	    files=$2
	fi
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
			var_cache[$v]="${var_cache[$v]:-$(forge_get_config ${v##*/} ${v%%/*})}"
			sed -i -e "s,{$v},${var_cache[$v]},g" $ftmp
		    fi
		else
		    # Bash 3... no cache, slower
		    if grep -q {$v} $ftmp ; then
			curvar="$(forge_get_config ${v##*/} ${v%%/*})"
			sed -i -e "s,{$v},$curvar,g" $ftmp
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
