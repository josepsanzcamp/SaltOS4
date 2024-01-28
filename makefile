
SHELL=/bin/bash

all:
	minify code/web/js/{object,core,bootstrap,auth,app}.js > code/web/index.js
	cat code/web/htm/index.htm | php scripts/sha384.php | minify --html > code/web/index.htm

test:
	$(eval files := $(shell svn st code/api/index.php code/api/php scripts code/apps | tr ' ' '\n' | grep .php$ | sort))
	@for i in ${files}; do \
		echo $$i; \
		phpcs --standard=scripts/rules.xml $$i; \
		php -l $$i 1>/dev/null 2>/dev/null || php -l $$i; \
	done

	$(eval files := $(shell svn st code/web/js scripts code/apps | tr ' ' '\n' | grep .js$ | sort))
	@for i in ${files}; do \
		echo $$i; \
		jscs --config=scripts/rules.json $$i 2>/dev/null; \
		node -c $$i; \
	done

testall:
	$(eval files := $(shell find code/api/index.php code/api/php scripts code/apps -name *.php | sort))
	@for i in ${files}; do \
		echo $$i; \
		phpcs --standard=scripts/rules.xml $$i; \
		php -l $$i 1>/dev/null 2>/dev/null || php -l $$i; \
	done

	$(eval files := $(shell find code/web/js scripts code/apps -name *.js | sort))
	@for i in ${files}; do \
		echo $$i; \
		jscs --config=scripts/rules.json $$i 2>/dev/null; \
		node -c $$i; \
	done

libs:
	php scripts/checklibs.php scripts/checklibs.txt

devel:
	cat code/web/htm/index.htm | php scripts/debug.php index.js js/{object,core,bootstrap,auth,app}.js > code/web/index.htm

docs:
	php scripts/makedocs.php docs/document.t2t code/api/php code/web/js code/apps/*/app.js

clean:
	rm -f code/web/index.js
	rm -f code/web/index.htm

check:
	@echo -n web/api:" "
	@test -e code/web/api && echo Ok || echo Ko
	@echo -n web/apps:" "
	@test -e code/web/apps && echo Ok || echo Ko
	@echo -n api/apps:" "
	@test -e code/api/apps && echo Ok || echo Ko
	@echo -n api/data:" "
	@test -e code/api/data && echo Ok || echo Ko
