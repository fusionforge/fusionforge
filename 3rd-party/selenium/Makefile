#
# http://seleniumhq.org/
# http://seleniumhq.org/projects/remote-control/
# http://selenium.googlecode.com/svn/trunk/remote/server/
# take http://selenium.googlecode.com/files/selenium-remote-control-1.0.3.zip
# see tests/func/README.TXT
#
SELENIUMDL=http://selenium.googlecode.com/files/selenium-remote-control-1.0.3.zip

run: binary/selenium-server-1.0.3
	cd binary/selenium-server-1.0.3 ; LANG=C java -jar selenium-server.jar -interactive

getselenium: binary/selenium-server-current/selenium-server.jar

binary/selenium-server-current/selenium-server.jar: binary/selenium-server-1.0.3
	ln -s selenium-server-1.0.3 binary/selenium-server-current

binary/selenium-server-1.0.3: binary/selenium-remote-control-1.0.3.zip
	cd binary ; [ -d selenium-server-1.0.3 ] || unzip selenium-remote-control-1.0.3.zip

binary/selenium-remote-control-1.0.3.zip: binary
	if [ ! -f $@ ];then cd binary;wget -q -c $(SELENIUMDL);else echo "$@ already downloaded";fi

binary:
	mkdir binary

svnexport:
	svn export http://selenium.googlecode.com/svn/trunk/remote/server selenium-server
	#svn export http://selenium.googlecode.com/svn/trunk selenium

getpackage:
	sudo apt-get install maven-debian-helper
