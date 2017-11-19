<?php
    include_once "../config.php";
    include_once "functions.php";

    $raws=raw_getalldata();
    $cameradata=unserialize(file_get_contents(datapath."/cameradata.serialize"));

    $cameras=array();
    $i=0;
    foreach($raws as $raw){
        if($raw['validated'] == "1" and $raw['license'] != "CC0"){
            $make="";
            if($raw['make']!=""){
                $make=$cameradata[$raw['make']][$raw['model']]['make'] ?? $cameradata[$raw['make']]['make'] ?? $raw['make'];
            }
            $model="";
            if($raw['model']!=""){
                $model=$cameradata[$raw['make']][$raw['model']]['model'] ?? $raw['model'];
            }

            $camera="$make $model";
            if(!in_array($camera,$cameras)){
                $cameras[]=$camera;
            }
        }
    }
    sort($cameras);
    foreach($cameras as $camera){
        print ("$camera<br>");
    }
