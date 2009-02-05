<?php

error_reporting (E_ALL);
define_syslog_variables ();
openlog ("gforge-plugin-report", LOG_PID | LOG_PERROR, LOG_LOCAL5);

?>