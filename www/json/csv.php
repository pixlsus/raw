<?php
    header("Content-type: text/csv");
    header("Content-Disposition: attachment;filename=raw-pixls-us.csv");
    include_once "../../config.php";
    include_once "../functions.php";

    $raws=raw_getalldata();
    $i=0;
    foreach($raws as $raw){
        if($raw['validated']==1) {
            echo $raw['id'].',"'.$raw['make'].'","'.$raw['model'].'","'.$raw['mode'].'","'.$raw['remark'].'","'.$raw['license'].'","'.$raw['filename'].'","'.$raw['checksum'].'","https://raw.pixls.us/getfile.php/'.$raw['id'].'/raw/'.$raw['filename']."\"\r\n";
        }
    }
