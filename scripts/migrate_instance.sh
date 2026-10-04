#!/bin/bash

# Migrate an instance created by an old make_instance.sh to the current layout,
# the old instances publish the full api and apps directories using links in
# the web directory, the current layout only publishes the index.php of the api
# and the public files of the apps, the api, apps and data are not modified
#
# Usage: cd instance; bash ../scripts/migrate_instance.sh
#
# To migrate all the instances of the demos, where each instance is a directory
# with a hash of 32 chars, execute the follow command in the private directory:
#
# for i in ????????????????????????????????; do (cd $i; bash ../scripts/migrate_instance.sh); done

for i in web api apps data; do
    if [ ! -d $i ]; then
        echo "Error: $i not found, this script must be executed inside an instance"
        exit 1
    fi
done
if [ -n "$(find web -type f)" ]; then
    echo "Error: web contains files that are not links in $(pwd), this is not an instance"
    exit 1
fi

# The old instances have a link to the removed .htaccess of the code
if [ -L .htaccess ]; then
    rm -f .htaccess
fi

# The web directory only contains links, it is created again from scratch
rm -rf web
mkdir web
cd web
for i in ../../code/web/*; do
    ln -s $i
done
rm -f api apps
mkdir api
ln -s ../../../code/web/api/index.php api/index.php
mkdir apps
cd apps
for i in ../../../code/web/apps/*; do
    ln -s $i
done
cd ..
cd ..

# Minimum security links
ln -sf ../../code/api/.htaccess api/.htaccess
ln -sf ../../code/apps/.htaccess apps/.htaccess
ln -sf ../../code/data/.htaccess data/.htaccess
