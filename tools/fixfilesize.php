#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    {
        $dbh = db_init();
        $sth = $dbh->prepare('ALTER TABLE raws ADD filesize bigint(64) UNSIGNED NOT NULL;');
        $sth->execute();
    }

    $data=raw_getalldata();

    foreach($data as $raw){
        $file=datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'];

        $filesize=filesize($file);
        if($filesize!=$raw['filesize'] ){
            echo "fixing filesize for ".$raw['filename']." : ".$filesize." =>".$raw['filesize']."\n";
            raw_modify($raw['id'],array('filesize' => $filesize));
        } else {
            echo "filesize is just fine for ".$raw['filename']."\n";
        }
    }
