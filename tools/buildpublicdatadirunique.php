#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");
    include("buildpublicdatadir.inc.php");

    // We don't really want to race with ourselves, so acquire a lock.
    define('publicdatauniquepath_lock', publicdatauniquepath.'.lock');
    $lock = fopen(publicdatauniquepath_lock, "w");
    if(!flock($lock, LOCK_EX | LOCK_NB)) {
        echo 'Unable to obtain lock';
        exit(-1);
    }

    define('timestamp', time());

    $cameradata=parsecamerasxml();
    $data=raw_getalldata();

    $raws = [];
    foreach($data as $raw){
        if($raw['validated']==1 && $raw['masterset']==1){
            $raws[] = new RawEntry($raw);
        }
    }

    buildpublicdatadir($raws, ["../data/LICENSE-CC0-1.0.txt" => "LICENSE.txt"], timestamp, publicdatauniquepath, publicdatauniqueurl, publicdatauniqueannexuuid, 'data-unique');

    //--------------------------------------------------------------------------

    // We're done, release the lock.
    unlink(publicdatauniquepath_lock);
    flock($lock, LOCK_UN);
    fclose($lock);
