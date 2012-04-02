Alias "/scmrepos/git" /var/lib/gforge/chroot/scmrepos/git
<Location "/scmrepos/git">
	DAV on

	Options	+Indexes -ExecCGI -FollowSymLinks -MultiViews

	php_admin_flag    engine        off
	php_admin_value   open_basedir  /opt/groups/

#	# Use the generated access file for WebDAV access.
#	SVNParentPath			/opt/groups
#	AuthzSVNAccessFile		/etc/gforge/http/davroot-access

	AuthType Basic
	AuthName "Git repository"
	AuthUserFile /var/lib/gforge/svnroot-authfile

	# Try anonymous access first, then authentication if necessary.
	Satisfy Any
	Require valid-user
</Location>
