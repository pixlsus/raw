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

    # Install the CC0 legalcode. THIS IS ONLY APPLICABLE TO THE MASTERSET!
    copy("../data/LICENSE-CC0-1.0.txt", publicdatapathunique."/LICENSE.txt");

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
            $sha256table[$make."/".$model."/".$raw['filename']]=$raw['checksum'];

            if(!in_array($make,$makes)){
                $makes[]=$make;
            }
        }
    }

    ksort($sha256table, SORT_NATURAL | SORT_FLAG_CASE);

    $fp=fopen(publicdatapathunique."/filelist.sha256","w");
    foreach($sha256table as $file=>$sha256) {
        // There are two schemes:
        // <hash><space><space><filename>      <- read in text mode
        // <hash><space><asterisk><filename>   <- read in binary mode
        fprintf($fp,"%s *%s\n",$sha256,$file);
    }
    fclose($fp);

    file_put_contents(publicdatapathunique."/timestamp.txt",time());
