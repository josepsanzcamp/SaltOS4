
SHELL=/bin/bash
RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[0;33m
BLUE=\033[0;34m
NONE=\033[0m

.PHONY: utest docs ujest

FILES=object,core,bootstrap,storage,hash,token,auth,window,gettext,driver,filter,backup,form,push,common,app

export NODE_PATH := $(shell npm -g root)

all:
	@echo Nothing to do by default

web: clean
	cat code/web/lib/bootstrap/bootstrap-icons.min.css code/web/lib/atkinson/atkinson.min.css | \
	php scripts/fixpath.php fonts/AtkinsonHyperlegible atkinson/fonts/AtkinsonHyperlegible | \
	php scripts/fixpath.php fonts/bootstrap-icons bootstrap/fonts/bootstrap-icons > code/web/lib/index.css

	cat code/web/lib/bootstrap/bootstrap.bundle.min.js code/web/lib/md5/md5.min.js code/web/lib/sourcemap/sourcemapped-stacktrace.min.js > code/web/lib/index.js

	mkdir -p code/web/js/.js
	@for i in code/web/js/*.js; do \
	cat $$i | php scripts/md5sum.php > code/web/js/.js/$${i##*/}; \
	done
	uglifyjs code/web/js/.js/{$(FILES)}.js -c reduce_vars=false -m -o code/web/index.js --source-map filename=code/web/index.js.map,url=index.js.map
	rm -f code/web/js/.js/*.js
	rmdir code/web/js/.js
	cat code/web/htm/index.htm | php scripts/sha384.php | minify --html > code/web/index.htm

	@for i in code/apps/*/js/*.js; do \
	j=$${i%.*};  # file with path without extension    \
	k=$${i##*/}; # file without path with extension    \
	m=$${k%.*};  # file without path without extension \
	uglifyjs $$i -c reduce_vars=false -m -o $$j.min.js --source-map url=$$m.min.js.map; \
	done

	uglifyjs code/web/lib/md5/md5.min.js code/web/js/proxy.js -c reduce_vars=false -m -o code/web/proxy.js --source-map filename=code/web/proxy.js.map,url=proxy.js.map

devel: clean
	cat code/web/htm/index.htm | \
	php scripts/debug.php lib/index.css lib/bootstrap/bootstrap-icons.min.css lib/atkinson/atkinson.min.css | \
	php scripts/debug.php lib/index.js lib/bootstrap/bootstrap.bundle.min.js lib/md5/md5.min.js lib/sourcemap/sourcemapped-stacktrace.min.js | \
	php scripts/debug.php index.js js/{$(FILES)}.js > code/web/index.htm

	echo "importScripts('lib/md5/md5.min.js','js/proxy.js');" > code/web/proxy.js

clean:
	rm -f code/web/index.{htm,js,js.map}
	rm -f code/web/lib/index.{js,css}
	rm -f code/apps/*/js/*.min.{js,js.map}
	rm -f code/web/proxy.{js,js.map}

test:
ifeq ($(file),) # default behaviour
	$(eval files := $(shell svn st code/api/index.php code/api/php scripts utest code/apps/*/php code/apps/*/sample | grep -e ^A -e ^M -e ^? | grep '\.'php$$ | gawk '{print $$2}' | sort))
else
ifeq ($(file),all) # file=all
	$(eval files := $(shell find code/api/index.php code/api/php scripts utest code/apps/*/php code/apps/*/sample -name *.php | sort))
else # file=path
	$(eval files := $(shell find $(file) -name *.php | sort))
endif
endif
	@$(if $(files), \
	phpcs --colors -p --standard=scripts/phpcs.xml ${files}; \
	php -l ${files} 1>/dev/null 2>/dev/null || php -l ${files} | grep -v 'No syntax errors detected'; \
	phpstan -cscripts/phpstan.neon analyse ${files}; )

ifeq ($(file),) # default behaviour
	$(eval files := $(shell svn st code/web/js scripts ujest code/apps/*/js | grep -e ^A -e ^M -e ^? | grep '\.'js$$ | grep -v '\.'min'\.'js$$ | gawk '{print $$2}' | sort))
