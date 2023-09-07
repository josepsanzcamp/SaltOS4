
SHELL=/bin/bash

all:
	minify core/js/{core.js,bootstrap.js,app.js} > core/js/index.min.js
	cat core/htm/index.htm | php ../scripts/sha384.php | minify --html > core/htm/index.min.htm

test:
	$(shell test -f ../scripts/timestamp.tmp || touch -t 197001011200.00 ../scripts/timestamp.tmp)

	$(eval files := $(shell find index.php core/php ../scripts apps -name \*.php -newer ../scripts/timestamp.tmp | sort))
	@for i in ${files}; do \
		echo $$i; \
		phpcs --standard=../scripts/rules.xml $$i; \
		php -l $$i 1>/dev/null 2>/dev/null || php -l $$i; \
	done

	$(eval files := $(shell find core/js ../scripts apps -name \*.js -newer ../scripts/timestamp.tmp | grep -v index.min.js | sort))
	@for i in ${files}; do \
		echo $$i; \
		php ../scripts/phpcs_js.php --standard=../scripts/rules.xml $$i; \
	done

libs:
	php ../scripts/checklibs.php ../scripts/checklibs.txt

debug:
	cat core/htm/index.htm | php ../scripts/debug.php core/js/{core.js,bootstrap.js,app.js} > core/htm/index.min.htm

timestamp:
	touch ../scripts/timestamp.tmp
	ls -l ../scripts/timestamp.tmp

timestamp0:
	touch -t 197001011200.00 ../scripts/timestamp.tmp
	ls -l ../scripts/timestamp.tmp
