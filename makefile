
SHELL=/bin/bash
RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[0;33m
BLUE=\033[0;34m
NONE=\033[0m

.PHONY: utest docs

all: clean
	cat code/web/lib/md5/md5.min.js > code/web/index.js
	cat code/web/js/{object,core,bootstrap,auth,app}.js | php scripts/md5sum.php | minify --js >> code/web/index.js
	cat code/web/css/index.css | minify --css > code/web/index.css
	cat code/web/htm/index.htm | php scripts/sha384.php | minify --html > code/web/index.htm

test:
ifeq ($(file), all)
	$(eval files := $(shell find code/api/index.php code/api/php scripts utest code/apps/*/php -name *.php | sort))
else
ifneq ($(file), )
	$(eval files := $(shell find $(file) -name *.php | sort))
else
	$(eval files := $(shell svn st code/api/index.php code/api/php scripts utest code/apps/*/php | grep -e ^A -e ^M -e ^? | tr ' ' '\n' | grep '\.'php$$ | sort))
endif
endif
	@for i in ${files}; do \
		echo $$i; \
		phpcs --colors --standard=scripts/phpcs.xml $$i; \
		php -l $$i 1>/dev/null 2>/dev/null || php -l $$i; \
	done

ifeq ($(file), all)
	$(eval files := $(shell find code/web/js scripts code/apps/*/js -name *.js | sort))
else
ifneq ($(file), )
	$(eval files := $(shell find $(file) -name *.js | sort))
else
	$(eval files := $(shell svn st code/web/js scripts code/apps/*/js | grep -e ^A -e ^M -e ^? | tr ' ' '\n' | grep '\.'js$$ | sort))
endif
endif
	@for i in ${files}; do \
		echo $$i; \
		jscs --config=scripts/jscs.json $$i 2>/dev/null; \
		node -c $$i; \
	done

libs:
	php scripts/checklibs.php scripts/checklibs.txt

devel: clean
	cat code/web/htm/index.htm | php scripts/debug.php index.js lib/md5/md5.min.js js/{object,core,bootstrap,auth,app}.js | php scripts/debug.php index.css css/index.css > code/web/index.htm

docs: .
	php scripts/makedocs.php docs/code.t2t code/api/php code/web/js
	php scripts/makedocs.php docs/apps.t2t code/apps/*/js code/apps/*/php
	php scripts/makedocs.php docs/utest.t2t utest/ utest/lib

clean:
	rm -f code/web/index.{js,css,htm}

check:
	@echo -e "$(YELLOW)Directories:$(NONE)"
	@echo -n api/data:" "; test -e code/api/data && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n api/apps:" "; test -e code/api/apps && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n web/api:" "; test -e code/web/api && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n web/apps:" "; test -e code/web/apps && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"

	@echo -e "$(YELLOW)Commands:$(NONE)"
	@echo -n minify:" "; which minify > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n php:" "; which php > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n svn:" "; which svn > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n svnversion:" "; which svnversion > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n phpcs:" "; which phpcs > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n jscs:" "; which jscs > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n node:" "; which node > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n wget:" "; which wget > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n txt2tags:" "; which txt2tags > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n pdflatex:" "; which pdflatex > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n sha384sum:" "; which sha384sum > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n xxd:" "; which xxd > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n base64:" "; which base64 > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n phpunit:" "; which phpunit > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n cloc:" "; which cloc > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"

utest:
ifeq ($(file), )
	phpunit -c scripts/phpunit.xml
else
	phpunit -c scripts/phpunit.xml ../../utest/$(file)
endif

cloc:
	cloc makefile scripts utest code/api/{index.php,php,xml} code/web/{js,htm} code/apps/*/{js,php,xml,locale}
