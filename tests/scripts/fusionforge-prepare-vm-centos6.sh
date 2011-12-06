#!/bin/sh
. tests/scripts/common-functions
. tests/scripts/common-vm

get_config $@
destroy_vm -t centos6 $@
start_vm_if_not_keeped -t centos6 $@

setup_dag_repo $@

sleep 5

ssh root@$HOST 'yum upgrade -y'

ssh root@$HOST 'yum install -y php php-cli php-pear php-xml java firefox vnc-server'

# Install phpunit inside the vps.
ssh root@$HOST 'pear config-set auto_discover 1'
ssh root@$HOST 'pear upgrade pear'
ssh root@$HOST 'pear install pear.phpunit.de/PHPUnit'

ssh root@$HOST 'pear upgrade --force PEAR'
ssh root@$HOST 'pear install --alldeps phpunit/PHPUnit-3.4.1'

ssh root@$HOST 'yum clean all'

ssh root@$HOST 'perl -spi -e "s/^X11Forwarding no/X11Forwarding yes/" /etc/ssh/sshd_config'

sudo /root/save_as_template_vz.sh centos-6-x86-test

stop_vm_if_not_keeped -t centos6 $@
