%define peardir /usr/share/pear
%define xmldir  /var/lib/pear

Summary: PEAR: WebDAV Server Baseclass
Name: php-pear-HTTP_WebDAV_Server
Version: 1.0.0RC5
Release: 1
License: New BSD License
Group: Development/Libraries
Source0: http://pear.php.net/get/HTTP_WebDAV_Server-%{version}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root
URL: http://pear.php.net/package/HTTP_WebDAV_Server
#BuildRequires: PEAR::PEAR >= 1.4.7

BuildArch: noarch

Requires: php-pear

%description
RFC2518 compliant helper class for WebDAV server implementation.

%prep
%setup -c -T
pear -q -c pearrc \
        -d php_dir=%{peardir} \
        -d doc_dir=/docs \
        -d bin_dir=%{_bindir} \
        -d data_dir=%{peardir}/data \
        -d test_dir=%{peardir}/tests \
        -d ext_dir=%{_libdir} \
        -s

%build

%install
rm -rf %{buildroot}
pear channel-update pear.php.net
pear -c pearrc install --nodeps --packagingroot %{buildroot} %{SOURCE0}

# Clean up unnecessary files
rm pearrc
rm %{buildroot}/%{peardir}/.filemap
rm %{buildroot}/%{peardir}/.lock
rm -rf %{buildroot}/%{peardir}/.registry
rm -rf %{buildroot}%{peardir}/.channels
rm %{buildroot}%{peardir}/.depdb
rm %{buildroot}%{peardir}/.depdblock

mv %{buildroot}/docs .


# Install XML package description
mkdir -p %{buildroot}%{xmldir}
tar -xzf %{SOURCE0} package.xml
cp -p package.xml %{buildroot}%{xmldir}/HTTP_WebDAV_Server.xml

%clean
rm -rf %{buildroot}

%post
pear install --nodeps --soft --force --register-only %{xmldir}/HTTP_WebDAV_Server.xml 2>&1 >/dev/null

%postun
if [ "$1" -eq "0" ]; then
    pear uninstall --nodeps --ignore-errors --register-only pear.php.net/HTTP_WebDAV_Server 2>&1 >/dev/null
fi

%files
%defattr(-,root,root)
%doc docs/HTTP_WebDAV_Server/*
%{peardir}/*
%{xmldir}/HTTP_WebDAV_Server.xml

%changelog
* Sat Nov 20 2010 Alain Peyrat <aljeux@free.fr>  - 1.0.0RC5
- Initial packaging
