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
            $sha1table[$make."/".$model."/".$raw['filename']]=$raw['checksum'];
        }
    }

    $fp=fopen(publicdatapath."/filelist.sha1","w");
    foreach($sha1table as $file=>$sha1) {
        fprintf($fp,"%s  %s\n",$sha1,$file);
    }
    fclose($fp);

    file_put_contents(publicdatapath."/timestamp.txt",time());

    // Badgegeneration
    $cameras=raw_getnumberofcameras();
    file_put_contents("../www/button-cameras.svg", file_get_contents("https://img.shields.io/badge/cameras-".$cameras."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-cameras.png", file_get_contents("https://img.shields.io/badge/cameras-".$cameras."-green.png?maxAge=3600"));
    $samples=raw_getnumberofsamples();
    file_put_contents("../www/button-samples.svg", file_get_contents("https://img.shields.io/badge/samples-".$samples."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-samples.png", file_get_contents("https://img.shields.io/badge/samples-".$samples."-green.png?maxAge=3600"));
    $reposize=raw_gettotalrepositorysize();
    file_put_contents("../www/button-size.svg", file_get_contents("https://img.shields.io/badge/size-".human_filesize($reposize)."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-size.png", file_get_contents("https://img.shields.io/badge/size-".human_filesize($reposize)."-green.png?maxAge=3600"));
    
    $postdata="rpu,key=cameras value=$cameras\n";
    $postdata.="rpu,key=samples value=$samples\n";
    $postdata.="rpu,key=reposize value=$reposize\n";
    
    $opts = array('http' => array( 'method'  => 'POST', 'header'  => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $postdata, 'timeout' => 60 ) );
    $context  = stream_context_create($opts);
    $url = influxserver."/write?db=".influxdb;
    file_get_contents($url, false, $context, -1, 40000); 
    