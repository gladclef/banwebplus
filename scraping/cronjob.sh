#! /bin/bash
cd "/home/beanweb/public/scraping/"
cwd=$( \pwd )
echo "evaluating scraping cronjob - ${cwd}" >> scraping.log
date >> scraping.log
echo ">>>>>>>>>> new_mexico_tech_banweb" >> scraping.log
./new_mexico_tech_banweb.py -v 2>&1 >> scraping.log
ret=$?
date >> scraping.log
if [[ "$ret" == "0" ]]; then
	echo ">>>>>>>>>> php_to_mysql" >> scraping.log
	php php_to_mysql.php 2>&1 >> scraping.log
	ret=$?
	if [[ "$ret" == "0" ]]; then
		echo "success" >> scraping.log
	else
		echo "failure/${ret}" >> scraping.log
	fi
else
	echo "failure/${ret}" >> scraping.log
fi
echo "" >> scraping.log