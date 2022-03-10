include ../../GNUmakefile.defaults

# Detection of distro-specific libexecdir for git-http-backend
gitlibexecdir=$(shell if [ -e /etc/redhat-release ]; then echo '/usr/libexec/git-core'; elif [ -e /etc/debian_version ]; then echo '/usr/lib/git-core'; else echo '/usr/lib/git'; fi)

all:
	@echo "No default action"

install:
	install -d -m 755 $(DESTDIR)$(pkgdatadir)/plugins/scmgit/libexec/
	ln -nfs $(gitlibexecdir)/git-http-backend $(DESTDIR)$(pkgdatadir)/plugins/scmgit/libexec/
