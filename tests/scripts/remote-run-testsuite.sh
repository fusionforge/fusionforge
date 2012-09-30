#! /bin/sh
. tests/vmconfig/default
[ ! -f tests/vmconfig/$1 ] || .  tests/vmconfig/$1

if [ -z "$TEST_SUITE" ]
then
	echo "TEST_SUITE is undefined"
	exit 1
fi

export CURDIR=`pwd`
WORKDIR=$(cd $CURDIR/..; pwd)

# Take jenkins WORKSPACE value is running jenkins
# if not use WORKDIR
export WORKSPACE=${WORKSPACE:-$WORKDIR}

# Run tests
ssh root@$HOST "$TEST_HOME/scripts/phpunit.sh $TEST_SUITE"

# Get log result 
[ -d "$WORKSPACE/reports" ] || mkdir $WORKSPACE/reports
rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/

# Exit with test result
exit $(ssh root@$HOST cat /root/phpunit.exitcode)
