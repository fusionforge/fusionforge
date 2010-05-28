#! /bin/sh
[ -f svn-gource-1.2.tar.gz ] || wget http://gource.googlecode.com/files/svn-gource-1.2.tar.gz 
[ -f svn-gource.py ]  || tar xvzf svn-gource-1.2.tar.gz
[ -f fusionforge-svnlog ] || svn log --verbose --xml > fusionforge-svnlog
[ -f fusionforge-gourcelog ] || python svn-gource.py --filter-dirs fusionforge-svnlog > fusionforge-gourcelog

SECONPERDAY="--seconds-per-day 0.001"	# default 1
MAXFILES="--max-files 50"		# default 1000
STARTPOSITION="--start-position 0.5"
STARTPOSITION=""

FFBITRATE="-b 3000K"
FFRATE="-r 60"

FFFORMAT="flv"

gource --stop-at-end \
	--disable-progress \
	--file-idle-time 1 \
	--disable-bloom \
	$SECONPERDAY \
	$MAXFILES \
	--date-format "%d/%m/%Y" \
	--user-scale 2 \
	$STARTPOSITION \
	--max-user-speed 500 \
	--highlight-all-users \
	--log-format custom \
	-800x600 \
	--output-ppm-stream - \
	fusionforge-gourcelog \
		| ffmpeg -y $FFBITRATE $FFRATE -f image2pipe -vcodec ppm -i - -vcodec $FFFORMAT fusionforge-gource.$FFFORMAT
