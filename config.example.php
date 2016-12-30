<?php
    define('dbhost','databasehost');
    define('dbuser','raw');
    define('dbpw','somecrazysecretpassword');
    define('dbname','raw');

    define('dbdsn','mysql:dbname='.dbname.';host='.dbhost.';charset=UTF8');

    //It's recommende to put the data dir outside the document root to prevent abuse.
    define('datapath','/srv/www/raw.dtstyle.net/data');
    define('hashdepth',3);

    define('baseurl','https://raw.dtstyle.net');
