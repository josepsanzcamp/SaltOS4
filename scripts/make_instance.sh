#!/bin/bash

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

mkdir api
cd api
for i in ../../code/api/.htaccess ../../code/api/*; do
    ln -s $i
done
for i in apps data; do
    rm -f $i
    ln -s ../$i
done
cd ..

mkdir apps
cd apps
for i in ../../code/apps/.htaccess ../../code/apps/*; do
    ln -s $i
done
cd ..

mkdir data
cd data
ln -s ../../code/data/.htaccess
for i in cache cron files inbox logs outbox temp trash upload; do
	mkdir $i
	chmod 777 $i
done
cd ..
