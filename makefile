
SHELL=/bin/bash

all:
	minify code/core/js/{core.js,bootstrap.js,hash.js,token.js,app.js} > code/core/js/index.min.js
	cat code/core/htm/index.htm | php scripts/sha384.php | minify --html > code/core/htm/index.min.htm

test:
	$(shell test -f scripts/timestamp.tmp || touch -t 197001011200.00 scripts/timestamp.tmp)

	$(eval files := $(shell find code/index.php code/core/php scripts code/apps -name \*.php -newer scripts/timestamp.tmp | sort))
	@for i in ${files}; do \
		echo $$i; \
		phpcs --standard=scripts/rules.xml $$i; \
		php -l $$i 1>/dev/null 2>/dev/null || php -l $$i; \
	done

	$(eval files := $(shell find code/core/js scripts code/apps -name \*.js -newer scripts/timestamp.tmp | grep -v index.min.js | sort))
	@for i in ${files}; do \
		echo $$i; \
		jscs --config=scripts/rules.json $$i 2>/dev/null; \
		node -c $$i; \
	done

libs:
	php scripts/checklibs.php scripts/checklibs.txt

debug:
	cat code/core/htm/index.htm | php scripts/debug.php core/js/{core.js,bootstrap.js,hash.js,token.js,app.js} > code/core/htm/index.min.htm

timestamp:
	touch scripts/timestamp.tmp
	ls -l scripts/timestamp.tmp

timestamp0:
	touch -t 197001011200.00 scripts/timestamp.tmp
	ls -l scripts/timestamp.tmp

docs:
	php scripts/makedocs.php docs/document.t2t code/core/php code/core/js code/apps/*/app.js
