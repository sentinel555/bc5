#!/bin/sh
for f in `find . -type f | grep '\.php$'`
do
	sed -f phpsed $f >/tmp/F
	cat /tmp/F > $f
done
