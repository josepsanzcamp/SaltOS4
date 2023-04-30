
all:
	minify bootstrap.js > bootstrap.min.js
	cd ../htm; cat bootstrap.htm | minify --html | php integrity.php > bootstrap.min.htm
