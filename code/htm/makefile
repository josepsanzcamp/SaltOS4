
all:
	cd ../js; minify bootstrap.js > bootstrap.min.js
	cat bootstrap.htm | minify --html | php integrity.php > bootstrap.min.htm
