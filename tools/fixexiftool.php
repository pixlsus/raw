#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    $data=raw_getalldata();

    foreach($data as $raw){
        $file=datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'];
        system('exiftool -G -s -a -m -u "'.$file.'"|grep -vE "^\[(ExifTool|File)\]">"'.$file.'.exiftool.txt"');
        system('exiftool -G -s -a -m -u -json "'.$file.'">"'.$file.'.exiftool.json"');
    }

