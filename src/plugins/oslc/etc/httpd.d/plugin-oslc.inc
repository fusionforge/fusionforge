
# This should be placed at the top of the directory whose path
# corresponds to RewriteBase below.

# The apache rewrite module should be activated for his to work

# You should customize the following elements :
#  - the RewriteBase bellow
#  - optional : the TRACKER_TYPE (mantis (default), fusionforge, demo)
#  - optional : the AUTH_TYPE (basic (default), oauth (for Mantis only))

SetEnv TRACKER_TYPE fusionforge
#SetEnv TRACKER_TYPE mantis

# HTTP Basic authentication
SetEnv AUTH_TYPE basic
# OAuth authorization (only supported for Mantis at the moment)
#SetEnv AUTH_TYPE oauth

# See application.ini for contextual behaviour depending on this variable
SetEnv APPLICATION_ENV development


# "Intercept" anything below the RewriteBase to redirect to index.php
#  which will do the URL parsing and so on


<Location plugins/oslc>
RewriteEngine On
RewriteBase /plugins/oslc/

RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [NC,L]
RewriteRule ^.*$ index.php [NC,L]
</Location>

