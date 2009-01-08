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

# Misc strings and value

# Versions of RPMs provided by us

# Versions of RPMs provided by the distribution

# Sources and patches
Source0:	getdist-%{version}.tar.gz

# Packages required for build

# Build architecture
BuildArch:	noarch

# Build root
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-buildroot

#
# Main package
#

Summary:	Display ditribution identifier
Name:		getdist
Version:	1.3
Release:	1
License:	GPL
Group:		System Environment/Base
URL:		http://novaforge.frec.bull.fr/projects/novaforge/

%description
Display an identifier matching the distribution installed on this computer.

%prep
%setup -q

%build

%install
[ "%{buildroot}" != "/" ] && rm -rf %{buildroot}
%{__install} -d %{buildroot}%{_bindir}
%{__install} scripts/getdist %{buildroot}%{_bindir}/

%clean
[ "%{buildroot}" != "/" ] && %{__rm} -rf %{buildroot}

%files
%defattr(-,root,root)
%doc LICENSE
%attr(0755,root,root) %{_bindir}/getdist

%changelog
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
