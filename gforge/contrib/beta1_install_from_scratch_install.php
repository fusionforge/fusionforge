#!/usr/local/bin/php
<?php
	//need to change arrays to use hashes so you don't have to worry about keeping things in order so much
	//figure out how to prevent it from recompiling all the time or even checking
	//don't forget the trailing /
	$SRC_DIR	= "/usr/local/src/INSTALL/";

	$options = array();
	for($i = 1; $i < $argc; $i++)
	{
		list($option,$value) = split("=",$argv[$i]);
		$options[$option] = $value;
	}

	if(array_key_exists("-h",$options) || array_key_exists("all",$options))
	{
		print "Usage: ./install.php [args...]\n";
		print "  -a		Download and compile all packages\n";
		print "  -c <File>	Only compile package, must already be downloaded\n";
		print "  -d <File>	Only download package, do not compile\n";
		print "  -h		This help\n";
		print "  -D		Specify the directory to store the files when downloading\n";
		print "  -S		Specify where to move and compile files\n";
	}

	if(array_key_exists("gforge",$options) || array_key_exists("all",$options))
	{
		$gforge 	= array();
		$gforge[]	= "gforge-3.0b1.tar.bz2";
		$gforge[]	= "gforge-3.0b1";
		$gforge[]	= "local";
		$gforge[] 	= "http://gforge.org/download.php/31/gforge-3.0b1.tar.bz2";
		$gforge[]	= "bzip2 -dc gforge-3.0b1.tar.bz2 | tar xf -";

		if(!file_exists("$SRC_DIR"."$gforge[0]"))
		{
			print shell_exec("cd $SRC_DIR; wget $gforge[3]");
		}

		//prevent install recursion by mv gforge-3.0b1 into /var/www/gforge-3.0 if /var/www/gforge-3.0 was already there
		if(!file_exists("/var/www/gforge-3.0"))
		{
			print shell_exec("cd $SRC_DIR;$gforge[4];mv gforge-3.0b1 /var/www/gforge-3.0/; chown -R apache:apache /var/www/gforge-3.0");
		}

		//need to edit this file, but for now, just copy our over it
		print shell_exec("mkdir /etc/gforge/; cp /var/www/gforge-3.0/etc/local.inc /etc/gforge/local.inc");
	}

	if(array_key_exists("zlib",$options) || array_key_exists("all",$options))
	{
		$zlib 		= array();	
		$zlib[]		= "zlib-1.1.4.tar.gz";						//file name		0
		$zlib[]		= "zlib-1.1.4";							//un tarred		1
		$zlib[]		= "local";							//local file location	2
		$zlib[]		= "ftp://swrinde.nde.swri.edu/pub/png/src/zlib-1.1.4.tar.gz";	//remote		3
		$zlib[]		= "gunzip zlib-1.1.4.tar.gz";					//gunzip		4
		$zlib[]		= "tar xvf zlib-1.1.4.tar";					//untar			5
		$zlib[]		= "./configure";						//configure		6
		$zlib[]		= "make test";							//build			7
		$zlib[]		= "make install";						//install		8

		if(!file_exists("$SRC_DIR"."$zlib[0]") && !file_exists("$SRC_DIR"."$zlib[1]".".tar"))
		{
			print shell_exec("cd $SRC_DIR; wget $zlib[3]");	
		}
	
		print shell_exec("cd $SRC_DIR;$zlib[4];$zlib[5];cd zlib-1.1.4;$zlib[6];$zlib[7];$zlib[8]");
	}

	if(array_key_exists("libpng",$options) || array_key_exists("all",$options))
	{
		$libpng 	= array();
		$libpng[]	= "libpng-1.2.5.tar.gz";
		$libpng[]	= "libpng-1.2.5";
		$libpng[]	= "local";
		$libpng[]	= "ftp://swrinde.nde.swri.edu/pub/png/src/libpng-1.2.5.tar.gz";
		$libpng[]	= "gunzip libpng-1.2.5.tar.gz";
		$libpng[]	= "tar xvf libpng-1.2.5.tar";
		$libpng[]	= "cp scripts/makefile.std makefile";
		$libpng[]	= "make test";
		$libpng[]	= "make install";
	
		if(!file_exists("$SRC_DIR"."$libpng[0]") && !file_exists("$SRC_DIR"."$libpng[1]".".tar"))
		{	
			print shell_exec("cd $SRC_DIR; wget $libpng[3]");
		}
		
		print shell_exec("cd $SRC_DIR;$libpng[4];$libpng[5];cd libpng-1.2.5;$libpng[6];$libpng[7];$libpng[8]");
	}

	if(array_key_exists("libjpeg",$options) || array_key_exists("all",$options))
	{
		$libjpeg 	= array();
		$libjpeg[]	= "jpegsrc.v6b.tar.gz";
		$libjpeg[]	= "jpeg-6b";
		$libjpeg[]	= "local";
		$libjpeg[]	= "http://www.ijg.org/files/jpegsrc.v6b.tar.gz";
		$libjpeg[]	= "gunzip jpegsrc.v6b.tar.gz";
		$libjpeg[]	= "tar xvf jpegsrc.v6b.tar";
		$libjpeg[]	= "./configure";
		$libjpeg[]	= "make";
		$libjpeg[]	= "make install";
	
		if(!file_exists("$SRC_DIR"."$libjpeg[0]") && !file_exists("$SRC_DIR"."jpegsrc.v6b.tar"))
		{	
			print shell_exec("cd $SRC_DIR; wget $libjpeg[3]");
		}
	
		print shell_exec("cd $SRC_DIR;$libjpeg[4];$libjpeg[5];cd jpeg-6b;$libjpeg[6];$libjpeg[7];$libjpeg[8]");
	}

	if(array_key_exists("apache",$options) || array_key_exists("all",$options))
	{
		$apache 	= array();
		$apache[]	= "apache_1.3.27.tar.gz";
		$apache[]	= "apache_1.3.27";
		$apache[]	= "local";
		$apache[]	= "http://apache.ttlhost.com/httpd/apache_1.3.27.tar.gz";
		$apache[]	= "gunzip apache_1.3.27.tar.gz";
		$apache[]	= "tar xvf apache_1.3.27.tar";
		$apache[]	= "./configure --prefix=/usr/local/apache --enable-module=so --server-uid=apache --server-gid=apache";
		$apache[]	= "make";
		$apache[]	= "make install";
	
		if(!file_exists("$SRC_DIR"."$apache[0]") && !file_exists("$SRC_DIR"."$apache[1]".".tar"))
		{
			print shell_exec("cd $SRC_DIR; wget $apache[3]");
		}
	
		print shell_exec("/usr/sbin/adduser -M apache");
		print shell_exec("cd $SRC_DIR;$apache[4];$apache[5];cd apache_1.3.27;make distclean;$apache[6];$apache[7];$apache[8]");
		//add apache httpd.conf config stuff
		//for now be lazy and just copy the stuff in cvs (http) and hand modify, after
		//all Rome was not automized over night
		
	}

	if(array_key_exists("postgres",$options) || array_key_exists("all",$options))
	{	
		$postgres 	= array();
		$postgres[]	= "postgresql-7.3.2.tar.gz";
		$postgres[]	= "postgresql-7.3.2";
		$postgres[]	= "local";
		$postgres[]	= "ftp://ftp.at.postgresql.org/db/www.postgresql.org/pub/source/v7.3.2/postgresql-7.3.2.tar.gz";
		$postgres[]	= "gunzip postgresql-7.3.2.tar.gz";
		$postgres[]	= "tar xvf postgresql-7.3.2.tar";
		$postgres[]	= "./configure";
		$postgres[]	= "make";
		$postgres[]	= "make install";
	
		if(!file_exists("$SRC_DIR"."$postgres[0]") && !file_exists("$SRC_DIR"."$postgres[1]".".tar"))
		{
			print shell_exec("cd $SRC_DIR; wget $postgres[3]");
		}

		print shell_exec("cd $SRC_DIR;$postgres[4];$postgres[5];cd postgresql-7.3.2;$postgres[6];$postgres[7];$postgres[8]");
		print shell_exec("/usr/sbin/adduser postgres;mkdir /usr/local/pgsql/data;chown postgres:postgres /usr/local/pgsql/data");
		print shell_exec("su - postgres -c \"/usr/local/pgsql/bin/initdb -D /usr/local/pgsql/data\"");
		//edit /usr/local/pgsql/data/pg_hba.com here

//FIX need to add PGDATA setting and semicolons after sql
print shell_exec("su - postgres -c \"echo 'export PGDATA=/usr/local/pgsql/data' >> .bashrc \"");
print shell_exec("su - postgres -c \"echo 'export PATH=${PATH}:/usr/local/pgsql/bin' >> .bashrc \"");
		print shell_exec("cp /usr/local/pgsql/data/pg_hba.conf /usr/local/pgsql/data/pg_hba.conf.orig");
		print shell_exec("cd /usr/local/pgsql/data/;head -n 46 pg_hba.conf >> temp;echo \"local all all trust\" >> temp;echo \"host all all 127.0.0.1 255.255.255.255 crypt\" >> temp;mv -f temp pg_hba.conf");
		//must start postgres here
		print shell_exec("su - postgres -c 'pg_ctl start -W -D /usr/local/pgsql/data -l /usr/local/pgsql/data/logfile -o \"-i -h 127.0.0.1\"'");
		//create the gforge db stuff here	
		print shell_exec("su - postgres -c \"createuser -d -A gforge\"");
		print shell_exec("psql -U postgres -d template1 -c \"update pg_shadow set passwd='postgres' where usename='postgres'\"");
		print shell_exec("psql -U postgres -d template1 -c \"update pg_shadow set passwd='gforge' where usename='gforge'\"");
		print shell_exec("psql -U postgres -d template1 -c  \"update pg_shadow set usesuper = 't' where usename = 'gforge'\"");
		print shell_exec("su - postgres -c \"createdb -U gforge gforge\"");
		print shell_exec("su - postgres -c \"createlang plpgsql gforge\"");
	}

	if(array_key_exists("php",$options) || array_key_exists("all",$options))
	{
		$php 		= array();
		$php[]		= "php-4.3.1.tar.gz";
		$php[]		= "php-4.3.1";
		$php[]		= "local";
		$php[]		= "http://www.php.net/get/php-4.3.1.tar.gz/from/us3.php.net/mirror";
		$php[]		= "gunzip php-4.3.1.tar.gz";
		$php[]		= "tar xvf php-4.3.1.tar";
		$php[]		= "./configure --with-apxs=/usr/local/apache/bin/apxs --with-gd --with-pgsql --with-jpeg-dir=/usr/local/lib --with-png-dir=/usr/local/lib --with-zlib-dir=/usr/local/lib --enable-sockets --enable-ftp";
		$php[]		= "make";
		$php[]		= "make install";
	
		if(!file_exists("$SRC_DIR"."$php[0]") && !file_exists("$SRC_DIR"."$php[1]".".tar"))
		{
			print shell_exec("cd $SRC_DIR; wget $php[3]");
		}

		print shell_exec("cd $SRC_DIR;$php[4];$php[5];cd php-4.3.1;make distclean;$php[6];$php[7];$php[8];rm /usr/local/lib/php.ini; cp php.ini-dist /usr/local/lib/");	

		//add php.ini edit stuff here, but for now just copy the file in cvs and hand edit
		//after all how many times are we going to move this server
	}
?>
