There's two ways to do scraping:
1) banweb_to_java <-- old way, but the code should be almost working
2) new_mexico_tech_banweb.py <-- older way, but update more recently

I'm currently running method (2) with a cronjob by the beanweb user:
su beanweb && crontab -e

This executes cronjob.sh