#! /bin/sh
# 
# $Id$
#
# Configure exim for Sourceforge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

replace_file () {
    file=$1
    cp $file ${file}.sourceforge-old
    mv ${file}.sourceforge-new $file
}

propose_update () {
    file=$1
    if diff -q ${file} ${file}.sourceforge-new &> /dev/null ; then
	# Old file and new file are identical
	rm -f ${file}.sourceforge-new
    else
	done=NO
	while [ "X$done" = "XNO" ]; do
	    cat >&2 <<EOPRMT
Configuration file \`$file':
Installing Sourceforge requires modifications in this file.
   What would you like to do about it ?  Your options are:
    Y or I  : automatically preform these changes
    N or O  : keep your currently version
      D     : show the differences between the versions
      Z     : start a new shell to examine the situation
 The default action is to keep your current version.
EOPRMT
	    echo -n >&2 "Automatically change $file (Y/I/N/O/D/Z) [default=N] ?"
	    read -e ANSWER;
	    case "$ANSWER" in
		y|Y|I|i)
		    echo >&2 "Replacing file $file with changed version"
		    replace_file $file
		    done=YES
		    ;;
		D|d)
		    diff -uBbw "$file" "${file}.sourceforge-new" | sensible-pager
		    ;;
		Z|z)
		    bash
		    ;;
		n|N|o|O)
		    # Do nothing
		    done=YES
		    exit 0;
		    ;;
		*)
		    # Do nothing
		    done=YES
		    exit 0;
	    esac
	done
    fi
}

case "$1" in
    configure)
	# Make sure Apache sees us
	cp -a /etc/apache/httpd.conf /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule php4_module/LoadModule php4_module/gi" /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule ssl_module/LoadModule ssl_module/gi" /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule apache_ssl_module/LoadModule apache_ssl_module/gi" /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule env_module/LoadModule env_module/gi" /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule vhost_alias_module/LoadModule vhost_alias_module/gi" /etc/apache/httpd.conf.sourceforge-new
	
	if ! grep -q "^Include /etc/sourceforge/sf-httpd.conf" /etc/apache/httpd.conf.sourceforge-new ; then
	    echo "### Next line inserted by Sourceforge install" >> /etc/apache/httpd.conf.sourceforge-new
	    echo "Include /etc/sourceforge/sf-httpd.conf" >> /etc/apache/httpd.conf.sourceforge-new
	fi

	propose_update /etc/apache/httpd.conf
	
	# Make sure pgsql,ldap and gd are enabled in the PHP config files
	if [ -f /etc/php4/apache/php.ini ]; then
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*pgsql.so" /etc/php4/apache/php.ini; then
		echo "Enabling pgsql in /etc/php4/apache/php.ini"
		echo "extension=pgsql.so" >> /etc/php4/apache/php.ini
	    fi
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*gd.so" /etc/php4/apache/php.ini; then
		echo "Enabling gd in /etc/php4/apache/php.ini"
		echo "extension=gd.so" >> /etc/php4/apache/php.ini
	    fi
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*ldap.so" /etc/php4/apache/php.ini; then
		echo "Enabling ldap in /etc/php4/apache/php.ini"
		echo "extension=ldap.so" >> /etc/php4/apache/php.ini
	    fi
	fi
	if [ -f /etc/php4/cgi/php.ini ]; then
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*pgsql.so" /etc/php4/cgi/php.ini; then
		echo "Enabling pgsql in /etc/php4/cgi/php.ini"
		echo "extension=pgsql.so" >> /etc/php4/cgi/php.ini
	    fi
	fi

	invoke-rc.d apache restart
	;;

    purge)
  	if grep -q "Include /etc/sourceforge/sf-httpd.conf" /etc/apache/httpd.conf ; then
	    pattern=$(basename $0)
	    tmp=$(mktemp /tmp/$pattern.XXXXXX)
	    grep -v "Include /etc/sourceforge/sf-httpd.conf\|### Next line inserted by Sourceforge install" /etc/apache/httpd.conf > $tmp
	    cat $tmp > /etc/apache/httpd.conf
	    rm -f $tmp
	    invoke-rc.d apache restart
  	fi
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
	;;
	
esac
