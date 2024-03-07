#!/bin/bash

cd xml
rm -f app.xml
touch app.$1.xml
ln -s app.$1.xml app.xml
cd ..

cd js
rm -f app.js
touch app.$1.js
ln -s app.$1.js app.js
cd ..
