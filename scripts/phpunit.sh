#!/bin/bash
if [ -n "$2" ]
then
    ./vendor/bin/phpunit  --stop-on-failure   --stop-on-error --filter $2  $1
  else
    ./vendor/bin/phpunit  --stop-on-failure   --stop-on-error  $1
fi



