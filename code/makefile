
all:
	minify lib/locutus/isset.js lib/locutus/str_replace.js js/bootstrap.js js/core.js > js/index.min.js
	cat htm/index.htm | php htm/sha384.php > htm/index.min.htm
	minimize htm/index.min.htm --output htm/index.min.htm 1>/dev/null

test:
	phpcs php
