OLDPACKAGE=gforge
PACKAGE=fusionforge
SRCPACKAGE=fusionforge
FORGENAME=FusionForge

SED_REPLACE=sed -e 's/@PACKAGE@/$(PACKAGE)/g' -e 's/@SRCPACKAGE@/$(SRCPACKAGE)/g' -e 's/@OLDPACKAGE@/$(OLDPACKAGE)/g' -e 's/@FORGENAME@/$(FORGENAME)/g'

../../debian/control: $(wildcard *)
	ls [0-9][0-9][0-9]* | grep -v shortdesc | grep -v scmcpold | while read file ; do cat $${file}; if [ -f $${file}.shortdesc ] ; then cat AAAdesc; echo ' .'; cat $${file}.shortdesc; fi; echo '';  done | $(SED_REPLACE) > $@
