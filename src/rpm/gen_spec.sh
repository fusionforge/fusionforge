#!/bin/bash -e

version=$1
snapshot=$2 # e.g. '+201408281835'; spec needs static tarball extract dir
if [ -z "$version" ]; then version=$(make version); fi
if [ -z "$autobuild" ]; then autobuild=''; fi

(
    for i in $(sed -n 's/^%package plugin-//p' rpm/plugins); do
	sed -n -e '/^#/d' -e "/^%package plugin-$i/,/^$/p" rpm/plugins \
	    | grep -v ^$ \
	    | sed 's/Requires:\(.*\)/Requires: %{name}-common = %{version},\1/'
	#echo "Group: Development/Tools"
	php utils/plugin_pkg_desc.php $i rpm
	cat <<-EOF
	%files plugin-$i -f plugin-$i.rpmfiles
	%post plugin-$i
	%{_datadir}/%{name}/post-install.d/common/plugin.sh $i configure
	%preun plugin-$i
	if [ \$1 -eq 0 ] ; then %{_datadir}/%{name}/post-install.d/common/plugin.sh $i remove; fi
	EOF
	echo
	echo
    done
) \
| sed \
    -e "s/@version@/$version/" \
    -e "s/@snapshot@/$snapshot/" \
    -e '/^@plugins@/ { ' -e 'ecat' -e 'd }' \
    rpm/fusionforge.spec.in > fusionforge.spec
