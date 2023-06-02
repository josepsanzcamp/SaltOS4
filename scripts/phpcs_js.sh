#!/bin/bash

php ../scripts/phpcs_js.php $1 $1.phpcs.js
phpcs $1.phpcs.js
rm -f $1.phpcs.js
