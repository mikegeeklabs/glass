#This is an example of creating a 'glass' database and importing the starter database. 
#the example password 'notapassword' should be set to your password, 'nopepassword' 
#should be a different password
mysqladmin -pnotapassword create glass
mysql -pnotapassword glass <database.sql
echo "CREATE USER 'glass'@'localhost' IDENTIFIED BY 'nopepassword';" | mysql -pnotapassword
echo "GRANT ALL PRIVILEGES ON glass.* TO 'glass'@'localhost';" | mysql -pnotapassword
echo "GRANT ALTER, CREATE ON glass TO 'glass'@'localhost';" | mysql -pnotapassword
echo "FLUSH PRIVILEGES;" | mysql -pnotapassword 
echo DELETE THIS FILE WHEN DONE