else
ifeq ($(file),all) # file=all
	$(eval files := $(shell find code/web/js scripts ujest code/apps/*/js -name *.js | grep -v '\.'min'\.'js$$ | sort))
else # file=path
	$(eval files := $(shell find $(file) -name *.js | grep -v '\.'min'\.'js$$ | sort))
endif
endif
	@$(if $(files), \
	jscs --config=scripts/jscs.json ${files}; \
	node -c ${files}; )

libs:
ifeq ($(libs),) # default behaviour
	php scripts/checklibs.php scripts/checklibs.txt
else # libs=lib[,lib,lib]
	php scripts/checklibs.php scripts/checklibs.txt $(shell echo ${libs} | tr ',' ' ')
endif

docs:
ifeq ($(file),)
	$(MAKE) docs file=api,web,apps,utest,ujest,devel
else
ifneq (,$(findstring api,$(file)))
	php scripts/maket2t.php docs/api.t2t code/api/php
	php scripts/makepdf.php docs/api.t2t
	php scripts/makehtml.php docs/api.t2t
endif
ifneq (,$(findstring web,$(file)))
	php scripts/maket2t.php docs/web.t2t code/web/js
	php scripts/imagest2t.php docs/web.t2t /tmp/tester.json
	php scripts/makepdf.php docs/web.t2t
	php scripts/makehtml.php docs/web.t2t
endif
ifneq (,$(findstring apps,$(file)))
	php scripts/maket2t.php docs/apps.t2t code/apps/*/{php,js}
	php scripts/makepdf.php docs/apps.t2t
	php scripts/makehtml.php docs/apps.t2t
endif
ifneq (,$(findstring utest,$(file)))
	php scripts/maket2t.php docs/utest.t2t utest/
	php scripts/makepdf.php docs/utest.t2t
	php scripts/makehtml.php docs/utest.t2t
endif
ifneq (,$(findstring ujest,$(file)))
	php scripts/maket2t.php docs/ujest.t2t ujest/
	php scripts/makepdf.php docs/ujest.t2t
	php scripts/makehtml.php docs/ujest.t2t
endif
ifneq (,$(findstring devel,$(file)))
	php scripts/updatet2t.php docs/devel.t2t
	php scripts/makepdf.php docs/devel.t2t
	php scripts/makehtml.php docs/devel.t2t
endif
endif

check:
	@echo -e "$(YELLOW)Directories:$(NONE)"
	@echo -n api/apps:" "; test -e code/api/apps && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n api/data:" "; test -e code/api/data && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n web/api:" "; test -e code/web/api && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n web/apps:" "; test -e code/web/apps && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"

	@echo -e "$(YELLOW)Commands:$(NONE)"
	@echo -n acorn:" "; which acorn > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n base64:" "; which base64 > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n cloc:" "; which cloc > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n curl:" "; which curl > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n git:" "; which git > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n istanbul-merge:" "; which istanbul-merge > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n jest:" "; which jest > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n jscs:" "; which jscs > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n minify:" "; which minify > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n node:" "; which node > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n nyc:" "; which nyc > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n pdflatex:" "; which pdflatex > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n phpcs:" "; which phpcs > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n phpstan:" "; which phpstan > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n phpunit:" "; which phpunit > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n php:" "; which php > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n puppeteer:" "; which puppeteer > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n sha384sum:" "; which sha384sum > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n svnversion:" "; which svnversion > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n svn:" "; which svn > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n txt2tags:" "; which txt2tags > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n uglifyjs:" "; which uglifyjs > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n wget:" "; which wget > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n xxd:" "; which xxd > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"

utest:
ifeq ($(file), ) # default behaviour
	@phpunit -c scripts/phpunit.xml $(shell svn st utest/test_*.php | grep -e ^A -e ^M -e ^? | grep '\.'php$$ | gawk '{print "../../"$$2}' | sort | paste -s -d' ')
else
ifeq ($(file), all) # file=all
	@phpunit -c scripts/phpunit.xml
else # file=xxx,yyy,zzz
	@phpunit -c scripts/phpunit.xml $(shell echo ${file} | tr ',' '\n' | gawk '{print "../../utest/test_"$$0".php"}' | paste -s -d' ')
endif
endif

ujest:
	php scripts/jest_tester.php
	rm -f /tmp/nyc_output/*/*.json
	rm -f ujest/snaps/__diff_output__/*
	-rmdir ujest/snaps/__diff_output__
ifeq ($(file), ) # default behaviour
	-@jest --config=scripts/jest.config.js $(shell svn st ujest/test_*.js | grep -e ^A -e ^M -e ^? | grep '\.'js$$ | gawk '{print "../"$$2}' | sort | paste -s -d' ')
else
ifeq ($(file), all) # file=all
	-@jest --config=scripts/jest.config.js
else # file=xxx,yyy,zzz
	-@jest --config=scripts/jest.config.js $(shell echo ${file} | tr ',' '\n' | gawk '{print "../ujest/test_"$$0".js"}' | paste -s -d' ')
endif
endif
	php scripts/jest_coverage.php

cloc:
	find scripts utest ujest code/api/{index.php,php,xml,locale} code/web/{js,htm} code/apps/*/{js,php,xml,locale,sample} > /tmp/cloc.include
	find code/apps/*/js/*.min.* utest/files/* > /tmp/cloc.exclude
	cloc --list-file=/tmp/cloc.include --exclude-list-file=/tmp/cloc.exclude

setup:
	php code/api/index.php setup

gc:
	php code/api/index.php gc

indexing:
	php code/api/index.php indexing

integrity:
	php code/api/index.php integrity

setupclean:
	rm -f code/data/inbox/1/*
	-rmdir code/data/inbox/1
	rm -f code/data/outbox/1/*
	-rmdir code/data/outbox/1
	rm -f code/data/files/*/*
	-rm -f code/data/files/*
	-rmdir code/data/files/*
	rm -f code/data/cache/*
	rm -f code/data/cron/*
	rm -f code/data/logs/*
	rm -f code/data/temp/*
	rm -f code/data/trash/*
	rm -f code/data/upload/*
	echo "DROP DATABASE saltos;" | mariadb
	echo "CREATE DATABASE saltos;" | mariadb

setupmysql:
	php code/api/index.php setup
	user=admin php code/api/index.php setup/certs
	user=admin php code/api/index.php setup/company
	user=admin php code/api/index.php setup/emails
	user=admin php code/api/index.php setup/crm
	user=admin php code/api/index.php setup/hr
	user=admin php code/api/index.php setup/purchases
	user=admin php code/api/index.php setup/sales

setupsqlite:
	echo '<root><db><type>pdo_sqlite</type></db></root>' > code/data/files/config.xml
	php code/api/index.php setup
	user=admin php code/api/index.php setup/certs
	user=admin php code/api/index.php setup/company
	user=admin php code/api/index.php setup/emails
	user=admin php code/api/index.php setup/crm
	user=admin php code/api/index.php setup/hr
	user=admin php code/api/index.php setup/purchases
	user=admin php code/api/index.php setup/sales
	rm -f code/data/files/config.xml

setupinstall: setupclean setupmysql setupsqlite

cron:
	php code/api/index.php cron

langs:
	python scripts/checklangs.py
