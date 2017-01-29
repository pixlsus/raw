#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    // found on http://php.net/manual/en/function.rmdir.php
    function delTree($dir) {
       $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
      }

    $cameradata=parsecamerasxml();
    $data=raw_getalldata();

    if(is_dir(publicdatapath)){
        delTree(publicdatapath);
    }
    mkdir(publicdatapath);

    foreach($data as $raw){
        if($raw['validated']==1){
            $make="unknown";
            $model="unknown";
            if($raw['make']!=""){
                $make=$cameradata[$raw['make']][$raw['model']]['make'] ?? $cameradata[$raw['make']]['make'] ?? $raw['make'];
            }
            if($raw['model']!=""){
                $model=$cameradata[$raw['make']][$raw['model']]['model'] ?? $raw['model'];
            }
            $make = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $make);
            $model = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $model);
            if(!is_dir(publicdatapath."/".$make)){
                mkdir(publicdatapath."/".$make);
            }
            if(!is_dir(publicdatapath."/".$make."/".$model)){
                mkdir(publicdatapath."/".$make."/".$model);
            }
            symlink(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'],publicdatapath."/".$make."/".$model."/".$raw['filename']);
        }
    }
    file_put_contents(publicdatapath."/timestamp.txt",time());
