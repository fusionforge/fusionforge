for elem in tables sequences indices views
do
diff desc2.5upd/$elem desc2.6fresh/$elem | grep '^>' | sed 's/^> //' | while read record
do
	#echo $elem $record
	cat desc2.6fresh/$elem.dump/$record
done
done
