#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    $zip = new ZipArchive;
    $result = $zip->open(datapath.'/raw_pixls_us_archive.tmp.zip', ZipArchive::CREATE);
    if ($result === TRUE) {
        $zip->addFile('data.txt', 'entryname.txt');

        $data=raw_getalldata();

        foreach($data as $raw){
            if($raw['validated']==1){
                $make="unkown";
                $model="unkown";
                if($raw['make']!=""){
                    $make=$raw['make'];
                }
                if($raw['model']!=""){
                    $model=$raw['model'];
                }
                echo "adding ".datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename']." as ".$make."/".$model."/".$raw['filename']."\n";
                $zip->addFile(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'],$make."/".$model."/".$raw['filename']);
            }
        }
        $zip->close();
        rename(datapath.'/raw_pixls_us_archive.tmp.zip', datapath.'/raw_pixls_us_archive.zip');
        echo "created new archive\n";
    } else {
        echo "right.. that didn't go as expected\n";
    }
