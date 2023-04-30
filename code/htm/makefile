
all:
	minify ../js/bootstrap.js > ../js/bootstrap.min.js
	cat bootstrap.htm | php integrity.php > bootstrap.min.htm
	minimize bootstrap.min.htm --output bootstrap.min.htm 1>/dev/null
