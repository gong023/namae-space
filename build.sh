#!/usr/bin/env sh

if [ ! -f box.phar ];
then
    curl -LSs https://box-project.github.io/box2/installer.php | php
else
    php ./box.phar update
fi

if [ ! -f composer.phar ];
then
    curl -LSs https://getcomposer.org/installer | php
else
    php ./composer.phar selfupdate
fi

if [ ! -f composer.lock ];
then
    php ./composer.phar install --no-dev
else
    php ./composer.phar update --no-dev
fi

php ./box.phar build
