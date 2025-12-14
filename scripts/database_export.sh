#!/bin/bash

cd /path/to/saltos_dumps
for i in $(mariadb datanase_name -e "show tables;" -N -s); do
	mariadb-dump --lock-tables=false --compact database_name $i | gzip -nf > $i.sql.gz
done
