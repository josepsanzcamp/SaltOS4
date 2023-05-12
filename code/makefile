
all:
	minify js/core.js js/bootstrap.js js/tester.js > js/index.min.js
	cat htm/index.htm | php ../scripts/sha384.php > htm/index.tmp.htm
	minimize htm/index.tmp.htm --output htm/index.min.htm 1>/dev/null
	rm -f htm/index.tmp.htm

devel:
	cat js/core.js js/bootstrap.js js/tester.js > js/index.min.js
	cat htm/index.htm > htm/index.min.htm

test:
	phpcs php
	bash ../scripts/phpcs_js.sh js/core.js
	bash ../scripts/phpcs_js.sh js/bootstrap.js
	bash ../scripts/phpcs_js.sh js/tester.js

libs:
	php ../scripts/checklibs.php ../scripts/checklibs.txt
