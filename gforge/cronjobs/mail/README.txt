To use the mailing list cronjobs, you shold first copy your /etc/aliases file 
to /etc/aliases.org

That file will then be the base used by the cron scripts to create the final 
/etc/aliases file.

#
##       Create the new mailing lists
#
06 * * * * ~/gforge3/cronjobs/mail/mailing_lists_create.php

#
##       Create the /etc/aliases file
#
08 * * * * ~/gforge3/cronjobs/mail/mailaliases.php

