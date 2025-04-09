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

    // We don't really want to race with ourselves, so acquire a lock.
    define('publicdatapath_lock', publicdatapath.'.lock');
    $lock = fopen(publicdatapath_lock, "w");
    if(!flock($lock, LOCK_EX | LOCK_NB)) {
        echo 'Unable to obtain lock';
        exit(-1);
    }

    define('timestamp', time());
    define('publicdatapath_timestamped', publicdatapath.".".timestamp);

    $cameradata=parsecamerasxml();
    $data=raw_getalldata();
    $makes=array();
    $noncc0samples=0;
    
    assert(!file_exists(publicdatapath_timestamped));
    mkdir(publicdatapath_timestamped);

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
            if(!is_dir(publicdatapath_timestamped."/".$make)){
                mkdir(publicdatapath_timestamped."/".$make);
            }
            if(!is_dir(publicdatapath_timestamped."/".$make."/".$model)){
                mkdir(publicdatapath_timestamped."/".$make."/".$model);
            }
            symlink(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'],publicdatapath_timestamped."/".$make."/".$model."/".$raw['filename']);
            $sha256table[$make."/".$model."/".$raw['filename']]=$raw['checksum'];

            if(!in_array($make,$makes)){
                $makes[]=$make;
            }
            
            if($raw['license']!="CC0"){
                $noncc0samples++;
            }
        }
    }

    ksort($sha256table, SORT_NATURAL | SORT_FLAG_CASE);

    $fp=fopen(publicdatapath_timestamped."/filelist.sha256","w");
    foreach($sha256table as $file=>$sha256) {
        // There are two schemes:
        // <hash><space><space><filename>      <- read in text mode
        // <hash><space><asterisk><filename>   <- read in binary mode
        fprintf($fp,"%s *%s\n",$sha256,$file);
    }
    fclose($fp);

    file_put_contents(publicdatapath_timestamped."/timestamp.txt",time());

    //--------------------------------------------------------------------------

    if(file_exists(publicdatapath)) {
        if(is_link(publicdatapath)) {
            define('publicdatapath_old', realpath(publicdatapath));
            // NOTE: do not delete anything yet.
        } else if(is_dir(publicdatapath)) {
            delTree(publicdatapath);
        } else {
            assert(false);
        }
    }

    define('publicdatapath_new', publicdatapath.".new");

    assert(!file_exists(publicdatapath_new));
    symlink(basename(publicdatapath_timestamped), publicdatapath_new);
    rename(publicdatapath_new, publicdatapath); // replaces old symlink!

    if(defined("publicdatapath_old")) {
        delTree(publicdatapath_old);
    }

    //--------------------------------------------------------------------------

    // Badgegeneration
    $cameras=raw_getnumberofcameras();
    file_put_contents("../www/button-cameras.svg", file_get_contents("https://img.shields.io/badge/cameras-".$cameras."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-cameras.png", file_get_contents("https://img.shields.io/badge/cameras-".$cameras."-green.png?maxAge=3600"));
    file_put_contents("../www/button-makes.svg", file_get_contents("https://img.shields.io/badge/makes-".count($makes)."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-makes.png", file_get_contents("https://img.shields.io/badge/makes-".count($makes)."-green.png?maxAge=3600"));
    $samples=raw_getnumberofsamples();
    file_put_contents("../www/button-samples.svg", file_get_contents("https://img.shields.io/badge/samples-".$samples."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-samples.png", file_get_contents("https://img.shields.io/badge/samples-".$samples."-green.png?maxAge=3600"));
    $reposize=raw_gettotalrepositorysize();
    file_put_contents("../www/button-size.svg", file_get_contents("https://img.shields.io/badge/size-".human_filesize($reposize)."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-size.png", file_get_contents("https://img.shields.io/badge/size-".human_filesize($reposize)."-green.png?maxAge=3600"));
    
    $reposize/=(1024*1024*1024);
    
    $missingcameras=count(unserialize(file_get_contents(datapath."/missingcameradata.serialize")));
    
    $postdata="rpu,key=cameras value=$cameras\n";
    $postdata.="rpu,key=samples value=$samples\n";
    $postdata.="rpu,key=reposize value=$reposize\n";
    $postdata.="rpu,key=noncc0samples value=$noncc0samples\n";
    $postdata.="rpu,key=missingcameras value=$missingcameras\n";
    $postdata.="rpu,key=makes value=".count($makes)."\n";
    
    $opts = array('http' => array( 'method'  => 'POST', 'header'  => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $postdata, 'timeout' => 60 ) );
    $context  = stream_context_create($opts);
    $url = influxserver."/write?db=".influxdb;
    file_get_contents($url, false, $context); 
    
    // We're done, release the lock.
    unlink(publicdatapath_lock);
    flock($lock, LOCK_UN);
    fclose($lock);
