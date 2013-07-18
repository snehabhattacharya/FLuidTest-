#!clones the existing Vesuvius branch into new instances
 mkdir /var/www/newInstance1
 #copy recursively into newInstances1
 cp -R  /var/www/project_vesu /var/www/newInstance1
 #creates new db and new user and grants the privileges
 mysql -u root -psneha227 -e "create database testdb"; 
 #MYSQL = `which mysql`
 #EXPECTED_ARGS=3
 #Q1="CREATE USER 'testusertest'@'localhost' IDENTIFIED BY  'password1234';"
 Q2="use testdb;"
 Q3="GRANT ALL ON *.* TO 'testusertest'@'localhost' IDENTIFIED BY 'password1234';"
 Q4="FLUSH PRIVILEGES;"
 sql="${Q2}${Q3}${Q4}"
 mysql -u root -psneha227 -e "$sql";
 #dumps the required tables into the current database
 mysqldump -u testusertest -ppassword1234 testdb < /var/www/newInstance1/project_vesu/vesuvius/vesuvius/backups/vesuviusStarterDb_v092.sql;
 mysql -u testusertest -p testdb < /var/www/project_vesu/vesuvius/vesuvius/backups/vesuviusStarterDb_v092.sql;
#copies the configuration file
#cp /var/www/project_vesu/vesuvius/vesuvius/conf/sahana.conf.example /var/www/newInstance1/project_vesu/vesuvius/vesuvius/conf/sahana1.conf
sed -i 's/snehatest/testdb/' /var/www/newInstance1/project_vesu/vesuvius/vesuvius/conf/sahana.conf
sed -i 's/root/testusertest/' /var/www/newInstance1/project_vesu/vesuvius/vesuvius/conf/sahana.conf
sed -i 's/sneha227/password1234/' /var/www/newInstance1/project_vesu/vesuvius/vesuvius/conf/sahana.conf
#copies the .htaccess file
#cp /var/www/project_vesu/vesuvius/vesuvius/www/htaccess.example /var/www/newInstance1/project_vesu/vesuvius/vesuvius/www/.htaccess

sed -i 's:project_vesu/vesuvius/vesuvius/www:newInstance1/project_vesu/vesuvius/vesuvius:' /var/www/newInstance1/project_vesu/vesuvius/vesuvius/www/.htaccess
#creates a symlink
ln -s /var/www/newInstance1/project_vesu/vesuvius/vesuvius/www /var/www/newInstance1

 mkdir /var/www/newInstance1/project_vesu/vesuvius/vesuvius/www/tmp ;
 mkdir /var/www/newInstance1/project_vesu/vesuvius/vesuvius/www/tmp/pfif_logs ;
 mkdir /var/www/newInstance1/project_vesu/vesuvius/vesuvius/www/tmp/pfif_cache ;
 mkdir /var/www/newInstance1/project_vesu/vesuvius/vesuvius/www/tmp/plus_cache ;
 mkdir /var/www/newInstance1/project_vesu/vesuvius/vesuvius/www/tmp/rap_cache ;
 mkdir /var/www/newInstance1/project_vesu/vesuvius/vesuvius/www/tmp/mpres_cache ;
 #deletes the root user from database
 p1="use testdb;"
 p2="DELETE FROM `users` WHERE `users`.`user_id` = 1 ; "
 del="${p1}${p2}";
 mysql -u testusertest -ppassword1234 -e "$del";
 

