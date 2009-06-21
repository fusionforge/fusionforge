#!/bin/sh
#
# This file is used to convert $GLOBALS['Language']->getText('str1','str2') to gettext("English String from tab(str1,str2)")
# Copyright Christian Bayle <bayle@debian.org> 2009
# Licenced as GPL v2 or next or Affero GPL to your courtesy
# Thanks to PK for perl regexp help
# 
# Interesting part is to point non translatable strings, that should maybe be modified  before operation
# not sure it is useable as is as in only replace strings for english.
# May be interesting to use in conjunction with Novaforge tab2po
#
# Usage:
# Just run in trunk dir, translated repository will be in ../trunk.gettext 
#
readallentab()
{
	find . -name "*.tab" | grep -v '.svn' | grep en_US | xargs cat
	find . -name "Base.tab" | grep -v '.svn' | xargs cat
}

substitute()
{
	var1="\\$\\Q$1\\E"
	var2="$2"
	#perl -e print quotemeta "$2"
	#echo "Converting in $3"
	echo "+++ $var1 ==> $var2 +++"
	#perl -pi -e "s/$var1/$var2/g" $3
	#perl -pi -e "s{$var1}{$var2}go" $3
	perl -pi -e "s{$var1}{$var2}sg" $3
}

decode()
{
	count=`grep "$1	$2	" alltab.txt | wc -l` 
	case $count in
		0)
			echo "ERROR: translation not found in $3 for:"
			echo "------------------------------------"
			echo "$1	$2"
			echo "------------------------------------"
			;;
		1)
			strn="`grep  \"$1	$2	\" alltab.txt | cut -d'	' -f3`"
			case $strn in
				*\$*)
					strns=`echo "$strn" | sed 's/\$./%s/g'` 
					newstrn="gettext(\"$strns\","
					#grep "'$1'.*'$2'" $file | sed "s/.*\(GLOBALS.*('$1'.*'$2',\).*/\1/"| sort -u | while read oldstrn
					grep "'$1'.*'$2'" $file | sed "s/.*\(GLOBALS\['Language'\].[^(]*('$1'.*'$2',\).*/\1/"| sort -u | while read oldstrn
					do
						#echo "== \$$oldstrn ==> $newstrn =="
						substitute "$oldstrn" "$newstrn" "$3"
					done
					;;
				*)
					newstrn="gettext(\"$strn\")"
					#grep "'$1'.*'$2'" $file | sed "s/.*\(GLOBALS.*('$1'.*'$2')\).*/\1/"| sort -u | while read oldstrn
					grep "'$1'.*'$2'" $file | sed "s/.*\(GLOBALS\['Language'\].[^(]*('$1'.*'$2')\).*/\1/"| sort -u | while read oldstrn
					do
						#echo "== \$$oldstrn ==> $newstrn =="
						substitute "$oldstrn" "$newstrn" "$3"
					done
					;;
				
			esac
			;;
		*)
			echo "ERROR translation found several time in $3 for"
			echo "------------------------------------"
			grep "$1	$2	" alltab.txt
			echo "------------------------------------"
			;;
	esac
}

if [ ! -f alltab.txt ] 
then
	readallentab > alltab.txt
	# I have to double backquote to have this working, probably because of shell interaction
	perl -pi -e 's/"/\\\\"/g' alltab.txt
fi

target=`pwd`.gettext

if [ ! -d "$target" ]
then
	echo "Copying tree in $target"
	find . | grep -v '/.svn' | cpio -pdumvB $target
fi

find $target -name "*.php" | grep -v '/.svn' | while read file
do
	found="0"
	#grep "getText(" $file | sed "s/.*getText.[^']*'\(.[^']*\)'.[^']*'\(.[^']*\)'.*/\1	\2/g" | while read key1 key2
	#grep "getText(" $file | sed "s/.*getText('\(.[^']*\)'.[^']*'\(.[^']*\)'.*/\1	\2/g" | while read key1 key2
	#
	perl -pi -e "s{\\QLanguage->getText\\E}{GLOBALS['Language']->getText}sg" $file
	#
	#grep "getText('.[^']*'.[^']*'.[^']*'.*" $file | sed "s/.*getText('\(.[^']*\)'.[^']*'\(.[^']*\)'.*/\1	\2/g" | while read key1 key2
	grep "GLOBALS\['Language'\]->getText('.[^']*'.[^']*'.[^']*'.*" $file | sed "s/.*GLOBALS\['Language'\]->getText('\(.[^']*\)'.[^']*'\(.[^']*\)'.*/\1	\2/g" | while read key1 key2
	do
		if [ "$found" = "0" ] 
		then 
			echo "======== $file ========"
		fi
		found="1"		
		decode $key1 $key2 $file
		#echo  "$key1 $key2"
	done
	if [ "$found" = "1" ] 
	then
		php -l $file | grep -v "No syntax errors detected"
	fi
done
