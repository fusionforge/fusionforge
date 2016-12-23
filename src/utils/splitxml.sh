#!/bin/bash
if [ $# -lt 1 ]; then
	echo "Usage: $0 inputfile"
	exit 1
fi
if [ ! -f "$1" ]; then
	echo "File $1 does not exist or is not a regular file"
	exit 1
fi
if ! hash xmlstarlet 2>/dev/null; then
	echo "xmlstarlet is required, please install it."
	exit 1
fi
path=$(dirname $1)
base=$(basename $1)
extension=${base##*.}
filename=${base%.*}
echo "splitting file:"
echo "filename=$filename"
echo "path=$path" 
echo "base=$base"
echo "extension=$extension"

# get root & root children list
unset list
list=( $(xmlstarlet el -d2 $1) )
# no child -> exit
case ${#list[@]} in
	0)	echo "No root, No child, exit"
		echo "exit from file $filename"
		exit 0;;
	1)	extension="${1##*.}"
		echo "No child, exit";
		if [$extension == "tmp"]; then
			root=${list[@]:0:1}
			mv $1 $root.xml
			echo "create file $root.xml"
		fi
		echo "exit from file $filename"
		exit 0;;
esac

root=${list[@]:0:1}
echo "Root :$root"
unset list2
list2=(${list[@]:1})
unset children
children=(${list2[@]#$root/})

# get root tag
tagline=$(grep -m 1 "< *$root" $1)
tag=$(expr "$tagline" : '.*\(< \?'"$root"'[^><]*>\).*')
# get number of root attributes 
noa=$(xmlstarlet sel -t -v "count(/*/@*)" $1)
echo "root number of attributes : $noa"

echo "children list:"
echo ${children[@]}

# several children with the same name -> exit
xmlstarlet el $1 | grep "^[^\/]*\/[^\/]*$" >$path/$filename.el2
for child in "${children[@]}"; do
	echo "count children named $child"
	nb=$(grep -c "$root/$child" $path/$filename.el2)
	if [ "$nb" -gt 1 ]; then
		echo "several ($nb) children named $child -> exit"
		if [ $extension == "tmp" ]; then
			mv $path/$filename.tmp $path/$filename.xml
			echo "create file $path/$filename.xml"
		fi
		rm -f $path/$filename.el2
		echo "exit from file $filename"
		exit 0
	fi
done
rm -f $path/$filename.el2

if [ "$noa" -gt 0 ]; then
	echo $tag>$path/$root.xml
fi

# treatment for each child
for child in "${children[@]}"; do
	echo "generate file for child: $child"
	./getelement.sh $1 $child $path/$child.tmp
	echo "file generated"
	unset list
	# get children of chide
	list=( $(xmlstarlet el -d2 $path/$child.tmp) )
	# if children -> create folder + call splitxml on each child
	nblist=${#list[@]} 
	if [ ${#list[@]} -gt 1 ]; then
		mkdir $path/$child
		if [[ "$child" == *Application ]]; then
			echo "child $child is an Application -> no split"
			mv $path/$child.tmp $path/$child/$child.xml
		else
			echo "children for $child -> recursive call"
			mv $path/$child.tmp $path/$child/
			#recursive call on $path/$child/$child.tmp
			echo "*******************************************************"
			echo "CALL of:"
			echo "./splitxml.sh $path/$child/$child.tmp"
			echo "*******************************************************"
			./splitxml.sh $path/$child/$child.tmp
			echo "*******************************************************"
			echo "END of:"
			echo "./splitxml.sh $path/$child/$child.tmp"
			echo "*******************************************************"
		fi
	else
		echo "No child for $child -> continue the loop"
		if [ ! -f "$path/$root.xml" ]; then
			echo $tag>$path/$root.xml
		fi
		echo "include $child data in $path/$root.xml file"
		cat $path/$child.tmp>>$path/$root.xml
		rm -f $path/$child.tmp
	fi
done
if [ -f "$path/$root.xml" ]; then
	echo "</$root>">>$path/$root.xml
fi
if [ -f "$path/$root.tmp" ]; then
	rm -f $path/$root.tmp
fi
exit 0