#! /bin/sh

# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>

# This script will start the Selenium server which will execute the tests on the installed forge

# Prerequisite : having run 'install.sh' and its prerequisites

# Note that there may be problems with the firefox profile and SSL
# certificate.  Refer to
# https://fusionforge.org/plugins/mediawiki/wiki/fusionforge/index.php/Virtual_machine_development_environment
# to find instructions to workaround these.

echo
echo "You may wish to install a graphical environment (gnome, kde, lxde, ...) to run the selenium server inside the VM's display."
echo

cd /root/fusionforge/3rd-party/selenium
make
