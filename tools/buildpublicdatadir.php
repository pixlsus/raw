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

    define('publicdatagitlfstmppath', publicdatapath."-git-lfs");

    $RCDs = [
                "publicdatapath" => new RefCountedDir(publicdatapath),
                "publicdatagitlfsrepopath" => new RefCountedDir(publicdatapath.".lfs.git"),
            ];

    foreach($RCDs as $RCD) {
        $RCD->gc();
    }

    $tempdirs = [
        publicdatagitlfstmppath,
    ];

    $cameradata=parsecamerasxml();
    $data=raw_getalldata();
    $makes=array();
    $noncc0samples=0;

    foreach($tempdirs as $tempdir) {
        if(is_dir($tempdir)){
            delTree($tempdir);
        }
        mkdir($tempdir);
    }

    $raws = [];
    foreach($data as $raw){
        if($raw['validated']==1){
            $raws[] = new RawEntry($raw);
        }
    }
    $tree = get_as_leafless_tree($raws, function($raw) {
        return [$raw->make, $raw->model];
    });

    foreach([
                $RCDs["publicdatapath"]->staging,
                publicdatagitlfstmppath
            ] as $prefix) {
        foreach(array_keys($tree) as $make) {
            mkdir($prefix."/".$make);
            foreach(array_keys($tree[$make]) as $model) {
                mkdir($prefix."/".$make."/".$model);
            }
        }
    }

    foreach($raws as $raw) {
        $prefix = $RCDs["publicdatapath"]->staging;
        $link = $prefix."/".$raw->getOutputPath();
        $raw = $raw->raw;
        $target = datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'];
        symlink($target, $link);
    }

    foreach($raws as $raw) {
        $prefix = publicdatagitlfstmppath;
        writeGitLFSPointer($prefix."/".$raw->getOutputPath(), $raw->raw);
    }

    foreach($raws as $raw) {
        $sha256table[$raw->getOutputPath()] = $raw->raw['checksum'];
    }

    foreach($raws as $raw) {
        if($raw->raw['license']!="CC0"){
            $noncc0samples++;
        }
    }

    ksort($sha256table, SORT_NATURAL | SORT_FLAG_CASE);

    $fp=fopen($RCDs["publicdatapath"]->staging."/filelist.sha256","w");
    foreach($sha256table as $file=>$sha256) {
        // There are two schemes:
        // <hash><space><space><filename>      <- read in text mode
        // <hash><space><asterisk><filename>   <- read in binary mode
        fprintf($fp,"%s *%s\n",$sha256,$file);
    }
    fclose($fp);

    file_put_contents($RCDs["publicdatapath"]->staging."/timestamp.txt",time());

    foreach (scandir($RCDs["publicdatapath"]->staging) as $filename) {
        if(is_file($RCDs["publicdatapath"]->staging."/".$filename)) {
            foreach ([
                        publicdatagitlfstmppath,
                     ] as $o) {
                copy($RCDs["publicdatapath"]->staging."/".$filename, $o."/".$filename);
            }
        }
    }

    turnIntoAGitLFSRepo(publicdatagitlfstmppath, $RCDs["publicdatagitlfsrepopath"]->staging, 'data');

    //--------------------------------------------------------------------------

    foreach($RCDs as $RCD) {
        $RCD->commit();
    }

    foreach($tempdirs as $tempdir) {
        delTree($tempdir);
    }

    //--------------------------------------------------------------------------

    // Badgegeneration
    $cameras=raw_getnumberofcameras();
    file_put_contents("../www/button-cameras.svg", file_get_contents("https://img.shields.io/badge/cameras-".$cameras."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-cameras.png", file_get_contents("https://img.shields.io/badge/cameras-".$cameras."-green.png?maxAge=3600"));
    file_put_contents("../www/button-makes.svg", file_get_contents("https://img.shields.io/badge/makes-".count(array_keys($tree))."-green.svg?maxAge=3600"));
    file_put_contents("../www/button-makes.png", file_get_contents("https://img.shields.io/badge/makes-".count(array_keys($tree))."-green.png?maxAge=3600"));
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
        influxPointSerialize("rpu", ["key"=>"makes"], ["value"=>count(array_keys($tree))]),
    ]);

    // We're done, release the lock.
    unlink(publicdatapath_lock);
    flock($lock, LOCK_UN);
    fclose($lock);
