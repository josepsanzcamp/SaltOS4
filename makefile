
################################################################################
# MAIN PART
################################################################################

SHELL=/bin/bash
RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[0;33m
BLUE=\033[0;34m
NONE=\033[0m

.PHONY: utest docs ujest

JS_OBJ   := code/web/js/object.js
JS_APP   := code/web/js/app.js
JS_PROXY := code/web/js/proxy.js
JS_ROOT  := $(filter-out $(JS_PROXY) $(JS_OBJ) $(JS_APP),$(wildcard code/web/js/*.js))
JS_BOOT  := $(wildcard code/web/js/bootstrap/*.js)

export NODE_PATH := $(shell npm -g root 2>/dev/null)
export NODE_OPTIONS := --no-deprecation --experimental-vm-modules
export JEST_OPTIONS := -u

all:
	@echo Nothing to do by default

################################################################################
# WEB PART
################################################################################

web: clean
	cat code/web/lib/icons/bootstrap-icons.min.css code/web/lib/atkinson/atkinson.min.css | \
	php scripts/fixpath.php fonts/AtkinsonHyperlegible atkinson/fonts/AtkinsonHyperlegible | \
	php scripts/fixpath.php fonts/bootstrap-icons icons/fonts/bootstrap-icons > code/web/lib/index.css

	cat code/web/lib/bootstrap/bootstrap.bundle.min.js \
		code/web/lib/md5/md5.min.js \
		code/web/lib/sourcemap/sourcemapped-stacktrace.min.js \
		code/web/lib/interactjs/interact.min.js \
		code/web/lib/topbar/topbar.min.js > code/web/lib/index.js

	mkdir -p code/web/js/.js
	@for i in $(JS_OBJ) $(JS_APP) $(JS_ROOT); do \
		f=$${i##*/}; \
		cat $$i | php scripts/md5sum.php > code/web/js/.js/$$f; \
	done
	@for i in $(JS_BOOT); do \
		f=$${i##*/}; \
		cat $$i | php scripts/md5sum.php > code/web/js/.js/bootstrap.$$f; \
	done
	uglifyjs \
		$(patsubst code/web/js/%.js,code/web/js/.js/%.js,$(JS_OBJ)) \
		$(patsubst code/web/js/%.js,code/web/js/.js/%.js,$(JS_ROOT)) \
		$(patsubst code/web/js/bootstrap/%.js,code/web/js/.js/bootstrap.%.js,$(JS_BOOT)) \
		$(patsubst code/web/js/%.js,code/web/js/.js/%.js,$(JS_APP)) \
		-c reduce_vars=false -m \
		-o code/web/index.js \
		--source-map filename=code/web/index.js.map,url=index.js.map
	rm -f code/web/js/.js/*.js
	rmdir code/web/js/.js
	cat code/web/html/index.html | php scripts/sha384.php | minify --html > code/web/index.html

	@for i in code/apps/*/js/*.js; do \
	j=$${i%.*};  # file with path without extension    \
	k=$${i##*/}; # file without path with extension    \
	m=$${k%.*};  # file without path without extension \
	uglifyjs $$i -c reduce_vars=false -m -o $$j.min.js --source-map url=$$m.min.js.map; \
	done

	uglifyjs code/web/lib/md5/md5.min.js $(JS_PROXY) -c reduce_vars=false -m -o code/web/proxy.js --source-map filename=code/web/proxy.js.map,url=proxy.js.map

devel: clean
	cat code/web/html/index.html | \
	php scripts/debug.php \
		lib/index.css lib/icons/bootstrap-icons.min.css \
		lib/atkinson/atkinson.min.css | \
	php scripts/debug.php \
		lib/index.js lib/bootstrap/bootstrap.bundle.min.js \
		lib/md5/md5.min.js \
		lib/sourcemap/sourcemapped-stacktrace.min.js \
		lib/interactjs/interact.min.js \
		lib/topbar/topbar.min.js | \
	php scripts/debug.php index.js \
		$(patsubst code/web/js/%.js,js/%.js,$(JS_OBJ)) \
		$(patsubst code/web/js/%.js,js/%.js,$(JS_ROOT)) \
		$(patsubst code/web/js/%.js,js/%.js,$(JS_BOOT)) \
		$(patsubst code/web/js/%.js,js/%.js,$(JS_APP)) \
	> code/web/index.html

	echo "importScripts('lib/md5/md5.min.js','js/proxy.js');" > code/web/proxy.js

clean:
	rm -f code/web/index.{html,js,js.map}
	rm -f code/web/lib/index.{js,css}
	rm -f code/apps/*/js/*.min.{js,js.map}
	rm -f code/web/proxy.{js,js.map}

################################################################################
# TEST PART
################################################################################

test:
ifeq ($(file),) # default behaviour
	$(eval files := $(shell git ls-files -m -o --exclude-standard code/api/*.php code/api/php/*/*.php scripts/*.php utest/*.php utest/*/*.php code/apps/*/php/*.php code/apps/*/sample/*.php | sort))
else ifeq ($(file),all) # file=all
	$(eval files := $(shell find code/api/index.php code/api/php scripts utest code/apps/*/php code/apps/*/sample -name *.php | sort))
else # file=path
	$(eval files := $(shell find $(file) -name *.php | sort))
endif
	@$(if $(files), \
	phpcs --colors -p --standard=scripts/phpcs.xml ${files}; \
	php -l ${files} 1>/dev/null 2>/dev/null || php -l ${files} | grep -v 'No syntax errors detected'; \
	phpstan -cscripts/phpstan.neon analyse ${files}; )

ifeq ($(file),) # default behaviour
	$(eval files := $(shell git ls-files -m -o --exclude-standard code/web/js/*.js code/web/js/*/*.js scripts/*.js ujest/*.js code/apps/*/js/*.js | grep -v '\.min\.js$$' | sort))
else ifeq ($(file),all) # file=all
	$(eval files := $(shell find code/web/js scripts ujest code/apps/*/js -name *.js | grep -v '\.'min'\.'js$$ | sort))
else # file=path
	$(eval files := $(shell find $(file) -name *.js | grep -v '\.'min'\.'js$$ | sort))
endif
	@$(if $(files), \
	jscs --config=scripts/jscs.json ${files}; \
	node -c ${files}; )

################################################################################
# CHECKLIBS PART
################################################################################

libs:
ifeq ($(libs),) # default behaviour
	php scripts/checklibs.php scripts/checklibs.txt
else # libs=lib[,lib,lib]
	php scripts/checklibs.php scripts/checklibs.txt $(shell echo ${libs} | tr ',' ' ')
endif

################################################################################
# DOCS PART
################################################################################

docs:
ifeq ($(file),)
	$(MAKE) docs file=api,web,apps,utest,ujest,devel,locale,user
else
ifneq (,$(findstring api,$(file)))
	php scripts/maket2t.php docs/api.t2t code/api/php
	php scripts/makepdf.php docs/api.t2t
#~ 	php scripts/makehtml.php docs/api.t2t
endif
ifneq (,$(findstring web,$(file)))
	php scripts/jest_tester.php
	php scripts/maket2t.php docs/web.t2t code/web/js
	php scripts/imagest2t.php docs/web.t2t /tmp/tester.json
	php scripts/makepdf.php docs/web.t2t
#~ 	php scripts/makehtml.php docs/web.t2t
endif
ifneq (,$(findstring apps,$(file)))
	php scripts/maket2t.php docs/apps.t2t code/apps/*/{php,js}
	php scripts/makepdf.php docs/apps.t2t
#~ 	php scripts/makehtml.php docs/apps.t2t
endif
ifneq (,$(findstring utest,$(file)))
	php scripts/maket2t.php docs/utest.t2t utest/
	php scripts/makepdf.php docs/utest.t2t
#~ 	php scripts/makehtml.php docs/utest.t2t
endif
ifneq (,$(findstring ujest,$(file)))
	php scripts/maket2t.php docs/ujest.t2t ujest/
	php scripts/makepdf.php docs/ujest.t2t
#~ 	php scripts/makehtml.php docs/ujest.t2t
endif
ifneq (,$(findstring devel,$(file)))
	php scripts/updatet2t.php docs/devel.t2t
	php scripts/makepdf.php docs/devel.t2t
#~ 	php scripts/makehtml.php docs/devel.t2t
endif
ifneq (,$(findstring locale,$(file)))
	php scripts/makelocale.php
endif
ifneq (,$(findstring user,$(file)))
	php scripts/makeuser.php
endif
endif

################################################################################
# CHECK PART
################################################################################

check: checkdirs checkdev checkprod

checkdirs:
	@echo -e "$(YELLOW)Directories:$(NONE)"
	@echo -n api/apps:" "; test -e code/api/apps && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n api/data:" "; test -e code/api/data && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n web/api:" "; test -e code/web/api && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n web/apps:" "; test -e code/web/apps && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"

checkdev:
	@echo -e "$(YELLOW)Devel commands:$(NONE)"
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
	@echo -n puppeteer:" "; which puppeteer > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n sha384sum:" "; which sha384sum > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n svnversion:" "; which svnversion > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n svn:" "; which svn > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n txt2tags:" "; which txt2tags > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n uglifyjs:" "; which uglifyjs > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n wget:" "; which wget > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n xxd:" "; which xxd > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"

checkprod:
	@echo -e "$(YELLOW)Production commands:$(NONE)"
	@echo -n php:" "; which php > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n chromium:" "; which chromium > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n pdfunite:" "; which pdfunite > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n xlsxio_xlsx2csv:" "; which xlsxio_xlsx2csv > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n soffice:" "; which soffice > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n pdftotext:" "; which pdftotext > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n convert:" "; which convert > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n tesseract:" "; which tesseract > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n pdftoppm:" "; which pdftoppm > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n timeout:" "; which timeout > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n certutil:" "; which certutil > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n openssl:" "; which openssl > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n pk12util:" "; which pk12util > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"
	@echo -n pdfsig:" "; which pdfsig > /dev/null && echo -e "$(GREEN)OK$(NONE)" || echo -e "$(RED)KO$(NONE)"

################################################################################
# UNIT TEST PART
################################################################################

utest:
ifeq ($(file), ) # default behaviour
	@phpunit -c scripts/phpunit.xml $(shell git ls-files -m -o --exclude-standard -- 'utest/test_*.php' | gawk '{print "../../"$$0}' | sort | paste -s -d' ')
else ifeq ($(file), all) # file=all
	@phpunit -c scripts/phpunit.xml
else # file=xxx,yyy,zzz
	@phpunit -c scripts/phpunit.xml $(shell echo ${file} | tr ',' '\n' | gawk '{print "../../utest/test_"$$0".php"}' | paste -s -d' ')
endif

ujest:
	php scripts/jest_tester.php
	rm -f /tmp/nyc_output/*/*.json
	rm -f ujest/snaps/__diff_output__/*
	rmdir ujest/snaps/__diff_output__ || true
ifeq ($(file), ) # default behaviour
	-@jest $(JEST_OPTIONS) --config=scripts/jest.config.js $(shell git ls-files -m -o --exclude-standard -- 'ujest/test_*.js' | gawk '{print "../"$$0}' | sort | paste -s -d' ')
else ifeq ($(file), all) # file=all
	-@jest $(JEST_OPTIONS) --config=scripts/jest.config.js
else # file=xxx,yyy,zzz
	-@jest $(JEST_OPTIONS) --config=scripts/jest.config.js $(shell echo ${file} | tr ',' '\n' | gawk '{print "../ujest/test_"$$0".js"}' | paste -s -d' ')
endif
	php scripts/jest_coverage.php

ujest_by_parts:
ifeq ($(part),1)
	$(MAKE) ujest file=core,filter,gettext,hash,proxy,push,storage,token,window
else ifeq ($(part),2)
	$(MAKE) ujest file=apps,bootstrap,customers,emails,invoices,tester
else ifeq ($(part),3)
	$(MAKE) ujest file=screenshots
endif

ujest_patch:
	patch -p1 -d $(NODE_PATH)/jest-image-snapshot -N -r /dev/null < scripts/jest-image-snapshot-diff-on-update.patch || true

################################################################################
# METRICS PART
################################################################################

cloc:
	find scripts utest ujest code/api/{index.php,php,xml,locale} code/web/{js,html} code/apps/*/{js,php,xml,locale,sample} code/*/lib/*.yaml > /tmp/cloc.include
	find code/apps/*/js/*.min.* utest/files/* > /tmp/cloc.exclude
	cloc --list-file=/tmp/cloc.include --exclude-list-file=/tmp/cloc.exclude

################################################################################
# MAINTENANCE PART
################################################################################

gc:
	php code/api/index.php gc

indexing:
	php code/api/index.php indexing

integrity:
	php code/api/index.php integrity

cron:
	php code/api/index.php cron

################################################################################
# LANGUAGE PART
################################################################################

lang ?= en_US

langs:
	python3 scripts/checklangs.py
	for i in global certs common company crm dashboard emails hr purchases sales users; do \
		python3 scripts/checklangs.py --lang $(lang) --group $$i --filter missing; \
	done

################################################################################
# SETUP PART
################################################################################

setuponly:
	php code/api/index.php setup

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
	echo "DROP DATABASE saltos;" | mariadb || true
	echo "CREATE DATABASE saltos;" | mariadb || true

setupdemo:
	php code/api/index.php setup
	user=admin php code/api/index.php setup/certs
	user=admin php code/api/index.php setup/company
	user=admin php code/api/index.php setup/emails
	user=admin php code/api/index.php setup/crm
	user=admin php code/api/index.php setup/hr
	user=admin php code/api/index.php setup/purchases
	user=admin php code/api/index.php setup/sales

setupmysql:
	echo '<root><db><type>pdo_mysql</type></db></root>' > code/data/files/config.xml

setupsqlite:
	echo '<root><db><type>pdo_sqlite</type></db></root>' > code/data/files/config.xml

setupunlink:
	rm -f code/data/files/config.xml

setupall:
	$(MAKE) setupclean
	$(MAKE) setupmysql
	$(MAKE) setupdemo
	$(MAKE) setupsqlite
	$(MAKE) setupdemo
	$(MAKE) setupunlink

################################################################################
# DOCKER TEST PART
################################################################################

teststart:
	mkdir -p volumes/test/mssql && sudo chown 10001:0 volumes/test/mssql || true
	cd scripts && docker compose --profile test up -d

teststop:
	cd scripts && docker compose --profile test kill
	cd scripts && docker compose --profile test down

testlogs:
	cd scripts && docker compose --profile test logs

teststatus:
	cd scripts && docker compose --profile test ps

################################################################################
# HTTP TEST PART
################################################################################

httpstart:
	cd code/web/ && ln -s index.html index.php || true
	php -S 0.0.0.0:8092 -t code/web/ 2>/dev/null &

httpstop:
	cd code/web/ && rm -f index.php || true
	pkill -f "^php -S 0.0.0.0:8092" || true

################################################################################
# DOCKER SERVER PART
################################################################################

serverbuild:
	cd scripts && docker compose --profile server build

serverstart:
	cd scripts && docker compose --profile server up -d

serverstop:
	cd scripts && docker compose --profile server kill
	cd scripts && docker compose --profile server down

serverlogs:
	cd scripts && docker compose --profile server logs

serverstatus:
	cd scripts && docker compose --profile server ps

serverbash:
	cd scripts && docker compose --profile server exec saltos4server bash

################################################################################
# DOCKER DEVEL PART
################################################################################

develbuild:
	cd scripts && docker compose --profile devel build

develstart:
	cd scripts && docker compose --profile devel up -d

develstop:
	cd scripts && docker compose --profile devel kill
	cd scripts && docker compose --profile devel down

devellogs:
	cd scripts && docker compose --profile devel logs

develstatus:
	cd scripts && docker compose --profile devel ps

develbash:
	cd scripts && docker compose --profile devel exec saltos4devel bash
