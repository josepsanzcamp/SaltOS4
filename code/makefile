
all:
	minify js/core.js js/bootstrap.js js/app.js > js/index.min.js
	cat htm/index.htm | php ../scripts/sha384.php > htm/index.tmp.htm
	minimize htm/index.tmp.htm --output htm/index.min.htm 1>/dev/null
	rm -f htm/index.tmp.htm

test:
	$(eval files := $(shell find php ../scripts apps -name \*.php))
	@for i in ${files}; do \
		echo $$i; \
		phpcs $$i; \
	done

	$(eval files := $(shell find js ../scripts apps -name \*.js | grep -v index.min.js))
	@for i in ${files}; do \
		echo $$i; \
		bash ../scripts/phpcs_js.sh $$i; \
	done

libs:
	php ../scripts/checklibs.php ../scripts/checklibs.txt
