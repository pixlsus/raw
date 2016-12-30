#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    $data=raw_getalldata();

    foreach($data as $raw){
        $file=datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'];

        $checksum=sha1_file($file);
        if($checksum!=$raw['checksum'] ){
            echo "fixing checksum for ".$raw['filename']." : ".$checksum." =>".$raw['checksum']."\n";
            raw_modify($raw['id'],array('checksum' => $checksum));
        } else {
            echo "checksum is just fine for ".$raw['filename']."\n";
        }
    }
