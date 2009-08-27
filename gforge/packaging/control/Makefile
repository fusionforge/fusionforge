../../debian/control: $(wildcard [0-9][0-9][0-9]*)
	ls [0-9][0-9][0-9]* | grep -v shortdesc | while read file ; do cat $${file}; if [ -f $${file}.shortdesc ] ; then cat AAAdesc; echo ' .'; cat $${file}.shortdesc; fi; echo '';  done | sed 's/@PACKAGE@/gforge/g' > ../../debian/control
