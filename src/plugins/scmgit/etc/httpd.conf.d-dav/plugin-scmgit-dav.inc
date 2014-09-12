Alias "/scmrepos/git" {core/data_path}/chroot/scmrepos/git
#this is a fake repo... looks like my configuration has a bug somewhere ? ...
#so I declare this Location as fake git repo
<Location "/scmrepos/git">
	DAV on
	Options	+Indexes -ExecCGI -FollowSymLinks -MultiViews
	AuthType Basic
	AuthName "Git repository"
        AuthUserFile {core/data_path}/gituser-authfile.__fake__
        Require valid-user
</Location>

#this part is based on apache mod_macro, you need to load the mod_macro module to use it
<Macro Project $repository>
<Location "/scmrepos/git/$repository">
        DAV on
        Options +Indexes -ExecCGI -FollowSymLinks -MultiViews
        AuthType Basic
        AuthName "Git repository: $repository"
        #The AuthUserFile filename is needed in the code. Please do not rename it.
        AuthUserFile {core/data_path}/gituser-authfile.$repository
        Require valid-user
</Location>
</Macro>

#######
# define each valid project below using this syntax:
# example for project named "myproject"
# Use Project myproject
#
# the list of available projects should be generate automaticaly by cronjob but you need to restart httpd
# to reload the configuration: service httpd reload (done by cronjob if you uncomment the lines)
