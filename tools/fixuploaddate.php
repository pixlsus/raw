#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    $data=raw_getalldata();

    foreach($data as $raw){
        $file=datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'];
        if($raw['date']=="0000-00-00" ){
            $stat=stat($file);
            $date=date("Y-m-d",$stat['mtime']);
            echo "Fixing date for ".$raw['filename']." : ".$date." =>".$raw['date']."\n";
            raw_modify($raw['id'],array('date' => $date));
        } else {
            echo "Date is set ".$raw['filename']."\n";
        }
    }
