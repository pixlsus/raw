#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");
    include("buildpublicdatadir.inc.php");

    // We don't really want to race with ourselves, so acquire a lock.
    define('publicdatapath_lock', publicdatapath.'.lock');
    $lock = fopen(publicdatapath_lock, "w");
    if(!flock($lock, LOCK_EX | LOCK_NB)) {
        echo 'Unable to obtain lock';
        exit(-1);
    }

    define('timestamp', time());

    $cameradata=parsecamerasxml();
    $data=raw_getalldata();
    $noncc0samples=0;

    $raws = [];
    foreach($data as $raw){
        if($raw['validated']==1){
            $raws[] = new RawEntry($raw);
        }
    }
    $tree = get_as_leafless_tree($raws, function($raw) {
        return [$raw->make, $raw->model];
    });

    buildpublicdatadir($raws, timestamp, publicdatapath, publicdataurl, publicdataannexuuid, 'data');

    foreach($raws as $raw) {
        if($raw->raw['license']!="CC0"){
            $noncc0samples++;
        }
    }

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
