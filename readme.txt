Basic setup for get it working.

Package requirements:
* Web server
* mysql server
* php7
* php7-cli
* php7-mysql
* php7-mbstring
* exiv2

Required config:
* Ensure that mysqld is not in `STRICT_TRANS_TABLES` mode!
  Query the current mode (`SELECT @@SQL_MODE;`),
  and put the value returned (without `STRICT_TRANS_TABLES`!)
  into `/etc/mysql/mariadb.conf.d/70-sql-mode.cnf`, e.g.:
  ```
  [mysqld]
  sql_mode="ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
  ```
* enable webserver's `php` module
* Enable php's `pdo_mysql` module
* Set `post_max_size = 1G` in webserver's `php.ini`
* Set `upload_max_filesize = 1G` in webserver's `php.ini`

1) Create database + user on mysqldb,
   grant permissons on that database to the user.
   Use db.sql to create the tables.

2) Point the webserver document root to the www directory.

3) Copy the config.example.php to config.php.

4) Edit the config.php to match your environment.

5) data dir must be writeable by the webserver.

6) create an admin user by using adduser.php in the tools directory.


