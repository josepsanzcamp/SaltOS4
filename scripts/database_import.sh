#!/bin/bash

echo "DROP DATABASE datanase_name" | mariadb
echo "CREATE DATABASE datanase_name" | mariadb
cd /path/to/saltos_dumps
for i in *.sql.gz; do
	zcat $i | mariadb datanase_name
done
