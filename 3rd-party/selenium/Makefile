#
# http://seleniumhq.org/
# http://seleniumhq.org/projects/remote-control/
# http://selenium.googlecode.com/svn/trunk/remote/server/
# see tests/func/README.TXT
#

S_VERSION=2.21.0

run: selenium-server.jar
	LANG=C java -jar selenium-server.jar -interactive

selenium-server.jar:
	wget -c -O $@ http://selenium.googlecode.com/files/selenium-server-standalone-$(S_VERSION).jar

svnexport:
	svn export http://selenium.googlecode.com/svn/trunk/remote/server selenium-server
	#svn export http://selenium.googlecode.com/svn/trunk selenium

getpackage:
	sudo apt-get install maven-debian-helper

getselenium: selenium-server.jar

clean:
	rm -f selenium-server.jar

botbuild: getselenium

botclean:
	@echo "Nothing to do"
