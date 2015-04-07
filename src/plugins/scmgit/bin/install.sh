#!/bin/bash -e
# gitweb post-install

plugindir=$(forge_get_config plugins_path)/scmgit

gitwebcgi=$(ls /var/www/git/gitweb.cgi /var/www/gitweb-caching/gitweb.cgi \
    /usr/lib/cgi-bin/gitweb.cgi /usr/share/gitweb/gitweb.cgi 2>/dev/null | tail -1)
gitwebdir=$(ls -d /var/www/git /var/www/git/static /var/www/gitweb-caching /usr/share/gitweb/static 2>/dev/null | tail -1)
# CentOS: /var/www/git/
# Debian, openSUSE: /usr/share/gitweb/

case "$1" in
    configure)
	scmgit_repos_path=$(forge_get_config repos_path scmgit)

	echo "Modifying (x)inetd for Subversion server"
	if [ -d /etc/xinetd.d/ ]; then
	    if [ ! -e /etc/xinetd.d/fusionforge-plugin-scmgit ]; then
		cat > /etc/xinetd.d/fusionforge-plugin-scmgit <<-EOF
		service git
		{
		    port            = 9418
		    socket_type     = stream
		    wait            = no
		    user            = nobody
		    server          = /usr/bin/git
		    server_args     = daemon --inetd --export-all --base-path=$scmgit_repos_path
		}
		EOF
	    fi
	    service xinetd restart
	fi

	# rsync access
	if ! grep -q '^use chroot' /etc/rsyncd.conf 2>/dev/null; then
	    touch /etc/rsyncd.conf
	    echo 'use chroot=no' | sed -i -e '1ecat' /etc/rsyncd.conf
	fi
	sed -i -e 's/^use chroot.*/use chroot=no/' /etc/rsyncd.conf
	if ! grep -q '\[git\]' /etc/rsyncd.conf; then
	    cat <<-EOF >> /etc/rsyncd.conf
		[git]
		comment=Git source repositories
		path=$scmgit_repos_path
		EOF
	fi

	# Gitweb
	if [ -z "$gitwebcgi" -o -z "$gitwebdir" ]; then echo "Cannot find gitweb"; exit 1; fi
	mkdir -p -m 755 $plugindir/cgi-bin/
	ln -nfs $gitwebcgi                 $plugindir/cgi-bin/
	ln -nfs $gitwebdir/git-favicon.png $plugindir/www/
	ln -nfs $gitwebdir/git-logo.png    $plugindir/www/
	ln -nfs $gitwebdir/gitweb.css      $plugindir/www/
	ln -nfs $gitwebdir/gitweb.js       $plugindir/www/
	;;

    remove)
	rm -f /etc/xinetd.d/fusionforge-plugin-scmgit
	rm -rf $plugindir/cgi-bin/
	find $plugindir/www/ -type l -print0 | xargs -r0 rm
	;;
    *)
        echo "Usage: $0 {configure|remove}"
        exit 1
esac
