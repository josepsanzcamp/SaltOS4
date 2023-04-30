
all:
	cat ../js/bootstrap.js | minify --js > ../js/bootstrap.min.js
	cat bootstrap.htm | minify --html | php integrity.php > bootstrap.min.htm
