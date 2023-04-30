
all:
	cat ../js/bootstrap.js | minify --js > ../js/bootstrap.min.js
	cat bootstrap.htm | php integrity.php > bootstrap.min.htm
	minimize bootstrap.min.htm --output bootstrap.min.htm
