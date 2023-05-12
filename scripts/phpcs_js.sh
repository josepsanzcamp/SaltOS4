#!/bin/bash

php ../scripts/phpcs_js.php $1 $1.cache a
phpcs $1
php ../scripts/phpcs_js.php $1 $1.cache b
