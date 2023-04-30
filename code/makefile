
all:
	minify js/index.js > js/index.min.js
	cat htm/index.htm | php htm/sha384.php > htm/index.min.htm
	minimize htm/index.min.htm --output htm/index.min.htm 1>/dev/null

test:
	phpcs php
