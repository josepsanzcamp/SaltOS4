
SHELL=/bin/bash

all:
	minify code/web/js/{core,bootstrap,auth,app}.js > code/web/all.min.js
	cat code/web/htm/index.htm | php scripts/sha384.php | minify --html > code/index.htm

test:
	$(shell test -f scripts/timestamp.tmp || touch -t 197001011200.00 scripts/timestamp.tmp)

	$(eval files := $(shell find code/api/index.php code/api/php scripts code/apps -name \*.php -newer scripts/timestamp.tmp | sort))
	@for i in ${files}; do \
		echo $$i; \
		phpcs --standard=scripts/rules.xml $$i; \
		php -l $$i 1>/dev/null 2>/dev/null || php -l $$i; \
	done

	$(eval files := $(shell find code/web/js scripts code/apps -name \*.js -newer scripts/timestamp.tmp | sort))
	@for i in ${files}; do \
		echo $$i; \
		jscs --config=scripts/rules.json $$i 2>/dev/null; \
		node -c $$i; \
	done

libs:
	php scripts/checklibs.php scripts/checklibs.txt

devel:
	cat code/web/htm/index.htm | php scripts/debug.php web/all.min.js web/js/{core,bootstrap,auth,app}.js > code/index.htm

timestamp:
	touch scripts/timestamp.tmp
	ls -l scripts/timestamp.tmp

timestamp0:
	touch -t 197001011200.00 scripts/timestamp.tmp
	ls -l scripts/timestamp.tmp

docs:
	php scripts/makedocs.php docs/document.t2t code/core/php code/core/js code/apps/*/app.js

clean:
	rm -f code/web/all.min.js
	rm -f code/index.htm
