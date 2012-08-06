# jsmin http://www.crockford.com/javascript/jsmin.html
ALLSRC=mobrowser.js modomevent3.js modomt.js modomext.js getobject2.js xmlextras.js acdropdown.js

../../moacdropdown.js: $(ALLSRC)
	cat $(ALLSRC) > tmp.js
	jsmin < tmp.js > $@
	rm tmp.js

# perl -pi~ -e 's/^\s+//; s(//.*)();' $@
