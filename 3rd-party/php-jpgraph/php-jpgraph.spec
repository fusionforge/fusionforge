# $Id: php-jpgraph.spec 4308 2006-04-21 22:20:20Z dries $
# Authority: dag

%define real_name jpgraph

Summary: OO Graph Library for PHP
Name: php-jpgraph
Version: 1.5.2
Release: 1
License: GPL
Group: Development/Languages
URL: http://www.aditus.nu/jpgraph/

Source: http://members.chello.se/jpgraph/jpgdownloads/jpgraph-%{version}.tar.gz
BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root

BuildArch: noarch
Requires: php
Obsoletes: jpgraph
Provides: jpgraph

Patch0: libphp-jpgraph_1.5.2-12.diff


%description
JpGraph is an OO class library for PHP 4.1 (or higher). JpGraph makes it
easy to draw both "quick and dirty" graphs with a minimum of code and
complex professional graphs which requires a very fine grain control.

JpGraph is equally well suited for both scientific and business type of graphs.

An important feature of the library is that it assigns context sensitive
default values for most of the parameters which radically minimizes the
learning curve. The features are there when you need it - they don't get
in your way when you don't need them!

%package docs
Summary: Documentation for package %{name}
Group: Documentation

%description docs
JpGraph is an OO class library for PHP 4.1 (or higher). JpGraph makes it
easy to draw both "quick and dirty" graphs with a minimum of code and
complex professional graphs which requires a very fine grain control.

This package includes the documentation for %{name}.

%prep
%setup -n %{real_name}-%{version}
%patch0 -p1

### Change the default TTF_DIR to Red Hat's TTF_DIR.
%{__perl} -pi.orig -e 's|/usr/X11R6/lib/X11/fonts/truetype/|/usr/X11R6/lib/X11/fonts/TTF/|' src/jpgraph.php

%build

%install
%{__rm} -rf %{buildroot}
%{__install} -d -m0755 %{buildroot}%{_datadir}/%{real_name}
%{__install} -p -m0644 src/jpgraph*.php %{buildroot}%{_datadir}/%{real_name}/

%clean
%{__rm} -rf %{buildroot}

%files
%defattr(-, root, root, 0755)
%doc README
%{_datadir}/%{real_name}/

%files docs
%defattr(-, root, root, 0755)
%doc src/Examples/

%changelog
* Mon Mar 29 2010 Alain Peyrat <aljeux@free.fr> - 1.5.2-1
- Initial package, spec taken from DAG.
