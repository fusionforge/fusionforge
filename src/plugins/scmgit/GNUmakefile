include ../../GNUmakefile.defaults

# Detection of distro-specific libexecdir for git-http-backend
gitlibexecdir=$(shell if [ -e /etc/redhat-release ]; then echo '/usr/libexec'; else echo '/usr/lib'; fi)

all:
	@echo "No default action"

install:
	install -d -m 755 $(DESTDIR)$(pkgdatadir)/plugins/scmgit/libexec/
	ln -nfs $(gitlibexecdir)/git-core/git-http-backend $(DESTDIR)$(pkgdatadir)/plugins/scmgit/libexec/
