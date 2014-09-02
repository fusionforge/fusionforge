Name: selenium
Version: 2.42.2
Release: 2
Summary: Selenium server Jar
License: Apache 2.0

#%define _rpmdir ../
%define _rpmdir %{_topdir}/RPMS/
%define _rpmfilename %%{NAME}-%%{VERSION}-%%{RELEASE}.%%{ARCH}.rpm
%define _unpackaged_files_terminate_build 0

%description
Not a real package, only contains selenium server jar

%files
%dir "/"
%dir "/usr/"
%dir "/usr/share/"
%dir "/usr/share/selenium/"
"/usr/share/selenium/selenium-server.jar"

%install
[ -d $RPM_BUILD_ROOT%{_datadir}/selenium/ ] || mkdir -p $RPM_BUILD_ROOT%{_datadir}/selenium/
%{__cp} %{_topdir}/SPEC/selenium-server.jar $RPM_BUILD_ROOT%{_datadir}/selenium/
