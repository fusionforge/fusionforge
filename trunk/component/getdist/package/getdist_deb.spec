#---------------------------------------------------------------------------
# This file is part of the NovaForge project
# Novaforge is a registered trade mark from Bull S.A.S
# Copyright (C) 2007 Bull S.A.S.
# http://novaforge.org
#
# This file is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this file; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#---------------------------------------------------------------------------

[prep]

[control]

Package: getdist
Source: getdist-1.3.tar.gz
Version: 1.3
Section: System Environment/Base 
Priority: optional
Essential: no
Architecture: all
Maintainer: Gregory Cuellar <gregory.cuellar@bull.net>
Provides: GPL
Description: Display ditribution identifier.

[install]

# Binaries
BIN_INSTALL=`rpm --eval %{__install}`
BIN_CP=`rpm --eval %{__cp}`
BIN_MKDIR=/bin/mkdir
BIN_TAR=/bin/tar
BIN_RM=`rpm --eval %{__rm}`

# Variables
NAME=getdist
VERSION=1.2
BINDIR=`rpm --eval %{_bindir}`


# Setup
$BIN_TAR -xzf $NAME-$VERSION.tar.gz
cd $NAME-$VERSION

# Install
`[ -n $BUILDROOT -a $BUILDROOT != / ] && $BIN_RM -rf $BUILDROOT`

# Install /bin
$BIN_INSTALL -d $BUILDROOT$BINDIR
$BIN_INSTALL scripts/getdist $BUILDROOT$BINDIR/
cd ..

[clean]

# Binaries
BIN_RM=`rpm --eval %{__rm}`

`[ -n $BUILDROOT -a $BUILDROOT != / ] && $BIN_RM -rf $BUILDROOT`

[preinst]

[postinst]

[prerm]

[postrm]

[changelog]
* Fri Nov 7 2008 Gregory Cuellar <gregory.cuellar@bull.net> 1.3-1
- Add Debian

* Wed Nov 14 2007 Gilles Menigot <gilles.menigot@bull.net> 1.2-1
- Add GPL v2 license

* Mon Jun 04 2007 Gilles Menigot <gilles.menigot@bull.net> 1.1-2
- Moved to SVN repository

* Fri Mar 09 2007 Gilles Menigot <gilles.menigot@bull.net> 1.1-1
- Modify check order
- Add Aurora SPARC Linux

* Fri Feb 23 2007 Gilles Menigot <gilles.menigot@bull.net> 1.0-1
- Initial release
