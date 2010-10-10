#
# http://seleniumhq.org/
# http://seleniumhq.org/projects/remote-control/
# http://selenium.googlecode.com/svn/trunk/remote/server/
# take http://selenium.googlecode.com/files/selenium-remote-control-1.0.3.zip
# see tests/func/README.TXT
#

run: binary/selenium-server-1.0.3
	cd binary/selenium-server-1.0.3 ; LANG=C java -jar selenium-server.jar -interactive

binary/selenium-server-1.0.3: binary/selenium-remote-control-1.0.3.zip
	cd binary ; [ -d selenium-server-1.0.3 ] || unzip selenium-remote-control-1.0.3.zip

binary/selenium-remote-control-1.0.3.zip: binary
	cd binary ; wget -c http://selenium.googlecode.com/files/selenium-remote-control-1.0.3.zip

binary:
	mkdir binary
