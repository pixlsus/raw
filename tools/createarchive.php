#!/usr/bin/php
<?php
    include("../config.php");
    include("../www/functions.php");

    file_put_contents(datapath."/cameras.xml",file_get_contents("https://raw.githubusercontent.com/darktable-org/rawspeed/develop/data/cameras.xml"));
    $cameradata=parsecamerasxml();
    file_put_contents(datapath."/cameradata.serialize",serialize($cameradata));

    $allknowncameras = array();
    foreach($cameradata as $make) {
        if(!is_array($make))
            continue;
        foreach($make as $model) {
            if(!is_array($model) || count($model) != 2)
                continue;

            $thecamera = array($model['make'], $model['model']);
            if(in_array($thecamera, $allknowncameras))
                continue;

            $allknowncameras[] = $thecamera;
        }
    }

    $allexistingsamples = array();
    $data=raw_getalldata();

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

        $thecamera = array($make, $model);
        if(!in_array($thecamera, $allexistingsamples))
            $allexistingsamples[] = $thecamera;

            $make = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $make);
            $model = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $model);
        }
    }
    $allmissingcameras = array();
    foreach($allknowncameras as $knowncamera) {
        if(in_array($knowncamera, $allexistingsamples))
            continue;

        if(in_array($knowncamera, $allmissingcameras))
            continue;
        
        $allmissingcameras[] = $knowncamera;
    }
    file_put_contents(datapath."/missingcameradata.serialize",serialize($allmissingcameras));
