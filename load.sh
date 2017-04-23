#!/bin/bash

cd /tmp/stock_data

for f in /tmp/stock_data/*.csv
do
chown mysql.mysql $f		
filename="${f%.*}"
symbol="${filename##*/}"

mysql -e "load data infile '"$f"' into table stock_data fields TERMINATED BY ',' LINES TERMINATED BY '\n' IGNORE 1 LINES (date,open,high,low,close,volume,adj_close) SET symbol='$symbol'"   -u stocks --password=stockpass stock_history -P 3306
done
