#!/usr/bin/env bash

set -e

if ! type docker > /dev/null; then
    echo "Docker is required to run tests."
    exit 1
fi

args=$@
is_daemon=false

if [[ ${args:0:4} == "--md" ]]; then
  args=${args:4}
  is_daemon=true
fi

if [[ `docker ps` != *"mtr_mysql"* ]]; then
  if [ "$is_daemon" = true ] ; then
    echo "# Starting database for functional tests (as daemon)"
  else
    echo "# Starting database for functional tests"
  fi

  docker run -d -e MYSQL_ROOT_PASSWORD=123123 --name mtr_mysql mysql:5 > /dev/null
else
  echo "# MySQL container is already running, reusing it"
fi

echo ""

# MONOLITH_TEST_SUITE env variable is used by FunctionalTestClass
docker run \
-it \
--rm \
-v `pwd`:/mnt/tmp \
-w /mnt/tmp \
-e MONOLITH_TEST_SUITE=1 \
--link mtr_mysql:mysql \
modera/php7-fpm "vendor/bin/phpunit ${args}"

exit_code=$?

if [ "$is_daemon" = false ] ; then
  docker rm -f mtr_mysql > /dev/null
fi

exit $exit_code