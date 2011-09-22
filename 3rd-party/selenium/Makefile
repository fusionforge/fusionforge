#
# http://seleniumhq.org/
# http://seleniumhq.org/projects/remote-control/
# http://selenium.googlecode.com/svn/trunk/remote/server/
# see tests/func/README.TXT
#

S_VERSION=2.6.0

run: selenium-server-standalone-$(S_VERSION).jar
	LANG=C java -jar selenium-server-standalone-$(S_VERSION).jar -interactive

selenium-server-standalone-$(S_VERSION).jar:
	wget -c http://selenium.googlecode.com/files/selenium-server-standalone-$(S_VERSION).jar

svnexport:
	svn export http://selenium.googlecode.com/svn/trunk/remote/server selenium-server
	#svn export http://selenium.googlecode.com/svn/trunk selenium

getpackage:
	sudo apt-get install maven-debian-helper
