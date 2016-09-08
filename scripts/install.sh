#!/usr/bin/env bash

RUNNER_GIT_DIR="mtr"

set -e

RUNNER_GIT_PATH=`pwd`"/mtr" # do not touch

if ! type docker > /dev/null; then
    echo "Docker is required to run tests."
    exit 1
fi

if [ ! -d "$RUNNER_GIT_PATH" ]; then
  echo "# Cloning and installing test-runner"

  git clone https://github.com/modera/tests-runner.git $RUNNER_GIT_PATH

  docker run \
  -it \
  --rm \
  -v $RUNNER_GIT_PATH:/mnt/tmp \
  -w /mnt/tmp \
  modera/php7-fpm "composer install"
fi

cp $RUNNER_GIT_DIR/scripts/phpunit.sh .

echo ""
echo "# Hooray! modera/tests-runner has been installed to ${RUNNER_GIT_DIR} directory, please now update your phpunit.xml.dist"
echo "# file's <listeners> section and after that you can run your tests using created ./phpunit.sh script."