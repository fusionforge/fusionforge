SetEnvIf Request_URI ^/authscm/([^/]+)/git/ ITKUID=$1
SetEnvIf Request_URI ^/authscm/([^/]+)/git/([^/]+)/ ITKGID=$2
