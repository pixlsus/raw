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

    $zip = new ZipArchive;
    $result = $zip->open(datapath.'/raw_pixls_us_archive.tmp.zip', ZipArchive::CREATE);
    $allexistingsamples = array();
    if ($result === TRUE) {
        $zip->addFile('data.txt', 'entryname.txt');

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
                echo "adding ".datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename']." as ".$make."/".$model."/".$raw['filename']."\n";
                $zip->addFile(datapath."/".hash_id($raw['id'])."/".$raw['id']."/".$raw['filename'],$make."/".$model."/".$raw['filename']);
            }
        }
        $zip->close();
        rename(datapath.'/raw_pixls_us_archive.tmp.zip', datapath.'/raw_pixls_us_archive.zip');
        echo "created new archive\n";
    } else {
        echo "right.. that didn't go as expected\n";
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
