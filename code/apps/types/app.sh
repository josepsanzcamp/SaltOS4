#!/bin/bash

cd xml
rm -f app.xml
rm -f list.xml
ln -s app.$1.xml app.xml
ln -s list.$1.xml list.xml
cd ..

cd js
rm -f app.js
ln -s app.$1.js app.js
cd ..

rm -f ../../data/cache/*
sudo systemctl restart apache2
