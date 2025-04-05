#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    {
        $dbh = db_init();
        $sth = $dbh->prepare('ALTER TABLE raws MODIFY checksum CHAR(64) NOT NULL');
        $sth->execute();
    }

    $data=raw_getalldata();

    foreach($data as $raw){
        $file=datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'];

        $checksum=hash_file('sha256', $file);
        if($checksum!=$raw['checksum'] ){
            echo "fixing checksum for ".$raw['filename']." : ".$checksum." =>".$raw['checksum']."\n";
            raw_modify($raw['id'],array('checksum' => $checksum));
        } else {
            echo "checksum is just fine for ".$raw['filename']."\n";
        }
    }
