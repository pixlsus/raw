#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    // We don't really want to race with ourselves, so acquire a lock.
    define('publicdatapath_lock', publicdatapath.'.lock');
    $lock = fopen(publicdatapath_lock, "w");
    if(!flock($lock, LOCK_EX | LOCK_NB)) {
        echo 'Unable to obtain lock';
        exit(-1);
    }

    define('timestamp', time());
    define('publicdatapath_timestamped', publicdatapath.".".timestamp);

    define('publicdatagittmppath', publicdatapath."-git");
    define('publicdatagitrepopath', publicdatapath.".git");
    define('publicdatagitrepopath_timestamped', publicdatagitrepopath.".".timestamp);

    $cameradata=parsecamerasxml();
    $data=raw_getalldata();
    $makes=array();
    $noncc0samples=0;

    assert(!file_exists(publicdatapath_timestamped));
    mkdir(publicdatapath_timestamped);

    if(is_dir(publicdatagittmppath)){
        delTree(publicdatagittmppath);
    }
    mkdir(publicdatagittmppath);

    foreach($data as $raw){
        if($raw['validated']==1){
            $output_filename = get_raw_pretty_name($raw, $make, $model);
            if(!is_dir(publicdatapath_timestamped."/".$make)){
                mkdir(publicdatapath_timestamped."/".$make);
            }
            if(!is_dir(publicdatapath_timestamped."/".$make."/".$model)){
                mkdir(publicdatapath_timestamped."/".$make."/".$model);
            }
            symlink(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'],publicdatapath_timestamped."/".$output_filename);
            $sha256table[$output_filename]=$raw['checksum'];
            if(!in_array($make,$makes)){
                $makes[]=$make;
            }
            if($raw['license']!="CC0"){
                $noncc0samples++;
            }

            if(!is_dir(publicdatagittmppath."/".$make)){
                mkdir(publicdatagittmppath."/".$make);
            }
            if(!is_dir(publicdatagittmppath."/".$make."/".$model)){
                mkdir(publicdatagittmppath."/".$make."/".$model);
            }
            writeGitLFSPointer(publicdatagittmppath."/".$output_filename, $raw);
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

    foreach (scandir(publicdatapath_timestamped) as $filename) {
        if(is_file(publicdatapath_timestamped."/".$filename)) {
            copy(publicdatapath_timestamped."/".$filename, publicdatagittmppath."/".$filename);
        }
    }

    turnIntoAGitLFSRepo(publicdatagittmppath, publicdatagitrepopath_timestamped, 'data');

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
    if(file_exists(publicdatagitrepopath)) {
        if(is_link(publicdatagitrepopath)) {
            define('publicdatagitrepopath_old', realpath(publicdatagitrepopath));
            // NOTE: do not delete anything yet.
        } else if(is_dir(publicdatagitrepopath)) {
            delTree(publicdatagitrepopath);
        } else {
            assert(false);
        }
    }

    define('publicdatapath_new', publicdatapath.".new");
    define('publicdatagitrepopath_new', publicdatagitrepopath.".new");

    assert(!file_exists(publicdatapath_new));
    assert(!file_exists(publicdatagitrepopath_new));
    symlink(basename(publicdatapath_timestamped), publicdatapath_new);
    symlink(basename(publicdatagitrepopath_timestamped), publicdatagitrepopath_new);
    rename(publicdatapath_new, publicdatapath); // replaces old symlink!
    rename(publicdatagitrepopath_new, publicdatagitrepopath); // replaces old symlink!

    if(defined("publicdatapath_old")) {
        delTree(publicdatapath_old);
    }
    if(defined("publicdatagitrepopath_old")) {
        delTree(publicdatagitrepopath_old);
    }

    delTree(publicdatagittmppath);

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
    
    
    influxPoints([
        influxPointSerialize("rpu", ["key"=>"cameras"], ["value"=>$cameras]),
        influxPointSerialize("rpu", ["key"=>"samples"], ["value"=>$samples]),
        influxPointSerialize("rpu", ["key"=>"reposize"], ["value"=>$reposize]),
        influxPointSerialize("rpu", ["key"=>"noncc0samples"], ["value"=>$noncc0samples]),
        influxPointSerialize("rpu", ["key"=>"missingcameras"], ["value"=>$missingcameras]),
        influxPointSerialize("rpu", ["key"=>"makes"], ["value"=>count($makes)]),
    ]);

    // We're done, release the lock.
    unlink(publicdatapath_lock);
    flock($lock, LOCK_UN);
    fclose($lock);
