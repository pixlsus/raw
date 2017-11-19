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
    $makes=array();

    if(is_dir(publicdatapathunique)){
        delTree(publicdatapathunique);
    }
    mkdir(publicdatapathunique);

    foreach($data as $raw){
        if($raw['masterset']==1){
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
            if(!is_dir(publicdatapathunique."/".$make)){
                mkdir(publicdatapathunique."/".$make);
            }
            if(!is_dir(publicdatapathunique."/".$make."/".$model)){
                mkdir(publicdatapathunique."/".$make."/".$model);
            }
            symlink(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'],publicdatapathunique."/".$make."/".$model."/".$raw['filename']);
            $sha1table[$make."/".$model."/".$raw['filename']]=$raw['checksum'];

            if(!in_array($make,$makes)){
                $makes[]=$make;
            }
        }
    }

    ksort($sha1table, SORT_NATURAL | SORT_FLAG_CASE);

    $fp=fopen(publicdatapathunique."/filelist.sha1","w");
    foreach($sha1table as $file=>$sha1) {
        fprintf($fp,"%s  %s\n",$sha1,$file);
    }
    fclose($fp);

    file_put_contents(publicdatapathunique."/timestamp.txt",time());
