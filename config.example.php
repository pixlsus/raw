<?php
    define('dbhost','databasehost');
    define('dbuser','raw');
    define('dbpw','somecrazysecretpassword');
    define('dbname','raw');

    define('dbdsn','mysql:dbname='.dbname.';host='.dbhost.';charset=UTF8');

    //It's recommende to put the data dir outside the document root to prevent abuse.
    define('datapath','/srv/www/raw.dtstyle.net/data');
    define('hashdepth',3);

    define('baseurl','https://raw.pixls.us');
    define('publicdatapath','/srv/www/raw.dtstyle.net//www/data');
    define('publicdatapathunique','/srv/www/raw.dtstyle.net/www/data-unique');

    define('influxserver','graph');
    define('influxdb','rpu');

    define('unwanted','|png|jpg|zip|tar|rar|');
