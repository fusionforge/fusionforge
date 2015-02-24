SetEnvIf Request_URI ^/authscm/([^/]+)/git/ ITKUID=$1 ITKGID=$1
SetEnvIf Request_URI ^/authscm/([^/]+)/gitweb/ ITKUID=$1 ITKGID=$1
# Note: when setting ITKUID, the user's groups (project memberships) are added
# Note: it's important to set ITKGID otherwise it stays 'www-data' and privilege separation is broken
