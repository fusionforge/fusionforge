SetEnvIf Request_URI ^/authscm/([^/]+)/svn/ ITKUID=$1
SetEnvIf Request_URI ^/authscm/([^/]+)/svn/([^/]+)/ ITKGID=$2
