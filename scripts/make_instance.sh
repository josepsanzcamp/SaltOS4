#!/bin/bash

ln -s ../code/.htaccess

mkdir web
cd web
for i in ../../code/web/.htaccess ../../code/web/*; do
    ln -s $i
done
for i in api apps; do
    rm -f $i
    ln -s ../$i
done
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
for i in cache files inbox logs outbox temp upload; do
	mkdir $i
	chmod 777 $i
done
cd ..
