#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    $data=raw_getalldata();

    foreach($data as $raw){
        $exifdata=raw_readexif(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename']);
        $newdata=raw_parseexif($exifdata);
        raw_modify($raw['id'],array('pixels' => $newdata['pixels']));
    }

