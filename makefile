
SHELL=/bin/bash
RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[0;33m
BLUE=\033[0;34m
END=\033[0m

.PHONY: utest docs

all:
	minify code/web/js/{object,core,bootstrap,auth,app}.js > code/web/index.js
	cat code/web/htm/index.htm | php scripts/sha384.php | minify --html > code/web/index.htm

test:
	$(eval files := $(shell svn st code/api/index.php code/api/php scripts utest code/apps/*/php | grep -e ^A -e ^M -e ^? | tr ' ' '\n' | grep '\.'php$$ | sort))
	@for i in ${files}; do \
		echo $$i; \
		phpcs --colors --standard=scripts/phpcs.xml $$i; \
		php -l $$i 1>/dev/null 2>/dev/null || php -l $$i; \
	done

	$(eval files := $(shell svn st code/web/js scripts code/apps/*/js | grep -e ^A -e ^M -e ^? | tr ' ' '\n' | grep '\.'js$$ | sort))
	@for i in ${files}; do \
		echo $$i; \
		jscs --config=scripts/jscs.json $$i 2>/dev/null; \
		node -c $$i; \
	done

testall:
	$(eval files := $(shell find code/api/index.php code/api/php scripts utest code/apps/*/php -name *.php | sort))
	@for i in ${files}; do \
		echo $$i; \
		phpcs --colors --standard=scripts/phpcs.xml $$i; \
		php -l $$i 1>/dev/null 2>/dev/null || php -l $$i; \
	done

	$(eval files := $(shell find code/web/js scripts code/apps/*/js -name *.js | sort))
	@for i in ${files}; do \
		echo $$i; \
		jscs --config=scripts/jscs.json $$i 2>/dev/null; \
		node -c $$i; \
	done

libs:
	php scripts/checklibs.php scripts/checklibs.txt

devel:
	cat code/web/htm/index.htm | php scripts/debug.php index.js js/{object,core,bootstrap,auth,app}.js > code/web/index.htm

docs: .
	php scripts/makedocs.php docs/document.t2t code/api/php code/web/js code/apps/*/js code/apps/*/php

clean:
	rm -f code/web/index.js
	rm -f code/web/index.htm

check:
	@echo -e "$(YELLOW)Directories:$(END)"
	@echo -n api/data:" "; test -e code/api/data && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n api/apps:" "; test -e code/api/apps && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n web/api:" "; test -e code/web/api && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n web/apps:" "; test -e code/web/apps && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"

	@echo -e "$(YELLOW)Commands:$(END)"
	@echo -n minify:" "; which minify > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n php:" "; which php > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n svn:" "; which svn > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n svnversion:" "; which svnversion > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n phpcs:" "; which phpcs > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n jscs:" "; which jscs > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n node:" "; which node > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n wget:" "; which wget > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n txt2tags:" "; which txt2tags > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n pdflatex:" "; which pdflatex > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n sha384sum:" "; which sha384sum > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n xxd:" "; which xxd > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n base64:" "; which base64 > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n phpunit:" "; which phpunit > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"
	@echo -n cloc:" "; which cloc > /dev/null && echo -e "$(GREEN)OK$(END)" || echo -e "$(RED)KO$(END)"

utest:
	cd code/api; \
	phpunit -c ../../scripts/phpunit.xml

cloc:
	cloc --exclude-dir=lib .
