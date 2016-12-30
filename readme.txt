Basic setup for get it working.

Requirements:

Webserver with php7 + php7-cli
Mysqldb
exiv2


1) Create database + user on mysqldb.
   Use db.sql to create the tables.

2) Point the webserver document root to the www directory.

3) Copy the config.example.php to config.php.

4) Edit the config.php to match your environment.

5) data dir must be writeable by the webserver.

6) create an admin user by using adduser.php in the tools directory.


