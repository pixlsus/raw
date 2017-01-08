#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    file_put_contents(datapath."/cameras.xml",file_get_contents("https://raw.githubusercontent.com/darktable-org/rawspeed/develop/data/cameras.xml"));
    $cameradata=parsecamerasxml();

    $zip = new ZipArchive;
    $result = $zip->open(datapath.'/raw_pixls_us_archive.tmp.zip', ZipArchive::CREATE);
    if ($result === TRUE) {
        $zip->addFile('data.txt', 'entryname.txt');

        $data=raw_getalldata();

        foreach($data as $raw){
            if($raw['validated']==1){
                $make="unknown";
                $model="unknown";
                if($raw['make']!=""){
                    $make=$cameradata[$raw['make']][$raw['model']]['make'];
                }
                if($raw['model']!=""){
                    $model=$cameradata[$raw['make']][$raw['model']]['model'];
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
