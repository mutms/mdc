#!/usr/bin/env bash

apt update
apt -y install openjdk-17-jre-headless
/mdc/composer_install.sh
php composer.phar create-project moodlehq/moodle-plugin-ci /var/www/moodle-plugin-ci ^4
