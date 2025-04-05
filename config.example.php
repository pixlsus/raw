<?php
    define('dbhost','databasehost');
    define('dbuser','raw');
    define('dbpw','somecrazysecretpassword');
    define('dbname','raw');

    define('dbdsn','mysql:dbname='.dbname.';host='.dbhost.';charset=UTF8');

    // NOTE: none of the paths should end with a slash!

    //It's recommende to put the data dir outside the document root to prevent abuse.
    define('datapath','/srv/www/raw.dtstyle.net/data');
    define('hashdepth',3);

    define('basepath','/srv/www/raw.dtstyle.net/www');
    define('baseurl','https://raw.pixls.us');

    define('publicdatapath',basepath.'/data');
    define('publicdataurl',baseurl.'/data');

    define('publicdatauniquepath',basepath.'/data-unique');
    define('publicdatauniqueurl',baseurl.'/data-unique');

    define('GIT_AUTHOR_NAME', 'Raw.Pixls.Us Bot');
    define('GIT_AUTHOR_EMAIL', 'rpu@raw.pixls.us');

    define('influxserver','graph');
    define('influxdb','rpu');

    define('unwanted','|png|jpg|zip|tar|rar|');
