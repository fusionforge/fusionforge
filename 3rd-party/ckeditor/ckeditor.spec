Name: ckeditor
Version: 3.5.3
Release: 0
Summary: WYSIWYG Text and HTML Editor for the Web
License: GPL
Group: Development/Tools
URL: http://ckeditor.com/
Packager: Alain Peyrat <alain.peyrat@alcatel-lucent.com>
Source0: %{name}_%{version}.tar.gz
BuildRoot: /var/tmp/%{name}-buildroot
BuildArch: noarch
AutoReqProv: no

%define debug_package %{nil}

%description
CKEditor is a text editor to be used inside web pages. It's a WYSIWYG
editor, which means that the text being edited on it looks as similar
as possible to the results users have when publishing it. It brings
to the web common editing features found on desktop editing applications
like Microsoft Word and OpenOffice.

%prep

%setup -c -n %{name}-%{version}

cat > ckeditor.conf <<'EOF'
Alias /%{name}/ %{_datadir}/%{name}/
<Directory %{_datadir}/%{name}/>
	Allow from all
</Directory>
EOF

%build

%install
%{__rm} -rf %{buildroot}
%{__install} -d -m0755 %{buildroot}%{_datadir}/
%{__cp} -ar * %{buildroot}%{_datadir}/
%{__install} -d %{buildroot}%{_sysconfdir}/httpd/conf.d/
%{__mv} %{buildroot}%{_datadir}/ckeditor.conf %{buildroot}%{_sysconfdir}/httpd/conf.d/

%clean
%{__rm} -rf %{buildroot}

%files
%defattr(-, root, root, 0755)
%{_datadir}/%{name}
%{_sysconfdir}/httpd/conf.d/ckeditor.conf

%changelog
* Mon Apr 18 2011 Alain PEYRAT <alain.peyrat@alcatel-lucent.com> - 3.5.3-0
- Updated for 3.5.3

* Mon Apr 04 2011 Alain PEYRAT <alain.peyrat@alcatel-lucent.com> - 3.5.2-0
- Initial package.
