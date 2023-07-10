
all:
	minify js/core.js js/bootstrap.js js/app.js > js/index.min.js
	cat htm/index.htm | php ../scripts/sha384.php | php ../scripts/minify.php > htm/index.min.htm

test:
	$(eval files := $(shell find index.php php ../scripts apps -name \*.php -newer /tmp/timestamp))
	@for i in ${files}; do \
		echo $$i; \
		phpcs $$i; \
	done

	$(eval files := $(shell find js ../scripts apps -name \*.js -newer /tmp/timestamp | grep -v index.min.js))
	@for i in ${files}; do \
		echo $$i; \
		bash ../scripts/phpcs_js.sh $$i; \
	done

libs:
	php ../scripts/checklibs.php ../scripts/checklibs.txt

debug:
	cat htm/index.htm | php ../scripts/debug.php js/core.js js/bootstrap.js js/app.js > htm/index.min.htm

timestamp:
	touch /tmp/timestamp
